<?php namespace Qiniu;

class Qiniu
{
    protected $accessKey;
    protected $secretKey;

    protected $buckets = array();

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function getBucket($name)
    {
        if (isset($this->buckets[$name])) {
            return $this->buckets[$name];
        }
        return new \Qiniu\Bucket($name, $this->accessKey, $this->secretKey); 
    }
}
