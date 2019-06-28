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

/**
 * Class Onejav
 * @package App\Service\Crawler
 */
class OnejavCrawler extends BaseCrawler
{
    /**
     * Search movie by keyword
     *
     * @param string $keyword
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function search($keyword)
    {
        if (!$crawler = $this->getCrawler('GET', 'https://onejav.com/search/' . urlencode($keyword))) {
            return false;
        }

        $covers = $crawler->filter('.container .card.mb-3 .column img')->each(
            function ($img) {
                return $img->attr('src');
            }
        );

        $results = $crawler->filter('.container .card.mb-3 .column.is-5')->each(
            function ($el) {
                $movie = new stdClass;

                $movie->title = trim($el->filter('h5 a')->text());
                $movie->size  = (float)(str_replace('GB', '', $el->filter('h5 span')->text()));
                // Date
                $movie->date        = trim($el->filter('.subtitle.is-6')->text());
                $movie->tags        = $el->filter('.tags .tag')->each(
                    function ($tag) {
                        return trim($tag->text());
                    }
                );
                $description        = $el->filter('.level.has-text-grey-dark');
                $movie->description = $description->count() ? trim($description->text()) : null;
                $movie->actresses   = $el->filter('.panel .panel-block')->each(
                    function ($actress) {
                        return trim($actress->text());
                    }
                );
                $movie->torrent     = trim(($el->filter('.control.is-expanded a')->attr('href')));
                $movie->itemNumber  = implode('-', sscanf(trim($movie->title), "%[A-Z]%d"));

                $crawler     = new R18Crawler;
                $searchLinks = $crawler->getSearchLinks($movie->itemNumber);

                if (!empty($searchLinks)) {
                    foreach ($searchLinks as $searchLink) {
                        if (!$searchLink) {
                            continue;
                        }

                        $detail = $crawler->getDetail($searchLink);
                        break;
                    }
                }

                $movie->detail = $detail ?? null;
                $movie->r18    = reset($searchLinks);

                return $movie;
            }
        );

        $list = [];

        foreach ($results as $index => $result) {
            $result->cover          = $covers[$index];
            $list[$result->title][] = $result;
        }

        return $list;
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
            $list [] = $this->getDetail('https://onejav.com' . $link);
        }

        return $list;
    }

    /**
     * @param string $url
     * @return boolean|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        if (!$crawler = $this->getCrawler('GET', $url)) {
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
        $movie->description = trim($crawler->filter('.level.has-text-grey-dark')->text());
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
                break;
            }
        }

        $movie->detail = $detail ?? null;
        $movie->r18    = reset($searchLinks);

        return $movie;
    }
}
