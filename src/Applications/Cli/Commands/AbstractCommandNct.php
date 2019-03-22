<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;

use Exception;
use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;
use XGallery\Webservices\Services\Nct;

/**
 * Class CommandFlickr
 * @package XGallery\Applications\Commands
 */
abstract class AbstractCommandNct extends AbstractCommand
{
    /**
     * @var Nct
     */
    protected $nct;

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('nct:'.strtolower($this->getClassName()));

        parent::configure();
    }

    /**
     * @return boolean
     * @throws Exception
     */
    protected function prepare()
    {
        $this->nct = new Nct;

        return true;
    }
}
