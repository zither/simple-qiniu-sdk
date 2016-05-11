<?php
require_once __DIR__ . '/common.php';

$file = $qiniu->file('sketch', 'test.png');
$response = $file->delete();
var_dump($response);
