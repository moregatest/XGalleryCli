<?php

namespace XGallery\Webservices\Services\Crawlers;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use stdClass;
use Symfony\Component\Console\Exception\RuntimeException;
use XGallery\Utilities\XCityHelper;
use XGallery\Webservices\Services\AbstractCrawler;

/**
 * Class Xcity
 * @package XGallery\Webservices\Services\Crawler
 */
class Xcity extends AbstractCrawler
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
     * getPages
     *
     * @param string $indexUrl
     * @return boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getPages($indexUrl)
    {
        $crawler = $this->fetch('GET', $indexUrl);

        if (!$crawler) {
            return false;
        }

        if ($crawler->filter('ul.pageScrl li.next')->previousAll()->filter('li a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('ul.pageScrl li.next')->previousAll()->filter('li a')->text();
    }

    /**
     * getProfilePages
     *
     * @return mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getProfiles()
    {
        static $pages;

        if (isset($pages)) {
            return $pages;
        }

        $list = [];

        foreach ($this->kana as $kana) {
            $totalPages = $this->getPages($this->endpoint.'/idol/?kana='.$kana.'&num=100&page=1');

            for ($page = 1; $page <= $totalPages; $page++) {

                $crawler = $this->fetch('GET', $this->endpoint.'/idol/?kana='.$kana.'&num=100&page='.$page);

                if (!$crawler) {
                    continue;
                }

                $domElement = $crawler->filter('.itemBox p.tn a');

                foreach ($domElement as $domProfile) {
                    $list [] = $domProfile->attributes[0]->value;
                }
            }
        }

        return $list;
    }

    /**
     * Get profile' properties
     *
     * @param $url
     * @return bool|mixed|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getProfile($url)
    {
        $crawler = $this->fetch('GET', $this->endpoint.'/idol/'.$url.'?style=simple');

        if (!$crawler) {
            return false;
        }

        $model             = new stdClass;
        $model->birthday   = null;
        $model->blood_type = null;
        $model->city       = null;
        $model->height     = null;

        try {
            $model->name = $crawler->filter('.itemBox h1')->text();
            $model->xid = explode('/', trim($url, '/'));
            $model->xid = end($model->xid);

            $fields = $crawler->filter('#avidolDetails dl.profile dd')->each(function ($dd) {
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
                    $month = XCityHelper::getMonth($days[1]);

                    if (!$month) {
                        return null;
                    }

                    return ['birthday' => $days[0].'-'.$month.'-'.$days[2]];
                }

                if (strpos($text, 'Blood Type') !== false) {
                    return ['blood_type' => trim(str_replace('Blood Type', '', $text))];
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
                                $waist = empty($size) ? null : (int)$size;
                                break;
                            case 2:
                                $size = str_replace('H', '', $size);
                                $hips = empty($size) ? null : (int)$size;
                                break;
                        }
                    }

                    return [
                        'sizes' => [
                            'breast' => isset($breast) ? $breast : null,
                            'waist' => isset($waist) ? $waist : null,
                            'hips' => isset($hips) ? $hips : null,
                        ],
                    ];
                }
            });

            if (empty($fields)) {
                return $model;
            }

            // Assign fields to object
            foreach ($fields as $field) {
                if (!$field) {
                    continue;
                }
                foreach ($field as $key => $value) {
                    if (empty($value)) {
                        $model->{$key} = null;
                        continue;
                    }

                    if (is_array($value)) {
                        foreach ($value as $subKey => $subValue) {
                            $model->{$subKey} = $subValue;
                        }
                        continue;
                    }
                    $model->{$key} = trim($value);
                }
            }
        } catch (\RuntimeException $exception) {
            $this->logError($exception->getMessage());
        }

        return $model;
    }

    /**
     * getProfileFilmPages
     * @param $url
     * @return bool|int|AbstractCrawler
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getProfileFilmPages($url)
    {
        return $this->getPages($this->endpoint.'/idol/'.$url.'?style=simple');
    }

    public function getProfileFilmLinks($profileUrl)
    {
        $totalPages = $this->getProfileFilmPages($profileUrl);
        $list       = [];

        for ($page = 1; $page <= $totalPages; $page++) {
            if ($page === 1) {
                $crawler = $this->fetch('GET', $this->endpoint.'/idol/'.$profileUrl.'?style=simple');
            } else {
                $crawler = $this->fetch('GET', $this->endpoint.'/idol/'.$profileUrl.'?style=simple&page='.$page);
            }

            if (!$crawler) {
                return false;
            }

            $domElement = $crawler->filter('.x-itemBox .x-itemBox-title a');

            foreach ($domElement as $domProfile) {
                $list [] = $domProfile->attributes[0]->value;
            }
        }

        return $list;
    }

    /**
     * Get film properties
     *
     * @param string $url
     * @return boolean|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getFilm($url)
    {
        $crawler = $this->fetch('GET', $this->endpoint.$url);

        if (!$crawler) {
            return false;
        }

        $film = new stdClass;

        try {
            $film->name = $crawler->filter('#program_detail_title')->text();
            $filmXId    = explode('=', $url);
            $film->xid  = (int)end($filmXId);

            // Get all fields
            $fields = $crawler->filter('.bodyCol ul li')->each(function ($li) {
                if (strpos($li->text(), '★Favorite') !== false) {
                    return ['favorite' => (int)str_replace('★Favorite', '', $li->text())];
                }
                if (strpos($li->text(), 'Sales Date') !== false) {
                    return ['sales_date' => trim(str_replace('/', '-', str_replace('Sales Date', '', $li->text())))];
                }
                if (strpos($li->text(), 'Label/Maker') !== false) {
                    return [
                        'label' => $li->filter('#program_detail_maker_name')->text(),
                        'marker' => $li->filter('#program_detail_label_name')->text(),
                    ];
                }

                if (strpos($li->text(), 'Genres') !== false) {
                    $genres = $li->filter('a.genre')->each(function ($a) {
                        return trim($a->text());
                    });

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
                        'time' => trim(str_replace(
                            'min',
                            '',
                            str_replace('Running Time', '', $li->text())
                        ), '.'),
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
            });

            // Assign fields to object
            foreach ($fields as $field) {
                if (!$field) {
                    continue;
                }
                foreach ($field as $key => $value) {
                    if (empty($value)) {
                        $film->{$key} = null;
                        continue;
                    }

                    if (is_array($value)) {
                        $film->{$key} = $value;
                        continue;
                    }

                    $film->{$key} = trim($value);
                }
            }
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }

        return $film;
    }
}
