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

use App\Service\AbstractCrawler;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class XiurenOrgCrawler
 * @package App\Service\Crawler\Xiuren
 */
final class XiurenCrawler extends AbstractCrawler
{
    protected $indexUrl = 'http://xiuren.org';

    /**
     * @return boolean|integer
     * @throws GuzzleException
     */
    public function getIndexPages()
    {
        $crawler = $this->getCrawler('GET', $this->getIndexUrl(1));

        if (!$crawler) {
            return false;
        }

        $pages = $crawler->filter('#page .info')->text();
        $pages = explode('/', $pages);

        return (int)end($pages);
    }

    /**
     * @param null $page
     * @return string
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . '/page-' . $page . '.html';
    }

    /**
     * @param $indexUrl
     * @return array|bool
     * @throws GuzzleException
     */
    public function getIndexDetailLinks($indexUrl)
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

    /**
     * @param $detailUrl
     * @return array|bool
     * @throws GuzzleException
     */
    public function getDetail($detailUrl)
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
