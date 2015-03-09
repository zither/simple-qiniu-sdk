<?php

require __DIR__ . "/Autoload.php";
Autoload::addNamespace("Qiniu", dirname(__DIR__) . "/src/Qiniu");
Autoload::register();

$accessKey = "accessKey";
$secretKey = "secretKey";
$qiniu = new \Qiniu\Qiniu($accessKey, $secretKey);

$bucket = $qiniu->getBucket("sketch");
$response = $bucket->put($_FILES["file"]["tmp_name"], "test.png");
echo $response->getContent();
