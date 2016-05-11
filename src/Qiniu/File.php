<?php
namespace Qiniu;

class File extends Resource
{
    protected $policies;
    protected $params = [];
    protected $metadata = [];
    protected $isDeleted = false;

    public function __construct(Config $config, $scope)
    {
        parent::__construct($config);
        $this->policies = new Policies($scope);
    }

    public function withPolicies(array $policies)
    {
        $this->policies->add($policies);
        return $this;
    }

    public function put($file)
    {
        $token = $this->signPolicies();
        $params = $this->getParams();
        $response = $this->http->callMultiRequest($token, $file, $params);

        if ($response->statusCode === 200) {
            $this->isDeleted = false;
            return json_decode($response->getContent());
        }

        return false;
    }

    public function stat()
    {
        $url = $this->uriWithRsHost($this->statUri());
        $request = $this->createSignedRequest($url);
        $response = $this->http->sendRequest($request);
        if ($response->statusCode === 200) {
            return $this->metadata = json_decode($response->getContent());
        }

        return false;
    }

    public function statUri()
    {
        return $this->config->statUri($this->policies->get('scope'));
    }

    public function delete()
    {
        $url = $this->uriWithRsHost($this->deleteUri());
        $request = $this->createSignedRequest($url);
        $response = $this->http->sendRequest($request);
        if ($response->statusCode === 200) {
            return $this->isDeleted = true;
        }

        return false;
    }   

    public function deleteUri() 
    {
        return $this->config->deleteUri($this->policies->get('scope'));
    }

    public function copy($bucket, $filename, $force = false)
    {
        $uriDest = sprintf('%s:%s', $bucket, $filename);
        $url = $this->uriWithRsHost($this->copyUri($bucket, $filename, $force));
        $request = $this->createSignedRequest($url);
        $response = $this->http->sendRequest($request);

        if ($response->statusCode === 200) {
            return new static($this->config, ['scope' => $uriDest]);
        }

        return false;
    }

    public function copyUri($bucket, $filename, $force = false)
    {
        $uriSrc = $this->policies->get('scope');
        $uriDest = sprintf('%s:%s', $bucket, $filename);
        return $this->config->copyUri($uriSrc, $uriDest, $force);    
    }

    public function move($bucket, $filename)
    {
        $url = $this->uriWithRsHost($this->moveUri($bucket, $filename));
        $request = $this->createSignedRequest($url);
        $response = $this->http->sendRequest($request);

        if ($response->statusCode === 200) {
            $this->withPolicies(['scope' => $uriDest]);
            return true;
        }

        return false;
    }

    public function moveUri($bucket, $filename)
    {
        $uriSrc = $this->policies->get('scope');
        $uriDest = sprintf('%s:%s', $bucket, $filename);
        return $this->config->moveUri($uriSrc, $uriDest);        
    }

    public function signPolicies()
    {
        $encodePolicies = json_encode($this->policies->all());
        return $this->auth->signData($encodePolicies);
    }

    public function getParams()
    {
        $key = explode(':', $this->policies->get('scope'));
        if (!isset($this->params['key'])) {
            $this->params['key'] = $key[1];
        }
        return $this->params;
    }

    public function withParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function setMetadata(array $data) 
    {
        $this->metadata = $data;
    }
}
