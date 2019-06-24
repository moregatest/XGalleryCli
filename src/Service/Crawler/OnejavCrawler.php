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

/**
 * Class Onejav
 * @package App\Service\Crawler
 */
class OnejavCrawler extends BaseCrawler
{
    /**
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

                $title      = $el->filter('h5 a')->text();
                $size       = $el->filter('h5 span')->text();
                $size       = str_replace('GB', '', $size);
                $torrent    = $el->filter('.control.is-expanded a')->attr('href');
                $itemNumber = implode(' ', sscanf(trim($title), "%[A-Z]%d"));

                $crawler     = new R18Crawler;
                $searchLinks = $crawler->getSearchLinks($itemNumber);

                if (!empty($searchLinks)) {
                    foreach ($searchLinks as $searchLink) {
                        if (!$searchLink) {
                            continue;
                        }

                        $detail = $crawler->getDetail($searchLink);
                        break;
                    }
                }

                return [
                    'title' => trim($title),
                    'size' => (float)$size,
                    'download' => trim($torrent),
                    'item_number' => $itemNumber,
                    'detail' => $detail ?? null,
                    'r18' => reset($searchLinks),
                ];
            }
        );

        $list = [];

        foreach ($results as $index => $result) {
            $result['cover']          = $covers[$index];
            $list[$result['title']][] = $result;
        }

        return $list;
    }

    public function getFeatured()
    {
        $indexUrl = 'https://onejav.com/';

        if (!$crawler = $this->getCrawler('GET', $indexUrl)) {
            return false;
        }
        $covers = $crawler->filter('.is-desktop .card-content .dragscroll a.thumbnail-link img')->each(
            function ($img) {
                return $img->attr('src');
            }
        );

        $links = $crawler->filter('.is-desktop .card-content .dragscroll a.thumbnail-link')->each(
            function ($el) {
                return $el->attr('href');
            }
        );

        $list = [];

        foreach ($links as $index => $link) {
            $parts      = explode('/', $link);
            $number     = end($parts);
            $itemNumber = implode('-', sscanf(trim($number), "%[A-Z|a-z]%d"));

            $crawler     = new R18Crawler;
            $searchLinks = $crawler->getSearchLinks($itemNumber);

            if (!empty($searchLinks)) {
                foreach ($searchLinks as $searchLink) {
                    if (!$searchLink) {
                        continue;
                    }

                    $detail = $crawler->getDetail($searchLink);
                    break;
                }

                $list[] = [
                    'title' => $detail->dvd_id,
                    'cover' => $covers[$index],
                    'item_number' => $itemNumber,
                    'detail' => $detail ?? null,
                    'r18' => reset($searchLinks),
                ];
            }
        }

        return $list;
    }
}
