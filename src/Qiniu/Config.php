<?php
namespace Qiniu;

class Config
{
    const UP_HOST = 'http://up.qiniu.com';
    const RS_HOST = 'http://rs.qiniu.com';
    const RSF_HOST = 'http://rsf.qbox.me';

    protected $accessKey;
    protected $secretKey;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function statUri($scope)
    {
        $encoded = $this->encode($scope);
        return sprintf('/stat/%s', $encoded);
    }

    public function deleteUri($scope)
    {
        $encoded = $this->encode($scope);
        return sprintf('/delete/%s', $encoded);
    }

    public function copyUri($uriSrc, $uriDest, $force = false)
    {
        return sprintf(
            '/copy/%s/%s/force/%s', 
            $this->encode($uriSrc),
            $this->encode($uriDest),
            $force ? 'true' : 'false'
        );
    }

    public function moveUri($uriSrc, $uriDest)
    {
        return sprintf(
            '/move/%s/%s',
            $this->encode($uriSrc),
            $this->encode($uriDest)
        );
    }

    public function listUri(array $params)
    {
        /*
        $needEncode = ['bucket', 'prefix', 'delimiter'];
        foreach ($params as $key => $value) {
            if (empty($value)) {
                unset($params[$key]);
                continue;
            }

            if (in_array($key, $needEncode)) {
                $params[$key] = $this->encode($value);
            }
        }
        */
        $query = http_build_query($params);
        return sprintf('/list?%s', $query);
    }

    public function batchUri()
    {
        return '/batch'; 
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
