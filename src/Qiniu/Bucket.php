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
     * Constructor
     *
     * @param $scope bucket name
     * @param $accessKey
     * @param $secretKey
     */
    public function __construct($scope, $accessKey, $secretKey)
    {
        $this->container["auth"] = new Auth($accessKey, $secretKey);
        $this->container["http"] = new Http();
        $this->container["policy"] = new Policy();
        $this->setPolicy(array("scope" => $scope));
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
     * @param $file
     * @param $params 文件名以及自定义参数
     * @param $overwrite
     *
     * @return object \Qiniu\Http\Response
     */
    public function put($file, $params = null, $overwrite = false)
    {
        // 将 params 格式化为一个包含 key 关键词的数组
        $params = is_array($params) ? $params: array("key" => $params);
        if (!isset($params["key"])) {
            $params["key"] = $this->getSaveKey();
        }

        if ($overwrite && strpos($this->policy->get("scope"), ":") === false) {
            $this->setOverwriteScope($params["key"]);
        }
        $token = $this->signPolicy();
        return $this->http->callMultiRequest($token, $file, $params);
    }

    /**
     * Overwrite 为真时必须将 scope 设置为 bucket:<key> 模式
     *
     * @param $key
     */
    protected function setOverwriteScope($key)
    {
        if (is_null($key)) {
            throw new \InvalidArgumentException(
                "You must set <key> or <saveKey> when overWrite is true."
            );
        }
        $currentScope = $this->policy->get("scope");
        $this->policy->set(array(
            "scope" => sprintf("%s:%s", $currentScope, $key)
        ));
    }

    /**
     * 当 <key> 未设置时尝试从 policy 中获取 <saveKey>
     *
     * @return mixed string or  null
     */
    protected function getSaveKey() 
    {
        return $this->policy->get("saveKey");
    }

    /**
     * 上传策略签名
     *
     * @return string 返回 upload token
     */
    protected function signPolicy()
    {
        $encodePolicy = json_encode($this->policy->getContainer());
        return $this->token = $this->auth->signData($encodePolicy);
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
