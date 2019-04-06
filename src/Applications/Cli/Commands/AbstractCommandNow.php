<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;


use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Model\ModelNow;
use XGallery\Webservices\Services\Now;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandNow extends AbstractCommand
{
    /**
     * Now service
     *
     * @var Now
     */
    protected $now;

    /**
     * @var ModelNow $model
     */
    protected $model;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('now:'.strtolower($this->getClassName()));
        $this->now   = new Now;
        $this->model = ModelNow::getInstance();

        parent::configure();
    }
}
