<?php
namespace Qiniu;

use Qiniu\Http\Request;

class Auth
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function sign($data)
    {
        $sign = hash_hmac('sha1', $data, $this->config->secretKey, true);
        return sprintf('%s:%s', $this->config->accessKey, $this->encode($sign));
    }

    public function signData($data)
    {
        $data = $this->encode($data);
        return sprintf('%s:%s', $this->sign($data), $data);
    }

    public function signRequest(Request $request)
    {
        $url = parse_url($request->url);
        $data = '';
        if (isset($url['path'])) {
            $data = $url['path'];
        }
        if (isset($url['query'])) {
            $data .= '?' . $url['query'];
        }
        $data .= "\n";

        if (isset($request->body) && $request->headers['Content-Type'] === 'application/x-www-form-urlencoded') {
            $data .= $request->body;
        }
        return $this->sign($data);
    }

    public function encode($string)
    {
        $find = ['+', '/'];
        $replace = ['-', '_'];
        return str_replace($find, $replace, base64_encode($string));
    }
}
