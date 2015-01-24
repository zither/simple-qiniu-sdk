<?php
namespace Qiniu;

use Qiniu\Auth;
use Qiniu\Http;
use Qiniu\Policy;

class Bucket
{
    const EXTR_OVERWRITE = true;

    /**
     * @var object[]
     */
    protected $container = array();

    /**
     * @var string
     */
    protected $token = null;

    /**
     * Constructer
     *
     * @param $scope bucket name
     * @param $accessKey
     * @param $secretKey
     */
    public function __construct($scope, $accessKey, $secretKey)
    {
        $this->container['auth'] = new Auth($accessKey, $secretKey);
        $this->container['http'] = new Http();
        $this->container['policy'] = new Policy();
        $this->setPolicy(array('scope' => $scope));
    }

    /**
     * 设置上传策略
     *
     * @param $policy
     */
    public function setPolicy($policy)
    {
        $this->policy->set($policy);
    }

    /**
     * 获取 upload token
     *
     * @return string
     */
    public function getUpToken()
    {
        return $this->signPolicy();
    }

    /**
     * 上传文件，overwrite 为 true 时为 put（更新）模式
     *
     * @param $body
     * @param $key
     * @param $overwrite
     *
     * @return array
     */
    public function put($body, $key = null, $overwrite = false)
    {
        if ($overwrite && strpos($this->policy->get('scope'), ':') === false) {
            $this->setOverwriteScope($key);
        }
        $this->signPolicy();
        $key = $this->getSaveKey($key);
        return $this->http->callMultiRequest($body, $this->token, $key);
    }

    /**
     * Overwrite 为真时必须将 scope 设置为 bucket:<key> 模式
     *
     * @param $key
     */
    protected function setOverwriteScope($key)
    {
        if (is_null($key) && !$this->policy->exists('saveKey')) {
            throw new \InvalidArgumentException(
                "You must set <key> or <saveKey> when overWrite is true."
            );
        }
        $currentScope = $this->policy->get('scope');
        $this->policy->set(array(
            'scope' => sprintf("%s:%s", $currentScope, $this->getSaveKey($key))
        ));
    }

    /**
     * 当 <key> 未设置时尝试从 policy 中获取 <saveKey>
     *
     * @param $key
     *
     * @return mixed <saveKey> 也未设置则返回 null
     */
    protected function getSaveKey($key) 
    {
        return is_null($key) ? $this->policy->get('saveKey') : $key;
    }

    /**
     * 上传策略签名
     *
     * @return string 返回 upload token
     */
    protected function signPolicy()
    {
        $encodePolicy = json_encode($this->policy->getContainer());
        return $this->token = $this->auth->signWithData($encodePolicy);
    }

    /**
     * Container getter
     *
     * @param $name
     *
     * @return object 辅助对象
     */
    public function __get($name)
    {
        return isset($this->container[$name]) ? $this->container[$name] : null;
    }
}
