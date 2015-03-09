<?php
namespace Qiniu;

use Qiniu\Http\Request;

class Auth
{
    protected $accessKey = null;
    protected $secretKey = null;

    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    public function Sign($data)
    {
        $sign = hash_hmac("sha1", $data, $this->secretKey, true);
        return sprintf("%s:%s", $this->accessKey, $this->encode($sign));
    }

    public function SignData($data)
    {
        $data = $this->encode($data);
        return sprintf("%s:%s", $this->sign($data), $data);
    }

    public function SignRequest(Request $request)
    {
        $url = $request->url;
        $url = parse_url($url["path"]);
        $data = "";
        if (isset($url["path"])) {
            $data = $url["path"];
        }
        if (isset($url["query"])) {
            $data .= "?" . $url["query"];
        }
        $data .= "\n";

        if (isset($request->body) && $request->header["Content-Type"] === "application/x-www-form-urlencoded") {
            $data .= $request->body;
        }
        return $this->sign($data);
    }

    public function encode($string)
    {
        $find = array("+", "/");
        $replace = array("-", "_");
        return str_replace($find, $replace, base64_encode($string));
    }
}
