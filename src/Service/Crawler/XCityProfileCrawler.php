<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use App\Service\AbstractCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * Class XCityCrawler
 * @package App\Service\Crawler
 */
final class XCityProfileCrawler extends AbstractCrawler
{
    /**
     * Endpoint
     *
     * @var string
     */
    protected $indexUrl = 'https://xxx.xcity.jp/idol/';

    /**
     * @var array
     */
    private $kana = ['あ', 'か', 'さ', 'た', 'な', 'は', 'ま', 'や', 'ら', 'わ'];

    /**
     * Get profile object
     * @param string $url
     * @return boolean|mixed|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
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
     * Get all profile links
     * @param null $callbackPagesCount
     * @param null $callback
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getAllDetailLinks($callbackPagesCount = null, $callback = null)
    {
        $pages      = $this->getIndexPages();
        $totalLinks = [];

        if (is_callable($callbackPagesCount)) {
            call_user_func($callbackPagesCount, $pages);
        }

        foreach ($pages as $kana => $totalPage) {
            for ($page = 1; $page <= $totalPage; $page++) {
                $indexUrl = $this->getIndexUrl(['kana' => $kana, 'index' => $page]);
                $links    = $this->getIndexDetailLinks($indexUrl);

                if (is_callable($callback)) {
                    call_user_func($callback, $links);
                }

                $totalLinks = array_merge($totalLinks, $this->getIndexDetailLinks($this->getIndexUrl($page)));
            }
        }

        return $totalLinks;
    }

    /**
     * @return array|boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        $pages = [];

        foreach ($this->kana as $kana) {
            $crawler = $this->getCrawler('GET', $this->getIndexUrl(['kana' => $kana, 'index' => 1]));

            if (!$crawler) {
                return false;
            }

            $nodes = $crawler->filter('ul.pageScrl li.next');

            if ($nodes->count() === 0 || $nodes->previousAll()->filter('li a')->count() === 0) {
                return 1;
            }

            $pages[$kana] = (int)$crawler->filter('ul.pageScrl li.next')->previousAll()->filter('li a')->text();
        }

        return $pages;
    }

    /**
     * @param null $page
     * @return string
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . '?kana=' . $page['kana'] . '&num=90&page=' . $page['index'];
    }

    /**
     * Get all profile links
     * @param string $url
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexDetailLinks($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        $domElement = $crawler->filter('.itemBox p.tn a');

        if ($domElement->count() === 0) {
            return false;
        }

        foreach ($domElement as $domProfile) {
            $list [] = $this->indexUrl . $domProfile->attributes[0]->value;
        }

        return $list;
    }
}
