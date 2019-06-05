<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Service\Crawler;

use App\Service\Crawler\BatdongsanCrawler;
use PHPUnit\Framework\TestCase;

/**
 * Class BdsCrawlerTest
 * @package App\Tests\Service\Crawler
 */
class BdsCrawlerTest extends TestCase
{

    /**
     * @return BatdongsanCrawler
     */
    private function getClient()
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        $instance = new BatdongsanCrawler;

        return $instance;
    }

    public function testGetPages()
    {
        $pages = $this->getClient()->getPages('https://batdongsan.com.vn/nha-dat-ban');

        $this->assertIsInt($pages, 'Is not valid integer');
        $this->assertGreaterThan(1, $pages, 'Not sure about this value');
    }

    public function testExtractItems()
    {
        $this->assertIsArray($this->getClient()->extractItems('https://batdongsan.com.vn/nha-dat-ban'));
    }

    public function testExtractItem()
    {
        $this->assertIsObject(
            $this->getClient()->extractItem(
                'https://batdongsan.com.vn/ban-can-ho-chung-cu-xa-nhon-hai-1-prj-quy-nhon-symphony-of-the-sea-sun/du-lich-mat-tien-bien-tp-nn-gia-33tr-m2-dang-cap-5-100-view-bien-lh-0919483088-pr21020684'
            )
        );
    }
}
