<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Command;

use Doctrine\ORM\EntityManagerInterface;
use XGallery\BaseCommand;

/**
 * Class MediaCommand
 * @package XGallery\Command
 */
class MediaCommand extends BaseCommand
{

    /**
     * XMedia constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
