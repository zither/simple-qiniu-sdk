<?php
namespace Qiniu;

use Qiniu\Http\Request;
use InvalidArgumentException;

class Bucket
{
    protected $name;
    protected $config;
    protected $auth;
    protected $http;
    protected $policies;

    public function __construct($scope, Config $config)
    {
        $this->name = $scope;
        $this->config = $config;
        $this->auth = new Auth($config);
        $this->http = new Http;
        $this->policies = new Policies(['scope' => $scope]);
    }

    public function setPolicies(array $policies)
    {
        $this->policies->add($policies);
    }

    public function stat($file)
    {
        $url = $this->config->statUri($this->name, $file);
        $request = $this->createSignedRequest($url);
        return $this->http->sendRequest($request);
    }

    public function delete($file)
    {
        $url = $this->config->deleteUri($this->name, $file);
        $request = $this->createSignedRequest($url);
        return $this->http->sendRequest($request);
    }   

    public function put($file, $params = null, $overwrite = false)
    {
        $params = is_array($params) ? $params : ['key' => $params];
        if (!isset($params['key'])) {
            $params['key'] = $this->getSaveKey();
        }

        if ($overwrite && strpos($this->policies->get('scope'), ':') === false) {
            $this->setOverwriteScope($params['key']);
        }

        $token = $this->token();

        return $this->http->callMultiRequest($token, $file, $params);
    }

    protected function getSaveKey() 
    {
        return $this->policies->get('saveKey');
    }

    protected function setOverwriteScope($key)
    {
        if (is_null($key)) {
            throw new InvalidArgumentException(
                'You must set <key> or <saveKey> with overWrite mode.'
            );
        }
        $currentScope = $this->policies->get('scope');
        $this->policies->add([
            'scope' => sprintf('%s:%s', $currentScope, $key)
        ]);
    }

    public function token()
    {
        return $this->signPolicies();
    }

    protected function signPolicies()
    {
        $encodePolicies = json_encode($this->policies->all());
        return $this->auth->signData($encodePolicies);
    }

    protected function createSignedRequest($url, $headers = [], $body = null)
    {
        $request = new Request($url, $headers, $body);
        $request->withHeader(
            'Authorization', 
            'QBox ' . $this->auth->signRequest($request)
        );
        return $request;
    }
}
