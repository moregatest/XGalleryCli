<?php
/**
 * Copyright (c) 2019 JOOservices Ltd
 * @author  Viet Vu <jooservices@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace XGallery\Webservices\Oauth\Oauth1\Traits;

use XGallery\Webservices\Oauth\Common\OauthHelper;

/**
 * Trait HasAuthorize
 * @package XGallery\Webservices\Oauth\Oauth1\Traits
 */
trait HasAuthorize
{
    use HasCredential;

    /**
     * Array of oauth parameters
     *
     * @var array
     */
    protected $oauthParameters = [];

    /**
     * Oauth signature
     *
     * @param string $method
     * @param string $uri
     * @param array  $parameters
     *
     * @return mixed
     */
    protected function sign($method, $uri, $parameters)
    {
        $parameters = array_merge($this->getOauthParameters(), $parameters);
        $oauthToken = $this->credential->getToken();

        if ($oauthToken) {
            $parameters['oauth_token'] = $oauthToken;
        }

        ksort($parameters);

        $parametersString = [];

        foreach ($parameters as $key => $value) {
            /**
             * @uses Both $key and $value MUST BE encoded
             */
            $parametersString[] = OauthHelper::encode($key).'='.OauthHelper::encode($value);
        }

        $baseSignature = OauthHelper::encode(strtoupper($method))
            .'&'.OauthHelper::encode($uri)
            .'&'.OauthHelper::encode(
                implode('&', $parametersString)
            );

        $parameters['oauth_signature'] = $this->getSignature($baseSignature);

        // For header we'll use encode for signature
        $this->oauthParameters                    = $parameters;
        $this->oauthParameters['oauth_signature'] = OauthHelper::encode($parameters['oauth_signature']);

        return $parameters;
    }

    /**
     * Get encrypted signature
     *
     * @param string $baseSignature
     *
     * @return string
     */
    protected function getSignature($baseSignature)
    {
        return base64_encode(hash_hmac('SHA1', $baseSignature, $this->getKey(), true));
    }

    /**
     * Get oauth header
     *
     * @return string
     */
    protected function getOauthHeader()
    {
        $header = 'OAuth ';

        foreach ($this->oauthParameters as $key => $value) {
            $header .= $key.'="'.$value.'",';
        }

        return rtrim($header, ',');
    }

    /**
     * Get oauth parameters
     *
     * @return array
     */
    protected function getOauthParameters()
    {
        return
            [
                'oauth_consumer_key' => $this->credential->getConsumerKey(),
                'oauth_nonce' => OauthHelper::getNonce(),
                'oauth_signature_method' => self::SIGNATURE_METHOD,
                'oauth_timestamp' => time(),
                'oauth_version' => self::VERSION,
            ];
    }

    /**
     * Get key
     *
     * @return string
     */
    private function getKey()
    {
        return OauthHelper::encode($this->credential->getConsumerSecretKey())
            .'&'.OauthHelper::encode($this->credential->getTokenSecret());
    }
}
