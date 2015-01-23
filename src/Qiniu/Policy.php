<?php namespace Qiniu;

class Policy
{
    protected $container = array();

    public function set($policy)
    {
        if (!is_array($policy)) {
            throw new \InvalidArgumentException(
                'setPolicy method\'s parameter must be an array.'
            );
        }
        $this->merge($policy);
    }

    public function merge($policy)
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
                $this->container[$key] = $value;
            }
        }
    }

    public function getContainer()
    {
        $expires = 3600;
        if ($this->exists('expires')) {
            $expires = $this->container['expires'];
        }
        $this->set(array('deadline' => time() + $expires));
        return $this->container;
    }

    public function get($name)
    {
        return $this->exists($name) ? $this->container[$name] : null;
    }

    public function exists($name)
    {
        return isset($this->container[$name]);
    }
}
