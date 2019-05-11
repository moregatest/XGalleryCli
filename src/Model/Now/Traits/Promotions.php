<?php

namespace XGallery\Model\Now\Traits;

use Doctrine\DBAL\FetchMode;

/**
 * Trait Promotions
 * @package XGallery\Model\Now\Traits
 */
trait Promotions
{
    public function getDeliveriesWithPromotion($categories)
    {
        // Top 5 best promotions in categories
        $top5Query                          = 'SELECT * FROM `xgallery_now_deliveries` AS `deliveries`
    INNER JOIN `xgallery_now_promotions` AS `promotions` ON `promotions`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories_xref` AS `category_xref` ON `category_xref`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories` AS `categories` ON `categories`.`id` = `category_xref`.`category_id`
WHERE `categories`.`id` IN ('.$categories.') ORDER BY `discount_amount` DESC LIMIT 5';
        $top5QueryByPercent                 = 'SELECT * FROM `xgallery_now_deliveries` AS `deliveries`
    INNER JOIN `xgallery_now_promotions` AS `promotions` ON `promotions`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories_xref` AS `category_xref` ON `category_xref`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories` AS `categories` ON `categories`.`id` = `category_xref`.`category_id`
WHERE `promotions`.`discount_value_type` = 1 AND `categories`.`id` IN ('.$categories.') ORDER BY `discount_amount` DESC LIMIT 5';
        $top5QueryByPercentWithMaxUnlimited = 'SELECT * FROM `xgallery_now_deliveries` AS `deliveries`
    INNER JOIN `xgallery_now_promotions` AS `promotions` ON `promotions`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories_xref` AS `category_xref` ON `category_xref`.`delivery_id` = `deliveries`.`delivery_id`
    INNER JOIN `xgallery_now_categories` AS `categories` ON `categories`.`id` = `category_xref`.`category_id`
WHERE `promotions`.`max_discount_amount` = 0 AND `categories`.`id` IN ('.$categories.') ORDER BY `discount_amount` DESC LIMIT 5';

        $list['top_promotions']                               = $this->connection->executeQuery($top5Query)->fetchAll(FetchMode::STANDARD_OBJECT);
        $list['top_promotions_by_percent']                    = $this->connection->executeQuery($top5QueryByPercent)->fetchAll(FetchMode::STANDARD_OBJECT);
        $list['top_promotions_by_percent_with_max_unlimited'] = $this->connection->executeQuery($top5QueryByPercentWithMaxUnlimited)->fetchAll(FetchMode::STANDARD_OBJECT);
        $categories                                           = $this->connection->executeQuery('SELECT `name` FROM `xgallery_now_categories` WHERE `id` IN ('.$categories.')')->fetchAll(FetchMode::COLUMN);

        return [
            'list' => $list,
            'categories' => implode(', ', $categories),
        ];
    }
}
