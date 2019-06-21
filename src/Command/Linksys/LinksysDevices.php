<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Command\Linksys;

use App\Service\Router\LinksysClient;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use XGallery\AbstractCommand;

/**
 * Class LinksysDevices
 * @package App\Command\Linksys
 */
final class LinksysDevices extends AbstractCommand
{
    /**
     * @var LinksysClient
     */
    private $client;

    /**
     * @var array
     */
    private $devices;

    /**
     * LinksysDevices constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);

        $this->client = $this->getClient();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Verify list of connected devices');

        parent::configure();
    }

    /**
     * @param string $name
     * @return LinksysClient
     */
    protected function getClient($name = '')
    {
        static $instance;

        if ($instance) {
            return $instance;
        }

        $instance = new LinksysClient;

        return $instance;
    }

    /**
     * @return boolean
     * @throws GuzzleException
     */
    public function prepareGetDevices()
    {
        $this->devices = $this->client->jNapCoreTransaction();

        if (!$this->devices) {
            return self::PREPARE_FAILED;
        }

        $this->devices = $this->devices[0]->output->devices;

        return self::PREPARE_SUCCEED;
    }

    /**
     * @return boolean
     */
    public function processVerify()
    {
        $acceptedDevices = [
            'EA7500' => 'C0:56:27:51:18:6A',
            'iPhone 7 Red' => '20:EE:28:D6:E0:F2',
            'Link\'s laptop' => '8C:A9:82:55:91:F8',
            'Chromecast Gen 1' => 'A4:77:33:0A:55:D7',
            'Chromecast Gen 2' => 'F4:F5:D8:FB:B2:B2',
            'Workspace' => '30:9C:23:09:2E:26',
            'S8+' => '30:07:4D:58:6A:D0',
            'iPhone 8+' => '3C:2E:F9:09:16:5D',
        ];

        $illegalDevices = [];

        foreach ($this->devices as $device) {
            $knowAddress = end($device->knownMACAddresses);

            if (!in_array(strtoupper($knowAddress), $acceptedDevices)) {
                $illegalDevices [] = $device;
            }
        }

        if (empty($illegalDevices)) {
            $this->log('No illegal devices');

            return true;
        }

        $this->log('Illegal devices are connected', 'warning');

        foreach ($illegalDevices as $illegalDevice) {
            $this->log('<options=bold>' . reset($illegalDevice->knownMACAddresses) . '</>', 'warning');
        }

        return true;
    }
}
