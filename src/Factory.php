<?php

namespace XGallery;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


/**
 * Class Factory
 *
 * @package XGallery
 */
class Factory
{

    const NAMESPACE = 'XGallery3';

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function getDbo()
    {
        $config = new Configuration;

        return DriverManager::getConnection(
            [
                'dbname' => 'xgallery3',
                'user' => 'root',
                'password' => 'root',
                'host' => 'localhost',
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb4'
            ],
            $config
        );

    }

    /**
     * @param $name
     *
     * @return Logger
     * @throws \Exception
     */
    public static function getLogger($name)
    {
        static $logger;

        if (isset($logger)) {
            return $logger;
        }

        $logger = new  Logger(self::NAMESPACE);
        $logFile = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($name));
        $logger->pushHandler(
            new StreamHandler(
                __DIR__ . '/../logs/' . $logFile . '_' . date("Y-m-d") . '.log'
            )
        );

        return $logger;
    }

    /**
     * @param string $namespace
     * @param int $defaultLifetime
     * @param string|null $directory
     *
     * @return FilesystemAdapter
     */
    public static function getCache($namespace = self::NAMESPACE, $defaultLifetime = 0, string $directory = null)
    {
        static $instances;

        $id = md5(serialize(func_num_args()));

        if (isset($instances[$id])) {
            return $instances[$id];
        }

        if ($directory === null) {
            $directory = __DIR__ . '/../cache';
        }

        $instances[$id] = new FilesystemAdapter($namespace, $defaultLifetime, $directory);

        return $instances[$id];
    }

    /**
     * @param $service
     * @return boolean|mixed
     */
    public static function getServices($service)
    {
        static $instances;

        $id = md5(serialize(func_get_args()));

        if (isset($instances[$id])) {
            return $instances[$id];
        }

        $classString = '\\XGallery\\Webservices\\Services\\' . ucfirst($service);
        $defineString = '\\XGallery\\Defines\\Defines' . ucfirst($service);

        if (!class_exists($classString)) {
            return false;
        }

        $class = new $classString;
        $credential = constant($defineString . '::CREDENTIAL');
        $class->setCredential(
            $credential['oauth_consumer_key'],
            $credential['oauth_consumer_secret'],
            $credential['oauth_token'],
            $credential['oauth_token_secret']
        );

        return $class;
    }
}