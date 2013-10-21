<?php
/**
 * 七牛云储存 PHP SDK，修改自七牛官方 PHP SDK，版权归七牛所有。
 *
 * @author JonChou <ilorn.mc@gmail.com>
 * @date 2013-08-25
 */
namespace Qiniu;

class Client
{
    // Client 实例
    public static $client = array();
    // Client 名称
    public $name = null;
    // Client 设置类
    public $config = null;
    // Client 签名类
    public $auth = null;
    // Client 请求类
    public $http = null;
    
    /**
     * 实例化 Client
     *
     * @param $config，access_key和secret_key为必须，name为可选。
     *
     * @return void
     */
    public function __construct($config)
    {
        $this->config = new \Qiniu\Config();
        $this->auth = new \Qiniu\Auth($config['access_key'], $config['secret_key']);
        if (is_null(static::getInstance())) {
            if (isset($config['name'])) {
              $this->name = $config['name'];
            }
            is_null($this->name) ? static::$client['default'] = $this : static::$client[$this->name] = $this;
        } 
        $this->http = new \Qiniu\Http();
    }
    
    /**
     * 通过静态方法获取 Client 实例
     *   
     * @TODO 测试多帐号支持
     *
     * @param $name
     *
     * @return object
     */
    public static function getInstance($name = 'default')
    {
        if (empty(static::$client)) {
            return null;
        }
        return isset(static::$client[$name]) ? static::$client[$name] : static::$client['default'];
    }

    /**
     * PSR-0 autoloader
     */
    public static function autoload($className)
    {
        $thisClass = str_replace(__NAMESPACE__.'\\', '', __CLASS__);

        $baseDir = dirname(__DIR__);

        if (substr($baseDir, -strlen($thisClass)) === $thisClass) {
            $baseDir = substr($baseDir, 0, -strlen($thisClass));
        }

        $className = ltrim($className, '\\');
        $fileName  = $baseDir;
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  .= DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (file_exists($fileName)) {
            require $fileName;
        }
    }

    /**
     * 注册 PSR-0 autoloader，如果你在使用 composer，请不要注册该函数。
     */
    public static function registerAutoloader()
    {
        spl_autoload_register(__NAMESPACE__ . "\\Client::autoload");
    }    

    /**
     * 查看文件状态
     *
     * @param $bucket
     * @param $key
     *
     * @return array 
     */
    public function rsStat($bucket, $key)
    {
        $uri = $this->config->rsURIStat($bucket, $key);
        return $this->http->call($this->getSignedRequest($uri));
    }
    
