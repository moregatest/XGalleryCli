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
 * Class R18Crawler
 * @package App\Service\Crawler\Jav
 */
class R18Crawler extends BaseCrawler implements JavCrawlerInterface
{
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

        if ($crawler->filter('li.next')->previousAll()->filter('a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('li.next')->previousAll()->filter('a')->text();
    }


    /**
     * @return array|bool
     */
    public function getProfileLinks()
    {
        return [];
    }

    /**
     * @param $url
     * @return stdClass
     */
    public function getProfileDetail($url)
    {
        return new stdClass;
    }

    /**
     * @param $url
     * @return array|boolean
     * @throws GuzzleException
     */
    public function getMovieLinks($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        $items = [];
        $nodes = $crawler->filter('.cmn-list-product01 li');

        if ($nodes->count() === 0) {
            return false;
        }

        foreach ($nodes as $item) {
            $items [] = $item->childNodes[1]->getAttribute('href');
        }

        return $items;
    }

    /**
     * @param $url
     * @return boolean|stdClass
     * @throws GuzzleException
     */
    public function getMovieDetail($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        try {
            $movieDetail             = new stdClass;
            $movieDetail->name       = $crawler->filter('.product-details-page h1')->text();
            $movieDetail->categories = $crawler->filter('.product-categories-list a')->each(
                function ($el) {
                    return trim($el->text());
                }
            );
            $fields                  = $crawler->filter('.product-onload .product-details dt')->each(
                function ($dt) {
                    $text = $dt->text();

                    $value = str_replace(['-'], [''], $dt->nextAll()->text());

                    return [
                        strtolower(str_replace(' ', '_', str_replace([':'], [''], $text))) => trim($value),
                    ];
                }
            );

            $movieDetail = $this->assignFields($fields, $movieDetail);

            return $movieDetail;
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());
        }
    }
}
