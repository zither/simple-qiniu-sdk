<?php
require_once __DIR__ . '/common.php';

$file = $qiniu->file('sketch', 'test.png');

$batch = $qiniu->batch();

$response = $batch->stat($file)
    ->copy($file, 'sketch', 'copy', true)
    ->move($file, 'sketch', 'test')
    ->delete($file)
    ->run();

var_dump($response);
