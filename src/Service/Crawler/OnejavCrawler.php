<?php

/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use App\Service\BaseCrawler;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use stdClass;

/**
 * Class Onejav
 * @package App\Service\Crawler
 */
class OnejavCrawler extends BaseCrawler
{
    protected $indexUrl = 'https://onejav.com';

    /**
     * @param string $indexUrl
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getAllDetailItems($indexUrl)
    {
        $indexUrl = $this->indexUrl . '/' . $indexUrl;

        if (!$pages = $this->getIndexPages($indexUrl)) {
            return [];
        }

        $items = [];

        for ($page = 1; $page <= $pages; $page++) {
            if (!$crawler = $this->getCrawler('GET', $indexUrl . '?page=' . $page)) {
                unset($crawler);
                continue;
            }
            $items = array_merge(
                $items,
                $crawler->filter('.container .card.mb-3')->each(
                    function ($el) {
                        return $this->getDetail($el);
                    }
                )
            );
            unset($crawler);
        }

        return $items;
    }

    /**
     * Return pages number
     * @param $indexUrl
     * @return boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages($indexUrl)
    {
        if (!$crawler = $this->getCrawler('GET', $indexUrl)) {
            return false;
        }

        if ($crawler->filter('ul.pagination-list a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('ul.pagination-list a')->last()->text();
    }

    /**
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getFeatured()
    {
        if (!$crawler = $this->getCrawler('GET', $this->indexUrl)) {
            return false;
        }

        $links = $crawler->filter('.is-desktop .card-content .dragscroll a.thumbnail-link')->each(
            function ($el) {
                return $el->attr('href');
            }
        );

        $list = [];

        foreach ($links as $index => $link) {
            if (!$crawler = $this->getCrawler('GET', $this->indexUrl . $link)) {
                unset($crawler);
                continue;
            }

            $list [] = $this->getDetail($crawler);
            unset($crawler);
        }

        return $list;
    }

    /**
     * @param $url
     * @return bool|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetailFromUrl($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        return $this->getDetail($crawler);
    }

    /**
     * @param $crawler
     * @return boolean|stdClass
     */
    private function getDetail($crawler)
    {
        if (!$crawler) {
            return false;
        }

        $movie = new stdClass;

        $movie->cover = null;
        $movie->title = null;
        $movie->size  = null;

        if ($crawler->filter('.columns img.image')->count()) {
            $movie->cover = $crawler->filter('.columns img.image')->attr('src');
        }

        if ($crawler->filter('h5 a')->count()) {
            $movie->title = trim($crawler->filter('h5 a')->text());
        }

        if ($crawler->filter('h5 span')->count()) {
            $movie->size = (float)str_replace('GB', '', $crawler->filter('h5 span')->text());
        }

        // Date
        $movie->date        = trim($crawler->filter('.subtitle.is-6')->text());
        $movie->tags        = $crawler->filter('.tags .tag')->each(
            function ($tag) {
                return trim($tag->text());
            }
        );
        $description        = $crawler->filter('.level.has-text-grey-dark');
        $movie->description = $description->count() ? trim($description->text()) : null;
        $movie->actresses   = $crawler->filter('.panel .panel-block')->each(
            function ($actress) {
                return trim($actress->text());
            }
        );
        $movie->torrent     = $crawler->filter('.control.is-expanded a')->attr('href');
        $movie->itemNumber  = implode('-', sscanf(trim($movie->title), "%[A-Z]%[0-9]"));

        /*        $crawler     = new R18Crawler;
                $searchLinks = $crawler->getSearchLinks($movie->itemNumber);
                $searchLinks = $searchLinks ?? [];

                if (!empty($searchLinks)) {
                    foreach ($searchLinks as $searchLink) {
                        if (!$searchLink) {
                            continue;
                        }

                        $detail = $crawler->getDetail($searchLink);
                        foreach ($detail->actress as $actress) {
                            $movie->actresses[] = $actress;
                        }

                        $movie->actresses = array_unique($movie->actresses);
                        break;
                    }
                }

                $movie->detail = $detail ?? null;
                $movie->r18    = reset($searchLinks);*/

        return $movie;
    }

    /**
     * @param $itemNumber
     * @return stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getR18($itemNumber)
    {
        $crawler = new R18Crawler;

        $searchLinks = $crawler->getSearchLinks($itemNumber);
        $searchLinks = $searchLinks ?? [];

        if (empty($searchLinks)) {
            return false;
        }

        return $crawler->getDetail(reset($searchLinks));
    }

    /**
     * @return array|bool
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getTags()
    {
        if (!$crawler = $this->getCrawler('GET', $this->indexUrl . '/tag')) {
            return false;
        }

        return $crawler->filter('.card-content .button.is-link')->each(function ($el) {
            return trim($el->text());
        });
    }
}
