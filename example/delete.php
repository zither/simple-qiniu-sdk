<?php
require_once __DIR__ . '/common.php';

$bucket = $qiniu->getBucket('sketch');
$response = $bucket->delete('test.png');
echo $response->getContent();
