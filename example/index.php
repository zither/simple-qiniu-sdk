<!DOCTYPE html>
<html>
    <head> 
        <meta charset="utf-8">
        <title>Simple Qiniu SDK</title>
    </head> 
    <body>
        <h1>Simple Qiniu SDK</h1>
        <p>重写之前的 Simple Qiniu SDK，目前支持资源上传，资源元信息查询，资源删除。</p>
        <p>源码下载地址：<a href="https://github.com/zither/simple-qiniu-sdk">https://github.com/zither/simple-qiniu-sdk</a></p>
        <div>
            <p>文件上传：</p>
            <form action="/upload.php" method="post"  enctype="multipart/form-data">
                <input name="file" type="file" />
                <button id="upload" type="submit">上传到七牛</button>
            </form>
        </div>

        <p><a href="/stat.php">查看测试图片信息</a></p>
        <p><a href="/delete.php">删除测试图片（删除成功则返回为空）</a></p>
    </body>
</html>
