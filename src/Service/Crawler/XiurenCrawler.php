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

/**
 * Class XiurenOrgCrawler
 * @package App\Service\Crawler\Xiuren
 */
final class XiurenCrawler extends AbstractCrawler
{
    /**
     * @var string
     */
    protected $indexUrl = 'http://xiuren.org';

    /**
     * @return boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        if (!$crawler = $this->getCrawler('GET', $this->getIndexUrl(1))) {
            return false;
        }

        $pages = explode('/', $crawler->filter('#page .info')->text());

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
     * @param string $indexUrl
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexDetailLinks($indexUrl)
    {
        if (!$crawler = $this->getCrawler('GET', $indexUrl)) {
            return false;
        }

        return $crawler->filter('#main .loop .content a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );
    }

    /**
     * @param string $detailUrl
     * @return array|boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($detailUrl)
    {
        if (!$crawler = $this->getCrawler('GET', $detailUrl)) {
            return false;
        }

        return $crawler->filter('#main .post .photoThum a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );
    }
}
