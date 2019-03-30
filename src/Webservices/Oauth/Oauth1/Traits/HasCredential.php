<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Oauth1\Traits;

use XGallery\Webservices\Oauth\Common\Credential;

/**
 * Class HasCredential
 *
 * @package XGallery\Webservices\Oauth\Oauth1\Traits
 */
trait HasCredential
{
    /**
     * Credential class
     *
     * @var Credential
     */
    protected $credential;

    /**
     * setCredential
     *
     * @param string $consumerKey
     * @param string $consumerSecretKey
     * @param string $token
     * @param string $tokenSecret
     * @return $this
     */
    public function setCredential($consumerKey, $consumerSecretKey, $token = '', $tokenSecret = '')
    {
        $this->credential = new Credential($consumerKey, $consumerSecretKey, $token, $tokenSecret);

        return $this;
    }

    /**
     * getCredential
     *
     * @return Credential
     */
    public function getCredential()
    {
        return $this->credential;
    }
}
