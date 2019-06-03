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

use App\Service\Crawler\Nct;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class NctCommand
 * @package XGallery\Command
 */
class NctCommand extends AbstractCommand
{
    /**
     * @var Nct
     */
    protected $client;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * NctCommand constructor.
     * @param Nct $client
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(Nct $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        parent::__construct();
    }
}
