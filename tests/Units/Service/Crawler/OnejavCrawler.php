<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Tests\Units\Service\Crawler;

use PHPUnit\Framework\TestCase;

/**
 * Class OnejavCrawler
 * @package App\Tests\Units\Service\Crawler
 */
class OnejavCrawler extends TestCase
{
    private $crawler;

    /**
     * OnejavCrawler constructor.
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->crawler = new \App\Service\Crawler\OnejavCrawler;

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testGetIndexPages()
    {
        $this->assertIsInt($this->crawler->getIndexPages('https://onejav.com/2019/08/19'), 'Can not get pages');
        $this->assertIsInt($this->crawler->getIndexPages('https://onejav.com'), 'Invalid pages');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function testGetAllDetailItems()
    {
        $this->assertIsArray($this->crawler->getAllDetailItems('2019/08/19'), 'Can not items');
    }
}
