<?php 
namespace Qiniu;

class Client
{
    protected $config;

    protected $buckets = [];


    public function __construct($accessKey, $secretKey)
    {
        $this->config = new Config($accessKey, $secretKey);
    }

    public function getBucket($name)
    {
        if (! isset($this->buckets[$name])) {
            $this->buckets[$name] = new Bucket($name, $this->config); 
        }

        return $this->buckets[$name]; 
    }
}
