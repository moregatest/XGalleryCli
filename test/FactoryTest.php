<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require_once __DIR__.'/../bootstrap.php';

use PHPUnit\Framework\TestCase;
use XGallery\Factory;

/**
 * Class FactoryTest
 */
class FactoryTest extends TestCase
{
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testCanGetDbalConnection()
    {
        $this->assertInstanceOf(
            'Doctrine\DBAL\Connection',
            Factory::getConnection()
        );
    }

    /**
     * @throws Exception
     */
    public function testCanGetLogger()
    {
        $this->assertInstanceOf(
            'Monolog\Logger',
            Factory::getLogger()
        );
    }

    /**
     * @throws Exception
     */
    public function testCanGetCacher()
    {
        $this->assertInstanceOf(
            'Symfony\Component\Cache\Adapter\FilesystemAdapter',
            Factory::getCache()
        );
    }

    /**
     * @throws Exception
     */
    public function testCanGetValidService()
    {
        $this->assertInstanceOf(
            '\\XGallery\\Webservices\\Services\\Flickr',
            Factory::getServices('Flickr')
        );
    }

    /**
     * @throws Exception
     */
    public function testCanGetInValidService()
    {
        $this->assertFalse(Factory::getServices('false'));
    }
}
