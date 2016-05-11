<!DOCTYPE html>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Simple Qiniu SDK</title>
    </head> 
    <body>
        <h1>Simple Qiniu SDK</h1>
        <p>源码下载地址：<a href="https://github.com/zither/simple-qiniu-sdk">https://github.com/zither/simple-qiniu-sdk</a></p>
        <form action="/upload.php" method="post"  enctype="multipart/form-data">
            <input name="file" type="file" />
            <button id="upload" type="submit">上传到七牛</button>
        </form>

        <p><a href="/stat.php">查看测试图片信息</a></p>
        <p><a href="/move.php">移动测试图片</a></p>
        <p><a href="/copy.php">复制测试图片</a></p>
        <p><a href="/delete.php">删除测试图片（删除成功则返回为空）</a></p>
        <p><a href="/list.php">查看文件列表</a></p>
        <p><a href="/batch.php">批量操作</a></p>
    </body>
</html>
