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
use XGallery\Model\Jav\ModelJav;
use XGallery\Webservices\Services\Crawlers\Xcity;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandJav extends AbstractCommand
{
    /**
     * @var Xcity
     */
    protected $xcity;

    /**
     * @var ModelJav
     */
    protected $model;

    /**
     * Configures the current command.
     *
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('jav:'.strtolower($this->getClassName()));
        $this->xcity = new Xcity;
        $this->model = ModelJav::getInstance();

        parent::configure();
    }
}
