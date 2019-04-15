<?php

namespace XGallery\Entities\Now;

/**
 * Class Delivery
 * @package XGallery\Entities\Now
 */
class Delivery
{
    public $name;
    public $address;
    public $short_description;
    public $city_id;
    public $district_id;
    public $restaurant_id;
    public $restaurant_url;
    public $delivery_id;
    public $foody_service_id;
    public $is_city_alert;
    public $is_favorite;
    public $is_now_delivery;
    public $is_quality_merchant;
    public $brand_id;
    public $min_price;
    public $max_price;
    public $has_contract;
    public $prepare_duration;
    public $position;
    public $total_order;
    public $url;

    /**
     * Delivery constructor.
     * @param object $deliveryDetail
     */
    public function __construct($deliveryDetail)
    {
        $this->name                = $deliveryDetail->name;
        $this->address             = $deliveryDetail->address;
        $this->short_description   = $deliveryDetail->short_description;
        $this->city_id             = $deliveryDetail->city_id;
        $this->district_id         = $deliveryDetail->district_id;
        $this->restaurant_id       = $deliveryDetail->restaurant_id;
        $this->restaurant_url      = $deliveryDetail->restaurant_url;
        $this->delivery_id         = $deliveryDetail->delivery_id;
        $this->foody_service_id    = isset($deliveryDetail->foody_service_id) ? (int)$deliveryDetail->foody_service_id : 0;
        $this->is_city_alert       = isset($deliveryDetail->is_city_alert) ? (int)$deliveryDetail->is_city_alert : 0;
        $this->is_favorite         = isset($deliveryDetail->is_favorite) ? (int)$deliveryDetail->is_favorite : 0;
        $this->is_now_delivery     = isset($deliveryDetail->is_now_delivery) ? (int)$deliveryDetail->is_now_delivery : 0;
        $this->is_quality_merchant = isset($deliveryDetail->is_quality_merchant) ? (int)$deliveryDetail->is_quality_merchant : 0;

        if (isset($deliveryDetail->brand)) {
            $this->brand_id = $deliveryDetail->brand->brand_id;
        }

        if (isset($deliveryDetail->price_range)) {
            $this->min_price = $deliveryDetail->price_range->min_price;
            $this->max_price = $deliveryDetail->price_range->max_price;
        }

        $this->has_contract     = (int)$deliveryDetail->delivery->has_contract;
        $this->prepare_duration = (int)$deliveryDetail->delivery->prepare_duration;
        $this->position         = json_encode($deliveryDetail->position);
        $this->total_order      = $deliveryDetail->total_order;
        $this->url              = $deliveryDetail->url;
    }
}
