<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service\Crawler;

use App\Service\HttpClient;
use stdClass;

/**
 * Class BaseCrawler
 * @package App\Service\Crawler
 */
class BaseCrawler extends HttpClient
{
    /**
     * @param array $fields
     * @param stdClass $item
     * @return stdClass
     */
    protected function assignFields($fields, $item)
    {
        // Assign fields to object
        foreach ($fields as $field) {
            if (!$field) {
                continue;
            }
            foreach ($field as $key => $value) {
                if (empty($value)) {
                    $item->{$key} = null;
                    continue;
                }

                if (is_array($value)) {
                    $item->{$key} = $value;
                    continue;
                }

                $item->{$key} = trim($value);
            }
        }

        return $item;
    }
}
