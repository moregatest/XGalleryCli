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
 * Class PhodacbietCrawler
 * @package App\Service\Crawler\Forums
 */
final class PhodacbietCrawler extends AbstractCrawler
{
    /**
     * @var string
     */
    protected $indexUrl = 'https://phodacbiet.info/forums/anh-hotgirl-nguoi-mau.43/';

    /**
     * @return boolean|integer
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        if (!$crawler = $this->getCrawler('GET', $this->indexUrl)) {
            return false;
        }

        return (int)$crawler->filter('.pageNav ul li a')->last()->text();
    }

    /**
     * Return array of thread URLs
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

        return $crawler->filter('.cate.post.thread a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );
    }

    /**
     * @param string $url
     * @return array|boolean|mixed
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        $images = $crawler->filter('img.bbImage')->each(
            function ($img) {
                return $img->attr('src');
            }
        );

        return $images;
    }

    /**
     * @param null $page
     * @return string
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . 'page-' . $page;
    }
}
