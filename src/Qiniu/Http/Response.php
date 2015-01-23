<?php
namespace Qiniu\Http;

class Response
{
    public $statusCode;
    public $header;
    public $contentLength;
    public $body;

    public function __construct($code, $header = array(), $body)
    {
        $this->statusCode = $code;
        $this->header = $header;
        $this->body = $body;
        $this->contentLength = strlen($body);
    }
}
