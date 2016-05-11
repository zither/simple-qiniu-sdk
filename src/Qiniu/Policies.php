<?php 
namespace Qiniu;

class Policies
{
    protected $items = [];

    protected $validPolicies = [
        'scope' => true, 
        'deadline' => true, 
        'callbackUrl' => true, 
        'callbackBody' => true, 
        'returnUrl' => true, 
        'asyncOps' => true, 
        'endUser' => true, 
        'expires' => true, 
        'insertOnly' => true,     
        'callbackHost' => true, 
        'callbackBodyType' => true, 
        'callbackFetchKey' => true,
        'persistentOps' => true, 
        'persistentNotifyUrl' => true, 
        'persistentPipeline' => true,
        'saveKey' => true, 
        'fsizeLimit' => true, 
        'detectMime' => true, 
        'mimeLimit' => true, 
        'returnBody' => true,
    ];

    public function __construct(array $policies = [])
    {
        $this->add($policies);
    }

    public function add(array $policies)
    {
        $validPolicies = array_intersect_key($policies, $this->validPolicies);
        return $this->items = array_merge($this->items, $validPolicies);
    }

    public function all()
    {
        $expires = $this->exists('expires') ? $this->get('expires') : 3600;
        return $this->add(['deadline' => time() + $expires]);
    }

    public function get($name, $default = null)
    {
        return $this->exists($name) ? $this->items[$name] : $default;
    }

    public function exists($name)
    {
        return isset($this->items[$name]);
    }

    public function remove($name)
    {
        unset($this->items[$name]);
    }
}
