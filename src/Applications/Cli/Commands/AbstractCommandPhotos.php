<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands;

use ReflectionException;
use XGallery\Applications\Cli\AbstractCommand;

/**
 * Class AbstractCommandPhotos
 * @package XGallery\Applications\Cli\Commands
 */
abstract class AbstractCommandPhotos extends AbstractCommand
{

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setName('photos:'.strtolower($this->getClassName()));

        parent::configure();
    }

    /**
     * @param $status
     * @return mixed|void
     */
    protected function executeComplete($status)
    {
        $this->connection->close();

        parent::executeComplete($status);
    }
}