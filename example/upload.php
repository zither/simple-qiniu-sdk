<?php
use Qiniu\Client;

require dirname(__DIR__) . "/vendor/autoload.php";

$accessKey = "Your access key";
$secretKey = "Your secret key";
$qiniu = new Client($accessKey, $secretKey);

$bucket = $qiniu->getBucket("sketch");
$response = $bucket->put($_FILES["file"]["tmp_name"], "test.png", true);
echo $response->getContent();
