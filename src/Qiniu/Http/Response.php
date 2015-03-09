<?php
namespace Qiniu\Http;

class Response
{
    public $statusCode;
    public $header;
    public $contentLength;
    public $body;

    public function __construct($code, $header = array(), $body = null)
    {
        $this->statusCode = $code;
        $this->header = $header;
        $this->body = $body;
        $this->contentLength = strlen($body);
    }

    public function __toString()
    {
        return $this->getContent();
    }

    public function getContent()
    {
        // 请求成功，返回七牛返回的 json 数据
        if ($this->statusCode === 200) {
            return $this->body;
        }
        // 请求失败，返回对应的错误码以及错误信息
        $data = array("code" => $this->statusCode, "error" => null);
        if (!empty($this->body)) {
            $data = array_merge($data, json_decode($this->body, true));
        }
        return json_encode($data);    
    }
}
