<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler\Xiuren;

use App\Service\Crawler\BaseCrawler;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class XiurenOrgCrawler
 * @package App\Service\Crawler\Xiuren
 */
class XiurenOrgCrawler extends BaseCrawler
{
    /**
     * @param $indexUrl
     * @return bool|int
     * @throws GuzzleException
     */
    public function getPages($indexUrl)
    {
        $crawler = $this->getCrawler('GET', $indexUrl);

        if (!$crawler) {
            return false;
        }

        $pages = $crawler->filter('#page .info')->text();
        $pages = explode('/', $pages);

        return (int)end($pages);
    }

    public function getItemLinks($indexUrl)
    {
        $crawler = $this->getCrawler('GET', $indexUrl);

        if (!$crawler) {
            return false;
        }

        return $crawler->filter('#main .loop .content a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );
    }

    public function getImages($detailUrl)
    {
        $crawler = $this->getCrawler('GET', $detailUrl);

        if (!$crawler) {
            return false;
        }

        return $crawler->filter('#main .post .photoThum a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );
    }
}
