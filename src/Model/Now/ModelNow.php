<?php

namespace XGallery\Model\Now;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use XGallery\Model\BaseModel;
use XGallery\Model\Now\Traits\Cuisines;
use XGallery\Model\Now\Traits\DeliveryNow;
use XGallery\Model\Now\Traits\Menus;
use XGallery\Model\Now\Traits\Promotions;
use XGallery\Model\Now\Traits\Provinces;
use XGallery\Model\Now\Traits\TableNow;

/**
 * Class ModelNow
 * @package XGallery\Model
 */
class ModelNow extends BaseModel
{
    use DeliveryNow;
    use TableNow;
    use Cuisines;
    use Promotions;
    use Menus;
    use Provinces;

    /**
     * getSorts
     *
     * @return mixed[]
     */
    public function getSorts()
    {
        try {
            return $this->connection->executeQuery(' SELECT * FROM `xgallery_now_restaurant_sort_types`')
                ->fetchAll(FetchMode::STANDARD_OBJECT);
        } catch (DBALException $exception) {
            $this->errors[] = $exception->getMessage();
        }
    }
}
