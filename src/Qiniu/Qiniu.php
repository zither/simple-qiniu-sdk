<?php 
namespace Qiniu;

class Qiniu
{
    protected $config;

    public function __construct($accessKey, $secretKey)
    {
        $this->config = new Config($accessKey, $secretKey);
    }

    public function file($bucket, $filename)
    {
        $scope = sprintf('%s:%s', $bucket, $filename);
        return new File($this->config, ['scope' => $scope]);
    }

    public function fileList($bucket, array $params = [])
    {
        return new FileList($this->config, $bucket, $params);
    }

    public function batch()
    {
        return new Batch($this->config);
    }

    public function image()
    {
    
    }

    public function token()
    {

    }
}
