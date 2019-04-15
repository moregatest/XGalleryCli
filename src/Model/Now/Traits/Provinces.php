<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use XGallery\Applications\Cli\Commands\Now\Deliveries;

/**
 * Trait Provinces
 * @package XGallery\Model\Now\Traits
 */
trait Provinces
{
    /**
     * getDistrictIds
     * @param int $city
     * @return mixed[]
     * @throws DBALException
     */
    public function getDistricts($city = Deliveries::SG_CITY_ID)
    {
        return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_districts` WHERE `city_id` = '.(int)$city)
            ->fetchAll(FetchMode::STANDARD_OBJECT);
    }
}
