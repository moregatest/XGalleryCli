<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Common;

/**
 * Class OauthHelper
 * @package XGallery\Webservices\Oauth\Common
 */
class OauthHelper
{

    /**
     * @return string
     */
    public static function getNonce()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function encode($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $aValue) {
                $value[$key] = self::encode($aValue);
            }
        } else {
            return str_replace(
                '%7E',
                '~',
                str_replace('+', ' ', rawurlencode($value))
            );
        }
    }
}