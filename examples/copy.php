<?php
require_once __DIR__ . '/common.php';

$file = $qiniu->file('sketch', 'test.png');
$response = $file->copy('sketch', 'copy.png', true);
var_dump($response);
