<?php 
namespace Qiniu;

use Qiniu\Bucket;

class Qiniu
{
    /**
     * @var string
     */
    protected $accessKey;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var object[]
     */
    protected $buckets = array();

    /**
     * Constructor
     *
     * @param $accessKey
     * @param $secretKey
     */
    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    /**
     * 获取指定的 Bucket 对象
     *
     * @param $name
     *
     * @return object \Qiniu\Bucket
     */
    public function getBucket($name)
    {
        if (!isset($this->buckets[$name])) {
            $this->buckets[$name] = new Bucket(
                $name, 
                $this->accessKey, 
                $this->secretKey
            ); 
        }
        return $this->buckets[$name]; 
    }
}
