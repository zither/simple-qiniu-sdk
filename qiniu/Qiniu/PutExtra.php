<?php
namespace Qiniu;

class PutExtra
{
    public $params = null;
    public $mimeType = null;
    public $crc32 = 0;
    public $checkCrc = 0;

    public function __construct($config)
    {
        if (isset($config['params'])) {
            $this->params = $config['params'];
        }
        if (isset($config['mimeType'])) {
            $this->mimeType = $config['mimeType'];
        }
        if (isset($config['crc32'])) {
            $this->crc32 = $config['crc32'];
        }
        if (isset($config['checkCrc'])) {
            $this->checkCrc = $config['checkCrc'];
        }
    }
}
