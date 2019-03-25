<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;

use Exception;
use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Webservices\Services\Now;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandNow extends AbstractCommand
{
    /**
     * @var Now
     */
    protected $now;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('now:'.strtolower($this->getClassName()));

        parent::configure();
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function prepare()
    {
        $this->now = new Now;

        return true;
    }
}
