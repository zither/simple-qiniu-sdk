<?php
namespace Qiniu\Http;

class Request 
{
    public $url;
    public $header;
    public $body;

    public function __construct($url, $header = array(), $body = null)
    {
        $this->url = $url;
        $this->header = $header;
        $this->body = $body;
    }
}
