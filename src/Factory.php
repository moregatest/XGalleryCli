<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App;

use ErrorException;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use XGallery\Defines\DefinesCore;

/**
 * Class Factory
 * @package App
 */
class Factory
{
    /**
     * Get cache instance
     *
     * @return boolean|FilesystemAdapter|MemcachedAdapter
     */
    public static function getCache()
    {
        static $instance;

        if ($instance) {
            return $instance;
        }

        $defaultLifetime = (int)getenv('cache_interval');

        switch (getenv('cache_driver')) {
            case 'memcached':
                try {
                    $instance = new MemcachedAdapter(
                        MemcachedAdapter::createConnection(getenv('memcached')),
                        DefinesCore::APPLICATION,
                        $defaultLifetime
                    );
                } catch (ErrorException $exception) {
                    return false;
                }
                break;
            case 'redis':
                try {
                    $instance = new RedisAdapter(
                        RedisAdapter::createConnection(getenv('redis_cache')),
                        DefinesCore::APPLICATION,
                        $defaultLifetime
                    );
                } catch (ErrorException $exception) {
                    return false;
                }
                break;
            default:
            case'filesystem':
                $instance = new FilesystemAdapter(
                    DefinesCore::APPLICATION,
                    $defaultLifetime,
                    getenv('filecache_path')
                );
                break;
        }

        return $instance;
    }

    /**
     * getMailer
     * @return PHPMailer
     * @throws Exception
     */
    public static function getMailer()
    {
        $mailer = new PHPMailer;
        $mailer->IsSMTP();

        $mailer->SMTPDebug  = 2;
        $mailer->SMTPAuth   = true;
        $mailer->SMTPSecure = getenv('smtp_secure');
        $mailer->Host       = getenv('smtp_host');
        $mailer->Port       = getenv('smtp_port');
        $mailer->IsHTML(true);
        $mailer->Username = getenv('smtp_username');
        $mailer->Password = getenv('smtp_password');
        $mailer->SetFrom(getenv('smtp_username'));
        $mailer->CharSet  = 'UTF-8';
        $mailer->Encoding = 'base64';

        return $mailer;
    }
}
