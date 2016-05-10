<?php
require_once __DIR__ . '/common.php';

$bucket = $qiniu->getBucket('sketch');
$response = $bucket->stat('test.png');
echo $response->getContent();
