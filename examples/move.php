<?php
require_once __DIR__ . '/common.php';

$file = $qiniu->file('sketch', 'test.png');
$response = $file->move('sketch', 'copy.png');
var_dump($response);
