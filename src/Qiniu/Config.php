<?php
namespace Qiniu;

class Config
{
    /**
     * Qiniu up host 
     *
     * @var string
     */
    const QINIU_UP_HOST = "http://up.qiniu.com";

    /**
     * Qiniu rs host 
     *
     * @var string
     */
    const QINIU_RS_HOST = "http://rs.qbox.me";

    /**
     * Qiniu rsf host 
     *
     * @var string
     */
    const QINIU_RSF_HOST = "http://rsf.qbox.me";

    /**
     * rsURIStat 
     *
     * @param string $bucket
     * @param string $key
     * @return string
     */
    public function rsURIStat($bucket, $key)
    {
        return static::QINIU_RS_HOST . "/stat/" . $this->encode("$bucket:$key");
    }

    /**
     * rsURIDelete
     *
     * @param string $bucket
     * @param string $key
     * @return string
     */
    public function rsURIDelete($bucket, $key)
    {
        return static::QINIU_RS_HOST . "/delete/" . $this->encode("$bucket:$key");
    }

    /**
     * rsURICopy
     *
     * @param string $bucketSrc
     * @param string $keySrc
     * @param string $bucketDest
     * @param string $keyDest
     * @return string
     */
    public function rsURICopy($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::QINIU_RS_HOST . 
            "/copy/" . $this->encode("$bucketSrc:$keySrc") . 
            "/" . $this->encode("$bucketDest:$keyDest");
    }

    /**
     * rsURIMove
     *
     * @param string $bucketSrc
     * @param string $keySrc
     * @param string $bucketDest
     * @param string $keyDest
     * @return string
     */
    public function rsURIMove($bucketSrc, $keySrc, $bucketDest, $keyDest)
    {
        return static::QINIU_RS_HOST . 
            "/move/" . $this->encode("$bucketSrc:$keySrc") .
            "/" . $this->encode("$bucketDest:$keyDest");
    }

    /**
     * Helper method
     *
     * @param string $string
     * @return string
     */
    public function encode($string)
    {
        $find = array("+", "/");
        $replace = array("-", "_");
        return str_replace($find, $replace, base64_encode($string));
    }
}
