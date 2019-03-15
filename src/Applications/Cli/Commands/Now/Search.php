<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Applications\Cli\Commands\Now;

use Nahid\JsonQ\Exceptions\ConditionNotAllowedException;
use Nahid\JsonQ\Exceptions\FileNotFoundException;
use Nahid\JsonQ\Exceptions\InvalidJsonException;
use Nahid\JsonQ\Jsonq;
use ReflectionException;
use Symfony\Component\Filesystem\Filesystem;
use XGallery\Applications\Cli\Commands\AbstractCommandNow;
use XGallery\Defines\DefinesNow;

/**
 * Class Search
 * @package XGallery\Applications\Cli\Commands\Now
 */
class Search extends AbstractCommandNow
{

    /**
     * @throws ReflectionException
     */
    protected function configure()
    {
        $this->setDescription('');
        $this->options = [
            'discount' => [
                'default' => '',
                'description' => 'Discount by percent',
            ],
            'discount_amount' => [
                'default' => '',
                'description' => '',
            ],
            'max_discount_amount' => [
                'default' => '',
                'description' => '0 for unlimited',
            ],
        ];

        parent::configure();
    }

    /**
     * @return bool|void
     * @throws ConditionNotAllowedException
     * @throws FileNotFoundException
     * @throws InvalidJsonException
     */
    protected function process()
    {
        $deliveries = $this->now->searchDetailDeliveries(
            [
                'district_ids' => [
                    DefinesNow::DISTRICT_1,
                    DefinesNow::DISTRICT_3,
                    DefinesNow::DISTRICT_4,
                    DefinesNow::DISTRICT_BT,
                    DefinesNow::DISTRICT_TB,
                    DefinesNow::DISTRICT_PN,
                    DefinesNow::DISTRICT_GV,
                ],
                'city_id' => DefinesNow::CITY_SG,
            ]
        );

        $json = new Jsonq;
        $json->json(json_encode($deliveries));
        $json->select('name');

        $discountAmount    = explode(',', trim($this->input->getOption('discount')));
        $maxDiscountAmount = explode(',', trim($this->input->getOption('max_discount_amount')));

        if (!empty($discountAmount)) {
            foreach ($discountAmount as $index => $value) {
                $json->orWhere('delivery.promotions.'.$index.'.discount', 'startswith', $value);
            }
        }

        if (!empty($maxDiscountAmount)) {
            foreach ($maxDiscountAmount as $index => $value) {
                $json->orWhere('delivery.promotions.'.$index.'.max_discount_amount', '>=', $value);
            }
        }

        (new Filesystem())->dumpFile(XGALLERY_ROOT.'/deliveries.json', $json->toJson());
    }
}