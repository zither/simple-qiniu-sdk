<?php
namespace Qiniu\Http;

class Error
{
    public $error;	 // string
    public $reqid;	 // string
    public $details; // []string
    public $code;	 // int

    public function __construct($code, $err)
    {
        $this->code = $code;
        $this->error = $err;
    }
}
