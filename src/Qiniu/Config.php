<?php
namespace Qiniu;

class Config
{
    const QINIU_UP_HOST = "http://up.qiniu.com";
    const QINIU_RS_HOST = "http://rs.qbox.me";
    const QINIU_RSF_HOST = "http://rsf.qbox.me";

    public function rsURIStat($bucket, $key)
    {
        return static::QINIU_RS_HOST . "/stat/" . $this->encode("$bucket:$key");
    }

    public function rsURIDelete($bucket, $key)
    {
        return static::QINIU_RS_HOST . "/delete/" . $this->encode("$bucket:$key");
    }

    public function rsURICopy($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::QINIU_RS_HOST
            . "/copy/" . $this->encode("$bucketSrc:$keySrc")
            . "/" . $this->encode("$bucketDest:$keyDest");
    }

    public function rsURIMove($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::QINIU_RS_HOST
            . "/move/" . $this->encode("$bucketSrc:$keySrc")
            . "/" . $this->encode("$bucketDest:$keyDest");
    }

    public function encode($string)
    {
        $find = array("+", "/");
        $replace = array("-", "_");
        return str_replace($find, $replace, base64_encode($string));
    }
}
