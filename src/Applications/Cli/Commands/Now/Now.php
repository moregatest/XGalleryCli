<?php

namespace XGallery\Applications\Cli\Commands\Now;

use Doctrine\DBAL\DBALException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use XGallery\Applications\Cli\Commands\AbstractCommandNow;

/**
 * Class Now
 * @package XGallery\Applications\Cli\Commands\Now
 */
final class Now extends AbstractCommandNow
{
    /**
     * @var object
     */
    protected $metadata;

    /**
     * @var object
     */
    protected $deliveryMetadata;

    /**
     * configure
     *
     * @throws \ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('Generate base data from NOW');

        parent::configure();
    }

    /**
     * prepareGetMetadata
     * @return boolean
     *
     * @throws GuzzleException
     * @throws InvalidArgumentException
     */
    protected function prepareGetMetadata()
    {
        // Tablenow
        $this->metadata = $this->now->getMetadata();
        // DeliveryNow
        $this->deliveryMetadata = $this->now->getDeliveryNowMetadata();

        if (!$this->metadata || !$this->deliveryMetadata) {
            return self::PREPARE_FAILED;
        }

        return self::PREPARE_SUCCEED;
    }

    /**
     * processInsertDatabase
     *
     * @return boolean
     * @throws DBALException
     */
    protected function processTableNow()
    {
        $this->model->insertTableNow($this->metadata);

        return true;
    }

    /**
     * processDeliveryNow
     *
     * @return boolean
     */
    protected function processDeliveryNow()
    {
        $this->model->insertDeliveryNow($this->deliveryMetadata);

        return true;
    }
}