    /**
     * 复制文件
     *
     * @param $bucketSrc
     * @param $keySrc
     * @param $bucketDest
     * @param $keyDest
     *
     * @return array or null
     */
    public function rsCopy($bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
    {
        $uri = $this->config->rsURICopy($bucketSrc, $keySrc, $bucketDest, $keyDest);
        return $this->http->callNoRet($this->getSignedRequest($uri));
    }
    
    /**
     * 移动文件
     *
     * @param $bucketSrc
     * @param $keySrc
     * @param $bucketDest
     * @param $keyDest
     *
     * @return array or null
     */
    public function rsMove($bucketSrc, $keySrc, $bucketDest, $keyDest) // => $error
    {
        $uri = $this->config->rsURIMove($bucketSrc, $keySrc, $bucketDest, $keyDest);
        return $this->http->callNoRet($this->getSignedRequest($uri));
    }
    
    /**
     * 删除文件
     *
     * @param $bucket
     * @param $key
     *
     * @return array or null
     */
    public function rsDelete($bucket, $key) // => $error
    {
        $uri = $this->config->rsURIDelete($bucket, $key);
        return $this->http->callNoRet($this->getSignedRequest($uri));
    }
    
    /**
     * 通过 CURL 上传字符串文件
     *
     * @param $bucketName
     * @param $key
     * @param $body
     *
     * @return array
     */
    public function putString($bucketName, $key, $body)
    {
        $bucket = $this->getSignedBucket($bucketName);
        $request = $this->getStringRequest($bucket, $key, $body);
        return $this->http->call($request);
    }
    
    /**
     * 通过 CURL 上传本地文件
     *
     * @param $bucketName
     * @param $key
     * @param $file
     * @param $params，生成 upToken 的参数条件
     *
     * @return array
     */
    public function putFile($bucketName, $key, $body, $params = array())
    {
        $bucket = $this->getSignedBucket($bucketName, $params);
        $putExtra = new \Qiniu\PutExtra(array('crc32' => 1)); 
        $request = $this->getMultiRequest($bucket, $key, $body, $putExtra);
        return $this->http->call($request);
    }

    /**
     * 获取私有文件下载地址
     *
     * @param $domain
     * @param $key
     * @param $expires
     *
     * @return string
     */
    public function getPrivateUrl($domain, $key, $expires = 3600)
    {
        $baseUrl = $this->getDeadlineUrl($domain, $key, $expires);
        $token = $this->auth->sign($baseUrl);
        return $baseUrl . '&token=' . $token;
    }
    
    /**
     * 获取共有文件下载地址
     *
     * @param $domain
     * @param $key
     *
     * @return string 
     */
    public function getPublicUrl($domain, $key)
    {
        $keyEsc = rawurlencode($key);
        return "http://$domain/$keyEsc";
    }

    /**
     * 获取已签名的 Request 类
     *
     * @param $uri
     *
     * @return object
     */
    public function getSignedRequest($uri)
    {
        $request = new \Qiniu\Http\Request(array('path' => $uri));
        $token = $this->auth->signRequest($request);
        $request->header['Authorization'] = "QBox $token";
        return $request;
    }
    
    /**
     * 获取已签名的 Bucket 类
     *
     * @param $bucketName
     * @param $params，生成 upToken 的参数条件
     *
     * @return object
     */
    public function getSignedBucket($bucketName, $params = array())
    {
        $scope = array('scope' => $bucketName);
        $policy = empty($params) ? $scope : (isset($params['scope']) ? $params : array_merge($scope, $params));
        $bucket = new \Qiniu\Bucket($policy);
        $bucket->token = $this->auth->signWithData($bucket->policy());
        return $bucket;
    }
    
    /**
     * 根据给定参数获取 upToken
     *
     * @param $params，scope为必要参数
     *
     * @return string
     */
    public function getUpToken($params)
    {
        $bucket = new \Qiniu\Bucket($params);
        return $this->auth->signWithData($bucket->policy());
    }
    
    /**
     * 获取上传字符串文件的 Request 类
     *
     * @param $bucket
     * @param $key
     * @param $params
     * @param $putExtra
     *
     * @return object
     */
    public function getStringRequest($bucket, $key, $params, $putExtra = null)
    {
        if ($putExtra === null) {
            $putExtra = new \Qiniu\PutExtra();
        }
        $data = $this->http->getStringData($bucket, $key, $params, $putExtra);
        list($contentType, $body) = $this->http->buildMultipartForm($data['fields'], $data['files']);
        $url = array('path' => \Qiniu\Config::QINIU_UP_HOST);
        if ($contentType === 'application/x-www-form-urlencoded') {
            if (is_array($body)) {
                $body = http_build_query($body);
            }
        }
        $request = new \Qiniu\Http\Request($url, $body);
        if ($contentType !== 'multipart/form-data') {
            $request->header['Content-Type'] = $contentType;
        }
        return $request;
    }
    
    /**
     * 获取上传本地文件的 Request 类
     *
     * @param $bucket
     * @param $key
     * @param $params
     * @param $putExtra
     *
     * @return object
     */
    public function getMultiRequest($bucket, $key, $params, $putExtra = null)
    {
        if ($putExtra === null) {
            $putExtra = new \Qiniu\PutExtra();
        }
        $body = is_array($params) && isset($params['file']) ? $params : array('file' => '@' . $params);
        $data = $this->http->getMultiData($bucket, $key, $body, $putExtra);
        $url = array('path' => \Qiniu\Config::QINIU_UP_HOST);
        $request = new \Qiniu\Http\Request($url, $data);
        $request->Header['Content-Type'] = 'multipart/form-data';
        return $request;
    }
        
    /**
     * 获取downloadToken
     *
     * @param $deadlineUrl
     *
     * @return string
     */
    public function getDownloadToken($deadlineUrl)
    {
        return $this->auth->sign($deadlineUrl);
    }
    
    /**
     * 为下载链接添加过期时间
     *
     * @param $domain
     * @param $key
     * @param $expires
     *
     * @return string
     */
    public function getDeadlineUrl($domain, $key, $expires = 3600)
    {
        $deadline = $expires + time();
        $baseUrl = $this->getPublicUrl($domain, $key);
        $pos = strpos($baseUrl, '?');
        if ($pos !== false) {
            $baseUrl .= '&e=';
        } else {
            $baseUrl .= '?e=';
        }
        return $baseUrl .= $deadline;        
    }
}
