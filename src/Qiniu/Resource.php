<?php
namespace Qiniu;

use Qiniu\Http\Request;

class Resource
{
    protected $config;
    protected $auth;
    protected $http;
    protected $operations = [];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->auth = new Auth($config);
        $this->http = new Http;
    }

    protected function uriWithUpHost($uri)
    {
        return Config::UP_HOST . $uri;
    }

    protected function uriWithRsHost($uri)
    {
        return Config::RS_HOST . $uri;
    }

    protected function uriWithRsfHost($uri)
    {
        return Config::RSF_HOST . $uri;
    }

    protected function createSignedRequest($url, $headers = [], $body = null)
    {
        $request = new Request($url, $headers, $body);
        $request->withHeader(
            'Content-Type', 
            'application/x-www-form-urlencoded'
        );
        $request->withHeader(
            'Authorization', 
            'QBox ' . $this->auth->signRequest($request)
        );
        return $request;
    }
}
