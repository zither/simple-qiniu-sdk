<?php
namespace Qiniu;

class Auth
{
    public $accessKey = null;
    public $secretKey = null;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function Sign($data) // => $token
    {
        $sign = hash_hmac('sha1', $data, $this->secretKey, true);
        return $this->accessKey . ':' . $this->encode($sign);
    }

    public function SignWithData($data) // => $token
    {
        $data = $this->encode($data);
        return $this->sign($data) . ':' . $data;
    }

    public function SignRequest(\Qiniu\Http\Request $request) // => ($token, $error)
    {
        $url = $request->url;
        $url = parse_url($url['path']);
        $data = '';
        if (isset($url['path'])) {
            $data = $url['path'];
        }
        if (isset($url['query'])) {
            $data .= '?' . $url['query'];
        }
        $data .= "\n";

        if (isset($request->body) && $request->header['Content-Type'] === 'application/x-www-form-urlencoded') {
            $data .= $request->body;
        }
        return $this->sign($data);
    }

    public function encode($str) // URLSafeBase64Encode
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($str));
    }
}
