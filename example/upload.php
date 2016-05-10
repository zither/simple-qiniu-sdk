<?php
use Qiniu\Config;

require_once __DIR__ . '/common.php';

$bucket = $qiniu->getBucket('sketch');
$response = $bucket->put(
    $_FILES['file']['tmp_name'],
    'test.png', 
    Config::EXTR_OVERWRITE
);
echo $response->getContent();
