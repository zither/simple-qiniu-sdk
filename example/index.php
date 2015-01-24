<?php

define('ROOT', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    include ROOT . DS . 'template.php';
    exit;
}

require "Autoload.php";

Autoload::addNamespace('Qiniu', dirname(ROOT) . DS . 'src/Qiniu');
Autoload::register();

$accessKey = 'accessKey';
$secretKey = 'secretKey';

$qiniu = new \Qiniu\Qiniu($accessKey, $secretKey);

$bucket = $qiniu->getBucket('sketch');
$bucket->setPolicy(array(
    'returnBody' => '{
        "key": $(key),
        "name": $(fname)
    }',
    'expires' => 3600
));

if (!empty($_FILES)) {
    // 上传文件函数
    list($return, $error) = $bucket->put($_FILES['file']['tmp_name'], 'key.jpg', \Qiniu\Bucket::EXTR_OVERWRITE);
    echo is_null($error) ? json_encode($return) : json_encode($error);
}
