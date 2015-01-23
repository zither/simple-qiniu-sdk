<?php
namespace Qiniu;

class Bucket
{
    const EXTR_OVERWRITE = true;

    // instance 储蓄池
    public $container = array();

    // 保存签名成功的upToken
    public $token = null;

    public function __construct($scope, $accessKey, $secretKey)
    {
        $this->container['auth'] = new \Qiniu\Auth($accessKey, $secretKey);
        $this->container['http'] = new \Qiniu\Http();
        $this->container['policy'] = new \Qiniu\Policy();
        $this->setPolicy(array('scope' => $scope));
    }

    public function setPolicy($policy)
    {
        $this->policy->set($policy);
    }

    public function getUpToken()
    {
        return $this->signPolicy();
    }

    public function put($body, $key = null, $overWrite = false)
    {
        // 允许覆盖原文件
        if ($overWrite) {
            $this->setOverwriteScope($key);
        }
        $this->signPolicy();
        $request = $this->getMultiRequest($body, $this->getSaveKey($key));
        return $this->http->call($request); 
    }

    protected function setOverwriteScope($key)
    {
        if (is_null($key) && !$this->policy->exists('saveKey')) {
            throw new \InvalidArgumentException(
                "You must set 'key' or 'saveKey' when overWrite is true."
            );
        }
        $this->policy->set(array(
            'scope' => $this->policy->get('scope') . ':' . $this->getSaveKey($key)
        ));
    }

    protected function getSaveKey($key) 
    {
        return is_null($key) ? $this->policy->get('saveKey') : $key;
    }

    public function signPolicy()
    {
        $encodePolicy = json_encode($this->policy->getContainer());
        return $this->token = $this->auth->signWithData($encodePolicy);
    }

    public function getMultiRequest($params, $key)
    {
        $params = is_string($params) ? array('file' => $params) : $params;
        list($contentType, $body) = $this->http->getMultiData($params, $this->token, $key);
        $request = new \Qiniu\Http\Request(
            \Qiniu\Config::QINIU_UP_HOST,
            array('Content-Type' => $contentType),
            $body
        );
        return $request;
    }

    public function __get($name)
    {
        return isset($this->container[$name]) ? $this->container[$name] : null;
    }
}
