<?php
use Qiniu\Config;

require_once __DIR__ . '/common.php';

$file = $qiniu->file('sketch', 'test.png');
$response = $file->put($_FILES['file']['tmp_name']);
var_dump($response);
