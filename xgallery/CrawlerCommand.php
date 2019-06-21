<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery;

use App\Service\CrawlerInterface;

/**
 * Class CrawlerCommand
 * @package XGallery
 */
class CrawlerCommand extends AbstractCommand
{

    /**
     * @param null $name
     * @return boolean|CrawlerInterface
     */
    protected function getClient($name = null)
    {
        static $instance;

        if ($instance) {
            return $instance;
        }

        if ($name === null) {
            $name = explode(':', $this->getName());
            $name = ucfirst($name[0]);
        }

        $className = '\\App\\Service\\Crawler\\' . $name . 'Crawler';

        if (!class_exists($className)) {
            return false;
        }

        $instance = new $className;

        return $instance;
    }
}
