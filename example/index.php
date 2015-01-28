<?php

define('ROOT', __DIR__);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include ROOT . '/template.php';
    exit;
}

require "Autoload.php";
Autoload::addNamespace('Qiniu', dirname(ROOT) . '/src/Qiniu');
Autoload::register();

$accessKey = 'accessKey';
$secretKey = 'secretKey';

$qiniu = new \Qiniu\Qiniu($accessKey, $secretKey);
$bucket = $qiniu->getBucket('sketch');

// 上传文件函数
$response = $bucket->put($_FILES['file']['tmp_name'], "key.png");
echo $response->getContent();
