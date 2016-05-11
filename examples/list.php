<?php
require_once __DIR__ . '/common.php';

$list = $qiniu->fileList('sketch', [
    'delimiter' => '/', 
    //'prefix' => 'test/',
    //'limit' => 5,
]);
$files = $list->fetch();
var_dump($files);
