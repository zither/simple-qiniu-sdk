<?php
/**
 * @file test.php
 * @brief 简化版的七牛 PHP SDK 使用方法示例，为了获取 callback，本示例使用了自己的业务服务器。
 * @author JonChou <ilorn.mc@gmail.com>
 * @date 2013-08-29
 */
require "qiniu/Qiniu/Client.php";

// 如果你使用 composer,请不要注册自带的 autoloader。
\Qiniu\Client::registerAutoloader();

// 实例化七牛 SDK
$config = array(
            'access_key' => 'Your access key',
            'secret_key' => 'Your secret key');
$sdk = new \Qiniu\Client($config);

// 请设置为你自己的 bucket 名称
$bucket = 'sketch';
// 测试用的文件名 key
$key = 'test.png';

// 自定义 returnBody 参数，省略该参数会返回默认字段
$params = array(
            // 直接获取 upToken 时必须设置 scope
            'scope' => $bucket,
            // 自定义 returnBody，支持自定义变量及魔法变量
            'returnBody' => '{
                                "key": $(key),
                                "name": $(fname),
                                "size": $(fsize),
                                "type": $(mimeType),
                                "hash": $(etag),
                                "w": $(imageInfo.width),
                                "h": $(imageInfo.height),
                                "description": $(x:description)
                             }',
            // token 过期时间
            'expires' => 3600);

if (!empty($_FILES)) {
    // 如果你只需要简单上传本地文件，这个参数设为本地文件路径即可
    //$body = '@' . $_FILES['file']['tmp_name'];
    // 需要自定义变量时需使用数组形式
    $body = array(
                // 本地文件路径
                'file' => '@' . $_FILES['file']['tmp_name'],
                // 自定义变量
                'x:user' => 'Pencily',
                'x:description' => '如果你看到这句话，就说明自定义变量(x:description)生效了。'
                );
    // 上传文件函数
    list($return, $error) = $sdk->putFile($bucket, 'test/' . time() . '.jpg', $body, $params);
    if ($error !== null) {
        echo json_encode($error);
    } else {
        echo json_encode($return);
    }
    exit;
}

// 查看图片状态
//list($ret, $err) = $sdk->rsStat($bucket, $key);
//echo "Qiniu_RS_Stat result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    var_dump($ret);
//}

// 复制图片已完成
//$err = $sdk->rsCopy($bucket, $key, 'sketch', 'copy.png');
//echo "====> Qiniu_RS_Copy result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    echo "Success!";
//}

// 移动图片已完成
//$err = $sdk->rsMove($bucket, 'move.png', $bucket, 'background3.png');
//echo "====> Qiniu_RS_Move result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    echo "Success!";
//}

// 删除图片已完成
//$err = $sdk->rsDelete($bucket, 'copy.png');
//echo "====> Qiniu_RS_Delete result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    echo "Success!";
//}


// 上传字符串已完成
//list($ret, $err) = $sdk->putFile($bucket, 'usb.txt', __FILE__);
//echo "====> Qiniu_Put result: \n";
//if ($err !== null) {
//    var_dump($err);
//} else {
//    var_dump($ret);
//}

// 直接根据参数条件获取 upToken，参数中必须包括 scope。
//echo $sdk->getUpToken($params);
?>
<html>
    <meta charset="utf-8">
    <head> 
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script> 
        <script src="http://sketch.qiniudn.com/jquery.form.js"></script> 
        <script> 
            // wait for the DOM to be loaded 
            $(document).ready(function() { 
                // bind 'myForm' and provide a simple callback function 
                $('#myForm').ajaxForm(
                        {
                            dataType:'json',
                            beforeSubmit: function(){
                                        $('#loading').css('visibility', 'visible');
                                        $('#myForm').hide();
                                    },
                            success:function(data){
                                        $('#image').attr('src', 'http://sketch.qiniudn.com/'+ data.key);
                                        $('#description').html('你上传的图片为'+data.key+','+data.description);
                                        $('#loading').hide();
                                        $('#success').css('visibility', 'visible');
                                    },
                            error:function(){
                                        $('#loading').hide();
                                        $('#error').html('图片上传失败，请刷新后重试。')
                                    }
                        });
                }); 
        </script> 
    </head> 
    <h1>Simple Qiniu PHP SDK</h1>
    <div>
        <span>这个简化版的七牛云储存 SDK 修改自官方的 PHP SDK，只提供了以下几个接口： </span>
        <ul>
            <li>查看文件：$sdk->rsStat($bucket, $key)</li>
            <li>复制文件：$sdk->rsCopy($bucketSrc, $keySrc, $bucketDest, $keyDest)</li>
            <li>移动文件：$sdk->rsMove($bucketSrc, $keySrc, $bucketDest, $keyDest)</li>
            <li>删除文件：$sdk->rsDelete($bucket, $key)</li>
            <li>直接获取upToken：$sdk->getUpToken($policy)</li>
            <li>上传字符串：$sdk->putString($bucket, $key, $content)</li>
            <li>上传本地文件：$sdk->putFile($bucket, $key, $body, $params)</li>
            <li>获取公开文件下载地址：$sdk->getPublicUrl('public-bucket.qiniudn.com', '404.jpg')</li>
            <li>获取私有文件下载地址：$sdk->getPrivateUrl('private-bucket.qiniudn.com', '404.jpg')</li>
        </ul>
    </div>
    <span>源码下载地址：<a href="https://github.com/zither/simple-qiniu-sdk">https://github.com/zither/simple-qiniu-sdk</a></span>
    <br />
    <br />
    <span>在线测试：</span>
    <br />
    <br />
    <form id="myForm" method="post" action="test.php" enctype="multipart/form-data">
        <input name="file" type="file" />
        <button id="upload" type="submit">上传到七牛</button>
    </form>
    <div id="loading" style="visibility:hidden">
        <img src="http://pencily.qiniudn.com/3.gif"><br />图片上传中....
    </div>
    <div id="success" style="visibility:hidden">
        <div id="description"></div>
        <img id="image" />
    </div>
    <div id="error"></div>
</html>
