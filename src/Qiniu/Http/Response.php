<?php
namespace Qiniu\Http;

class Response
{
    public $statusCode;
    public $headers;
    public $contentLength;
    public $body;

    public function __construct($code, $headers = [], $body = null)
    {
        $this->statusCode = $code;
        $this->headers = $headers;
        $this->body = $body;
        $this->contentLength = strlen($body);
    }

    public function getContent()
    {
        return $this->body;  
    }

    public function __toString()
    {
        return $this->getContent();
    }
}
