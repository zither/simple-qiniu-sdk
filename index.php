<?php
require "Autoload.php";

Autoload::addNamespace('Qiniu', 'src/Qiniu');
Autoload::register();

$accessKey = 'accesskey';
$secretKey = 'secretkey';

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
    list($return, $error) = $bucket->put($_FILES['file']['tmp_name'], 'test.jpg', \Qiniu\Bucket::EXTR_OVERWRITE);
    if ($error !== null) {
        echo json_encode($error);
    } else {
        echo json_encode($return);
    }
    exit;
}
?>
<html>
    <meta charset="utf-8">
    <head> 
        <title>Simple Qiniu SDK</title>
    </head> 
    <h1>Simple Qiniu SDK</h1>
    <div>
        <p>重写之前的 Simple Qiniu SDK，目前只实现了上传文件，</p>
        <p>源码下载地址：<a href="https://github.com/zither/simple-qiniu-sdk">https://github.com/zither/simple-qiniu-sdk</a></p>
        <p>在线测试：</p>
        <form id="myForm" method="post"  enctype="multipart/form-data">
            <input name="file" type="file" />
            <button id="upload" type="submit">上传到七牛</button>
        </form>
    </div>
</html>
