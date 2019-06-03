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

use XGallery\Command\LinksysCommand;
use XGallery\Defines\DefinesCommand;

/**
 * Class LinksysDevices
 * @package App\Command\Linksys
 */
class LinksysDevices extends LinksysCommand
{
    private $devices;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('linksys:devices');

        parent::configure();
    }

    /**
     * @return boolean
     */
    public function prepareGetDevices()
    {
        $devices = $this->client->jNapCoreTransaction();

        if (!$devices) {
            return DefinesCommand::PREPARE_FAILED;
        }

        $this->devices = $devices[0]->output->devices;

        return DefinesCommand::PREPARE_SUCCEED;
    }

    /**
     *
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

        $this->log('Illegal devices are connected', 'warning', $illegalDevices);

        return true;
    }
}
