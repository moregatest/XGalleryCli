<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler\Forums;

use App\Service\Crawler\BaseCrawler;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Class PhodacbietCrawler
 * @package App\Service\Crawler\Forums
 */
class PhodacbietCrawler extends BaseCrawler
{

    /**
     * @param string $url
     * @return array|boolean
     * @throws GuzzleException
     */
    public function getThreads($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        $urls = $crawler->filter('.cate.post.thread a')->each(
            function ($a) {
                return $a->attr('href');
            }
        );

        return $urls;
    }

    /**
     * @param string $url
     * @return array|boolean
     * @throws GuzzleException
     */
    public function getThreadImages($url)
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
}
