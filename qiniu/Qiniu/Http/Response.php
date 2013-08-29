<?php
namespace Qiniu\Http;

class Response
{
    public $statusCode;
    public $header;
    public $contentLength;
    public $body;

    public function __construct($code, $body)
    {
        $this->statusCode = $code;
        $this->header = array();
        $this->body = $body;
        $this->contentLength = strlen($body);
    }
}
