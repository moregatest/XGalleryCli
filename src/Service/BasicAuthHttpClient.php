<?php
/**
 *
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace App\Service;

/**
 * Class BasicAuthHttpClient
 * @package App\Service\BasicAuthentication
 */
class BasicAuthHttpClient extends HttpClient
{
    private $username;

    private $password;

    /**
     * @param $username
     * @param $password
     */
    public function setCredential($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return bool|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($method, $uri, array $options = [])
    {
        $options = array_merge($options, ['auth' => [$this->username, $this->password]]);

        return parent::request($method, $uri, $options);
    }
}
