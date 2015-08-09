<?php
namespace Qiniu;

use Qiniu\Http\Request;

class Auth
{
    /**
     * Access key
     *
     * @var string
     */
    protected $accessKey;

    /**
     * Secret key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * __construct
     *
     * @param mixed $accessKey
     * @param mixed $secretKey
     */
    public function __construct($accessKey, $secretKey)
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
    }

    /**
     * Sign
     *
     * @param string $data
     * @return string
     */
    public function sign($data)
    {
        $sign = hash_hmac("sha1", $data, $this->secretKey, true);
        return sprintf("%s:%s", $this->accessKey, $this->encode($sign));
    }

    /**
     * Sign data
     *
     * @param mixed $data
     * @return string
     */
    public function signData($data)
    {
        $data = $this->encode($data);
        return sprintf("%s:%s", $this->sign($data), $data);
    }

    /**
     * Sign Request
     *
     * @param Request $request
     * @return string
     */
    public function signRequest(Request $request)
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
