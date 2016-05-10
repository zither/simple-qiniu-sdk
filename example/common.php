<?php
use Qiniu\Client;

require dirname(__DIR__) . '/vendor/autoload.php';

$accessKey = 'Your access key';
$secretKey = 'Your secret key';
$qiniu = new Client($accessKey, $secretKey);
