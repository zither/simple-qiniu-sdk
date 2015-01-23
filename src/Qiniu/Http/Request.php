<?php
namespace Qiniu\Http;

class Request 
{
    public $url = null;
    public $header = null;
    public $body = null;

    public function __construct($url, $header= array(), $body = null)
    {
        $this->url = $url;
        $this->header = $header;
        $this->body = $body;
    }
}
