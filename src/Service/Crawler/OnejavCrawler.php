<?php
/**
 *
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
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Onejav
 * @package App\Service\Crawler
 */
class OnejavCrawler extends BaseCrawler
{
    /**
     * Return pages number
     *
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
     * @param $indexUrl
     * @return array
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getAllDetailItems($indexUrl)
    {
        $pages = $this->getIndexPages($indexUrl);
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
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getFeatured()
    {
        $indexUrl = 'https://onejav.com/';

        if (!$crawler = $this->getCrawler('GET', $indexUrl)) {
            return false;
        }

        $links = $crawler->filter('.is-desktop .card-content .dragscroll a.thumbnail-link')->each(
            function ($el) {
                return $el->attr('href');
            }
        );

        $list = [];

        foreach ($links as $index => $link) {
            if (!$crawler = $this->getCrawler('GET', $indexUrl . $link)) {
                unset($crawler);
                continue;
            }

            $list [] = $this->getDetail($crawler);
            unset($crawler);
        }

        return $list;
    }

    public function getDetailFromUrl($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        return $this->getDetail($crawler);
    }

    /**
     * @param Crawler $crawler
     * @return boolean|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    private function getDetail($crawler)
    {
        if (!$crawler) {
            return false;
        }

        $movie = new stdClass;

        $movie->cover = $crawler->filter('.columns img.image')->attr('src');
        $movie->title = trim($crawler->filter('h5 a')->text());
        $movie->size  = (float)str_replace('GB', '', $crawler->filter('h5 span')->text());
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
        $movie->itemNumber  = implode('-', sscanf(trim($movie->title), "%[A-Z]%d"));

        $crawler     = new R18Crawler;
        $searchLinks = $crawler->getSearchLinks($movie->itemNumber);

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
        $movie->r18    = reset($searchLinks);

        return $movie;
    }
}
