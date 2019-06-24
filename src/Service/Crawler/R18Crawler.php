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
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * Class R18Crawler
 * @package App\Service\Crawler\Jav
 */
final class R18Crawler extends AbstractCrawler
{
    /**
     * @var string|null
     */
    protected $indexUrl = 'https://www.r18.com/videos/vod/movies/list/pagesize=120/price=all/sort=new/type=all/page=';

    /**
     * @return bool|int
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexPages()
    {
        $crawler = $this->getCrawler('GET', $this->getIndexUrl(1));

        if (!$crawler) {
            return false;
        }

        if ($crawler->filter('li.next')->previousAll()->filter('a')->count() === 0) {
            return 1;
        }

        return (int)$crawler->filter('li.next')->previousAll()->filter('a')->text();
    }

    /**
     * @param null $page
     * @return string
     */
    protected function getIndexUrl($page = null)
    {
        return $this->indexUrl . $page;
    }

    /**
     * @param string $url
     * @return array|boolean
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getIndexDetailLinks($url)
    {
        return $this->extractLinks($url, '.cmn-list-product01 li');
    }

    /**
     * @param $url
     * @return boolean|mixed|stdClass
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    public function getDetail($url)
    {
        $crawler = $this->getCrawler('GET', $url);

        if (!$crawler) {
            return false;
        }

        try {
            $movieDetail             = new stdClass;
            $movieDetail->name       = trim($crawler->filter('.product-details-page h1')->text());
            $movieDetail->categories = $crawler->filter('.product-categories-list a')->each(
                function ($el) {
                    return trim($el->text());
                }
            );
            $fields                  = $crawler->filter('.product-onload .product-details dt')->each(
                function ($dt) {
                    $text = trim($dt->text());

                    $value = str_replace(['-'], [''], $dt->nextAll()->text());

                    return [
                        strtolower(str_replace(' ', '_', str_replace([':'], [''], $text))) => trim($value),
                    ];
                }
            );

            $movieDetail->actress = $crawler->filter('.product-actress-list a span')->each(
                function ($span) {
                    return trim($span->text());
                }
            );

            $movieDetail = $this->assignFields($fields, $movieDetail);

            return $movieDetail;
        } catch (RuntimeException $exception) {
            $this->logError($exception->getMessage());

            return false;
        }
    }

    public function getSearchLinks($keyword)
    {
        $url = 'https://www.r18.com/common/search/searchword=' . urlencode($keyword) . '/';

        if (!$crawler = $this->getCrawler('GET', $url)) {
            return false;
        }

        return $crawler->filter('.main .cmn-list-product01 li.item-list a')->each(
            function ($el) {
                if ($href = $el->attr('href')) {
                    return $href;
                }
            }
        );
    }
}
