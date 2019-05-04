<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

/**
 * Class Factory
 * @package XGallery
 */
class Factory
{
    const APP_NAMESPACE = 'XGallery3';

    /**
     * Get database connection
     *
     * @return Connection
     * @throws DBALException
     */
    public static function getConnection()
    {
        $config = new Configuration;

        return DriverManager::getConnection(
            [
                'dbname' => getenv('mysql_database'),
                'user' => getenv('mysql_user'),
                'password' => getenv('mysql_password'),
                'host' => getenv('mysql_host'),
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb4',
            ],
            $config
        );
    }

    /**
     * Get logger
     *
     * @param $name
     * @return Logger
     * @throws Exception
     */
    public static function getLogger($name = null)
    {
        static $loggers;

        if ($name === null) {
            $name = static::class;
        }

        if (isset($loggers[$name])) {
            return $loggers[$name];
        }

        $loggers[$name] = new Logger(self::APP_NAMESPACE);
        $logFile        = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($name));

        $loggers[$name]->pushHandler(
            new StreamHandler(
                getenv('log_path').'/'.uniqid($logFile.'_'.date('Y-m-d').'_'.time(), true).'.log'
            )
        );

        return $loggers[$name];
    }

    /**
     * Get cache instance
     *
     * @param string      $namespace
     * @param int         $defaultLifetime
     * @param string|null $directory
     *
     * @return FilesystemAdapter
     */
    public static function getCache($namespace = self::APP_NAMESPACE, $defaultLifetime = 0, string $directory = null)
    {
        static $instances;

        $id = md5(serialize(func_get_args()));

        if (isset($instances[$id])) {
            return $instances[$id];
        }

        if ($directory === null) {
            $directory = getenv('cache_path');
        }

        if ($defaultLifetime === null) {
            $defaultLifetime = getenv('cache_interval');
        }

        $instances[$id] = new FilesystemAdapter($namespace, $defaultLifetime, $directory);

        return $instances[$id];
    }

    /**
     * Get service instance
     *
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

        $classString = '\\XGallery\\Webservices\\Services\\'.ucfirst($service);

        if (!class_exists($classString)) {
            return false;
        }

        $class = new $classString;
        $class->setCredential(
            getenv($service.'_oauth_consumer_key'),
            getenv($service.'_oauth_consumer_secret'),
            getenv($service.'_oauth_token'),
            getenv($service.'_oauth_token_secret')
        );

        return $class;
    }

    /**
     * Get dispatcher instance
     *
     * @return EventDispatcher
     */
    public static function getDispatcher()
    {
        static $dispatcher;

        if (isset($dispatcher)) {
            return $dispatcher;
        }

        $dispatcher = new EventDispatcher;

        return $dispatcher;
    }

    /**
     * getMailer
     * @return PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function getMailer()
    {
        $mail = new PHPMailer;
        $mail->IsSMTP();
        $mail->SMTPDebug  = 0;
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = getenv('smtp_secure');
        $mail->Host       = getenv('smtp_host');
        $mail->Port       = getenv('smtp_port');
        $mail->IsHTML(true);
        $mail->Username = getenv('smtp_username');
        $mail->Password = getenv('smtp_password');
        $mail->SetFrom(getenv('smtp_username'));
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'base64';

        return $mail;
    }

    public static function getImapMailer()
    {
        return imap_open(
            '{imap.gmail.com:993/imap/ssl}INBOX',
            getenv('smtp_username'),
            getenv('smtp_password')
        );
    }

    /**
     * Get template
     *
     * @param string $dirPattern
     * @return PhpEngine
     */
    public static function getTemplate($dirPattern = XGALLERY_ROOT.'/templates/%name%')
    {
        $filesystemLoader = new FilesystemLoader($dirPattern);

        return new PhpEngine(new TemplateNameParser(), $filesystemLoader);
    }
}
