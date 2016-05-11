<?php
use Qiniu\Qiniu;

require dirname(__DIR__) . '/vendor/autoload.php';

$accessKey = 'Your access key';
$secretKey = 'Your secret key';
$qiniu = new Qiniu($accessKey, $secretKey);
