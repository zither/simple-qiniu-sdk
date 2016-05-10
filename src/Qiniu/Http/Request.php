<?php
namespace Qiniu\Http;

class Request 
{
    public $url;
    public $headers;
    public $body;

    public function __construct($url, $headers = [], $body = null)
    {
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function withHeader($name, $value)
    {
        $this->headers = array_merge($this->headers, [$name => $value]);
        return $this;
    }
}
