<?php
/**
 * Created by PhpStorm.
 * User: vietvu
 * Date: 2/12/2019
 * Time: 1:30 PM
 */

namespace XGallery\Webservices\Oauth\Common;


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