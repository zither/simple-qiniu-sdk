<?php
namespace Qiniu;

class Config
{
    const UP_HOST = 'http://up.qiniu.com';

    const RS_HOST = 'http://rs.qiniu.com';

    const RSF_HOST = 'http://rsf.qbox.me';

    const EXTR_OVERWRITE = true;

    protected $accessKey;

    protected $secretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function statUri($bucket, $key)
    {
        $encoded = $this->encode(sprintf('%s:%s', $bucket, $key));
        return sprintf('%s/stat/%s', static::RS_HOST, $encoded);
    }

    public function deleteUri($bucket, $key)
    {
        $encoded = $this->encode(sprintf('%s:%s', $bucket, $key));
        return sprintf('%s/delete/%s', static::RS_HOST, $encoded);
    }

    public function copyUri($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::RS_HOST . 
            "/copy/" . $this->encode("$bucketSrc:$keySrc") . 
            "/" . $this->encode("$bucketDest:$keyDest");
    }

    public function moveUri($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::RS_HOST . 
            "/move/" . $this->encode("$bucketSrc:$keySrc") .
            "/" . $this->encode("$bucketDest:$keyDest");
    }

    public function encode($string)
    {
        $find = ['+', '/'];
        $replace = ['-', '_'];
        return str_replace($find, $replace, base64_encode($string));
    }

    public function __get($key)
    {
        return $this->$key;
    }
}
