<?php
namespace Qiniu;

class Bucket
{
    const EXTR_OVERWRITE = true;

    // 上传策略容器
    public $policyContainer = array();

    // 保存签名成功的upToken
    public $token = null;

    public $auth;
    public $http;
    public $config;

    public function __construct($scope, $accessKey, $secretKey)
    {
        $this->setPolicy(array('scope' => $scope));
        $this->auth = new \Qiniu\Auth($accessKey, $secretKey);
        $this->http = new \Qiniu\Http();
        $this->config = new \Qiniu\Config();
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
        if (is_null($key) && !$this->policyExists('saveKey')) {
            throw new \InvalidArgumentException(
                "You must set 'key' or 'saveKey' when overWrite is true."
            );
        }
        $this->setPolicy(array(
            'scope' => $this->getPolicy('scope') . ':' . $this->getSaveKey($key)
        ));
    }

    protected function getSaveKey($key) 
    {
        return is_null($key) ? $this->getPolicy('saveKey') : $key;
    }

    public function signPolicy()
    {
        $encodePolicy = json_encode($this->getPolicyContainer());
        $this->token = $this->auth->signWithData($encodePolicy);
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

    public function getPolicyContainer()
    {
        $expires = 3600;
        if ($this->policyExists('expires')) {
            $expires = $this->policyContainer['expires'];
        }
        $this->setPolicy(array('deadline' => time() + $expires));
        return $this->policyContainer;
    }

    public function getPolicy($name)
    {
        return $this->policyExists($name) ? $this->policyContainer[$name] : null;
    }

    public function setPolicy($policy)
    {
        if (!is_array($policy)) {
            throw new \InvalidArgumentException(
                'setPolicy method\'s parameter must be an array.'
            );
        }
        $this->mergePolicy($policy);
    }

    public function mergePolicy($policy)
    {
        $defaultPolicy = array(
            'scope', 'deadline', 'callbackUrl', 'callbackBody', 'returnUrl', 
            'asyncOps', 'endUser', 'expires', 'insertOnly', 
            'callbackHost', 'callbackBodyType', 'callbackFetchKey',
            'persistentOps', 'persistentNotifyUrl', 'persistentPipeline', 'saveKey',
            'fsizeLimit', 'detectMime', 'mimeLimit', 'returnBody'
        );
        foreach ($policy as $key => $value) {
            if (in_array($key, $defaultPolicy)) {
                $this->policyContainer[$key] = $value;
            }
        }
    }

    public function policyExists($name)
    {
        return isset($this->policyContainer[$name]);
    }
}
