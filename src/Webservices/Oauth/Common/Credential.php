<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Common;

/**
 * Class Credential
 *
 * @package XGallery\Webservices\Oauth\Common
 */
class Credential
{

    /**
     * @var string
     */
    protected $consumerKey = '';

    /**
     * @var string
     */
    protected $consumerSecretKey = '';

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var string
     */
    protected $tokenSecret = '';

    /**
     * Credential constructor.
     *
     * @param        $consumerKey
     * @param        $consumerSecretKey
     * @param string $token
     * @param string $tokenSecret
     */
    public function __construct(
        $consumerKey,
        $consumerSecretKey,
        $token = '',
        $tokenSecret = ''
    ) {
        $this->consumerKey       = $consumerKey;
        $this->consumerSecretKey = $consumerSecretKey;
        $this->token             = $token;
        $this->tokenSecret       = $tokenSecret;
    }

    /**
     * @param $name
     *
     * @return string
     */
    public function __get($name)
    {
        if (!isset($this->{$name})) {
            return '';
        }

        return $this->{$name};
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }

    /**
     * @return string
     */
    public function getConsumerKey()
    {
        return $this->consumerKey;
    }

    /**
     * @return string
     */
    public function getConsumerSecretKey()
    {
        return $this->consumerSecretKey;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
}
