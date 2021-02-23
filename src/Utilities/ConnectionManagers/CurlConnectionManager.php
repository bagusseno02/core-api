<?php
namespace coreapi\Utilities\ConnectionManagers;

use GuzzleHttp\Client;

class BaseCurlConnectionManager implements BaseConnectionManager
{

    private $client;

    /**
     * BaseCurlConnectionManager constructor.
     * @param      $baseUrl
     * @param bool $isVerifySslCertificate
     * @param int  $timeoutClient
     */
    public function __construct($baseUrl, $isVerifySslCertificate = true, $timeoutClient = 60)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'verify' => $isVerifySslCertificate,
            'timeout' => $timeoutClient
        ]);
    }

    public function requestGet($url, $data, $headers = [])
    {
        $options = [];
        if (isset($data) && !empty($data)) {
            $options['query'] = $data;
        }
        if (isset($data) && !empty($data)) {
            $options['headers'] = $headers;
        }

        return $this->client->get($url, $options);

    }

    public function requestPost($url, $data, $headers = [])
    {
        $options = [];
        if (isset($data) && !empty($data)) {
            $options['json'] = $data;
        }
        if (isset($data) && !empty($data)) {
            $options['headers'] = $headers;
        }

        return $this->client->post($url, $options);

    }

    public function requestDelete($url, $headers = [])
    {
        $options = [];
        if (isset($data) && !empty($data)) {
            $options['headers'] = $headers;
        }

        return $this->client->delete($url, $options);
    }

    public function requestPut($url, $data, $headers = [])
    {
        $options = [];
        if (isset($data) && !empty($data)) {
            $options['json'] = $data;
        }
        if (isset($data) && !empty($data)) {
            $options['headers'] = $headers;
        }

        return $this->client->put($url, $options);
    }
}