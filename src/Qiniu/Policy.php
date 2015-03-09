<?php 
namespace Qiniu;

class Policy
{
    /**
     * @var string[]  
     */
    protected $container = array();

    /**
     * 设置上传策略
     *
     * @param $policy
     */
    public function set($policy)
    {
        if (!is_array($policy)) {
            throw new \InvalidArgumentException(
                "setPolicy method's parameter must be of the type array."
            );
        }
        $this->merge($policy);
    }

    /**
     * 合并有效的上传策略
     *
     * @param $policy
     */
    protected function merge($policy)
    {
        $defaultPolicy = array(
            "scope", "deadline", "callbackUrl", "callbackBody", "returnUrl", 
            "asyncOps", "endUser", "expires", "insertOnly", 
            "callbackHost", "callbackBodyType", "callbackFetchKey",
            "persistentOps", "persistentNotifyUrl", "persistentPipeline",
            "saveKey", "fsizeLimit", "detectMime", "mimeLimit", "returnBody"
        );
        $validPolicy = array_intersect_key($policy, array_flip($defaultPolicy));
        $this->container = array_merge($this->container, $validPolicy);
    }

    /**
     * 获取所有上传策略
     *
     * @return array
     */
    public function getContainer()
    {
        $expires = 3600;
        if ($this->exists("expires")) {
            $expires = $this->container["expires"];
        }
        $this->set(array("deadline" => time() + $expires));
        return $this->container;
    }

    /**
     * 获取指定的上传策略
     *
     * @param $name
     *
     * @return mixed 策略不存在时返回 null
     */
    public function get($name)
    {
        return $this->exists($name) ? $this->container[$name] : null;
    }

    /**
     * 检查策略是否设置
     *
     * @param $name
     *
     * @return boolean
     */
    public function exists($name)
    {
        return isset($this->container[$name]);
    }
}
