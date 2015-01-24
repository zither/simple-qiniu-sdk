<?php
namespace Qiniu\Http;

class Error
{
    public $error;
    public $reqid;
    public $details;
    public $code;

    public function __construct($code, $error)
    {
        $this->code = $code;
        $this->error = $error;
    }
}
