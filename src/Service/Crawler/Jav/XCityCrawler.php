<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler\Jav;

use App\Service\Crawler\BaseCrawler;
use App\Service\Crawler\JavCrawlerInterface;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use stdClass;

/**
 * Class XCityCrawler
 * @package App\Service\Crawler
 */
class XCityCrawler extends BaseCrawler implements JavCrawlerInterface
{
    /**
     * Endpoint
     *
     * @var string
     */
    private $endpoint = 'https://xxx.xcity.jp';

    /**
     * @var array
     */
    private $kana = ['あ', 'か', 'さ', 'た', 'な', 'は', 'ま', 'や', 'ら', 'わ'];

    /**
     * @param string $index
     * @return boolean|mixed
     */
    private function getMonth($index)
    {
        $months = [
            'Jan' => '01',
            'Feb' => '02',
            'Mar' => '03',
            'Apr' => '04',
            'May' => '05',
            'Jun' => '06',
            'Jul' => '07',
            'Aug' => '08',
            'Sep' => '09',
            'Oct' => '10',
            'Nov' => '11',
            'Dec' => '12',
        ];

        if (isset($months[$index])) {
            return $months[$index];
        }

        return false;
    }

    /**
     * Get number of pages
     *
     * @param string $indexUrl
     * @return boolean|integer
     * @throws GuzzleException
     */
    public function getPages($indexUrl)
    {
        $crawler = $this->getCrawler('GET', $indexUrl);

        if (!$crawler) {
            return false;
        }

        $nodes = $crawler->filter('ul.pageScrl li.next');

        if ($nodes->count() === 0 || $nodes->previousAll()->filter('li a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('ul.pageScrl li.next')->previousAll()->filter('li a')->text();
    }

    /**
     * Get all profile links
     *
     * @return array|boolean
     * @throws GuzzleException
     */
    public function getProfileLinks()
    {
        static $pages;

        if (isset($pages)) {
            return $pages;
        }

        $list  = [];
        $limit = 90;

        foreach ($this->kana as $kana) {
            $url        = $this->endpoint . '/idol/?kana=' . $kana . '&num=' . $limit;
            $totalPages = $this->getPages($url . '&page=1');

            for ($page = 1; $page <= $totalPages; $page++) {
                if (!$crawler = $this->getCrawler('GET', $url . '&page=' . $page)) {
                    continue;
                }

                $domElement = $crawler->filter('.itemBox p.tn a');

                if ($domElement->count() === 0) {
                    continue;
                }

                foreach ($domElement as $domProfile) {
                    $list [] = $domProfile->attributes[0]->value;
                }
            }
        }

        return $list;
    }

    /**
     * Get profile object
     *
     * @param string $url
     * @return stdClass|boolean
     * @throws GuzzleException
     */
    public function getProfileDetail($url)
    {
        $crawler = $this->getCrawler('GET', $this->endpoint . '/idol/' . $url . '?style=simple');

        if (!$crawler) {
            return false;
        }

        try {
            $model             = new stdClass;
            $fields            = $crawler->filter('#avidolDetails dl.profile dd')->each(
                function ($dd) {
                    $text = $dd->text();

                    if (strpos($text, '★Favorite') !== false) {
                        return ['favorite' => (int)str_replace('★Favorite', '', $text)];
                    }

                    if (strpos($text, 'Date of birth') !== false) {
                        $birthday = trim(str_replace('Date of birth', '', $text));

                        if (empty($birthday)) {
                            return null;
                        }

                        $days  = explode(' ', $birthday);
                        $month = $this->getMonth($days[1]);

                        if (!$month) {
                            return null;
                        }

                        return ['birthday' => $days[0] . '-' . $month . '-' . $days[2]];
                    }

                    if (strpos($text, 'Blood Type') !== false) {
                        $bloodType = str_replace(['Blood Type', 'Type', '-', '_'], ['', '', '', ''], $text);

                        return ['blood_type' => trim($bloodType)];
                    }

                    if (strpos($text, 'City of Born') !== false) {
                        return ['city' => trim(str_replace('City of Born', '', $text))];
                    }

                    if (strpos($text, 'Height') !== false) {
                        return ['height' => trim(str_replace('cm', '', str_replace('Height', '', $text)))];
                    }

                    if (strpos($text, 'Size') !== false) {
                        $sizes = trim(str_replace('Size', '', $text));

                        if (empty($sizes)) {
                            return null;
                        }

                        $sizes = explode(' ', $sizes);

                        foreach ($sizes as $index => $size) {
                            switch ($index) {
                                case 0:
                                    $size   = str_replace('B', '', $size);
                                    $size   = explode('(', $size);
                                    $breast = empty(trim($size[0])) ? null : (int)$size[0];
                                    break;
                                case 1:
                                    $size  = str_replace('W', '', $size);
                                    $size  = explode('(', $size);
                                    $waist = empty(trim($size[0])) ? null : (int)$size[0];
                                    break;
                                case 2:
                                    $size = str_replace('H', '', $size);
                                    $size = explode('(', $size);
                                    $hips = empty(trim($size[0])) ? null : (int)$size[0];
                                    break;
                            }
                        }

                        return [
                            'sizes' => [
                                'breast' => $breast ?? null,
                                'waist' => $waist ?? null,
                                'hips' => $hips ?? null,
                            ],
                        ];
                    }
                }
            );
            $model             = $this->assignFields($fields, $model);
            $model->name       = $crawler->filter('.itemBox h1')->text();
            $model->xid        = explode('/', trim($url, '/'));
            $model->xid        = end($model->xid);
            $model->birthday   = $model->birthday ?? null;
            $model->blood_type = $model->blood_type ?? null;
            $model->city       = $model->city ?? null;
            $model->height     = $model->height ?? null;

            return $model;
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }

    /**
     * Get film links in profile view
     *
     * @param string $profileUrl
     * @return array|boolean
     * @throws GuzzleException
     */
    public function getMovieLinks($profileUrl)
    {
        $totalPages = $this->getPages($this->endpoint . '/idol/' . $profileUrl . '?page=1');

        $list = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            $crawler = $this->getCrawler('GET', $this->endpoint . '/idol/' . $profileUrl . '?page=' . $page);

            if (!$crawler) {
                return false;
            }

            $domElement = $crawler->filter('.x-itemBox .x-itemBox-title a');

            if ($domElement->count() === 0) {
                continue;
            }

            foreach ($domElement as $domProfile) {
                $list [] = $domProfile->attributes[0]->value;
            }
        }

        return $list;
    }

    /**
     * Extract film data
     *
     * @param string $url
     * @return stdClass|boolean
     * @throws GuzzleException
     */
    public function getMovieDetail($url)
    {
        $url     = $this->endpoint . $url;
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        try {
            $film = new stdClass;

            $film->name = $crawler->filter('#program_detail_title')->text();
            $film->url  = $url;
            $filmXId    = explode('=', $url);
            $film->id   = (int)end($filmXId);

            // Get all fields
            $fields = $crawler->filter('.bodyCol ul li')->each(
                function ($li) {
                    if (strpos($li->text(), '★Favorite') !== false) {
                        return ['favorite' => (int)str_replace('★Favorite', '', $li->text())];
                    }
                    if (strpos($li->text(), 'Sales Date') !== false) {
                        return [
                            'sales_date' => trim(
                                str_replace('/', '-', str_replace('Sales Date', '', $li->text()))
                            ),
                        ];
                    }
                    if (strpos($li->text(), 'Label/Maker') !== false) {
                        return [
                            'label' => $li->filter('#program_detail_maker_name')->text(),
                            'marker' => $li->filter('#program_detail_label_name')->text(),
                        ];
                    }

                    if (strpos($li->text(), 'Genres') !== false) {
                        $genres = $li->filter('a.genre')->each(
                            function ($a) {
                                return trim($a->text());
                            }
                        );

                        return ['genres' => $genres];
                    }

                    if (strpos($li->text(), 'Series') !== false) {
                        return ['series' => str_replace('Series', '', $li->text())];
                    }

                    if (strpos($li->text(), 'Director') !== false) {
                        return ['director' => str_replace('Director', '', $li->text())];
                    }
                    if (strpos($li->text(), 'Item Number') !== false) {
                        return ['item_number' => str_replace('Item Number', '', $li->text())];
                    }

                    if (strpos($li->text(), 'Running Time') !== false) {
                        return [
                            'time' => (int)trim(str_replace(['Running Time', 'min', '.'], ['', '', ''], $li->text())),
                        ];
                    }

                    if (strpos($li->text(), 'Release Date') !== false) {
                        $releaseDate = trim(str_replace('Release Date', '', $li->text()));
                        if (!empty($releaseDate) && strpos($releaseDate, 'undelivered now') === false) {
                            return ['release_date' => str_replace('/', '-', $releaseDate)];
                        }
                    }

                    if (strpos($li->text(), 'Description') !== false) {
                        return ['description' => str_replace('Description', '', $li->text())];
                    }
                }
            );
            $film   = $this->assignFields($fields, $film);
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }

        return $film;
    }
}
