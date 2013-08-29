<?php
namespace Qiniu;

class Bucket
{
    // 生成upToken参数列表
    public $scope = null;
    public $callbackUrl = null;
    public $callbackBody = null;
    public $returnUrl = null;
    public $returnBody = null;
    public $asyncOps = null;
    public $endUser = null;
    public $expires = null;
    // 保存签名成功的upToken
    public $token = null;

    public function __construct($params)
    {
        $this->scope = $params['scope'];
        if (isset($params['callbackUrl'])) {
          $this->callbackUrl = $params['callbackUrl'];
        }
        if (isset($params['callbackBody'])) {
          $this->callbackBody = $params['callbackBody'];
        }
        if (isset($params['returnUrl'])) {
          $this->returnUrl = $params['returnUrl'];
        }
        if (isset($params['returnBody'])) {
          $this->returnBody = $params['returnBody'];
        }
        if (isset($params['asyncOps'])) {
          $this->asyncOps = $params['asyncOps']; 
        }
        if (isset($params['endUser'])) {
          $this->endUser = $params['endUser']; 
        }
        if (isset($params['expires'])) {
          $this->expires = $params['expires'];
        }
    }
    
    /**
     * 根据初始化的参数获取预签名许可
     *
     * @return Json 
     */
    public function policy()
    {
        $deadline = $this->expires;
        if ($deadline == 0) {
            $deadline = 3600;
        }
        $deadline += time();

        $policy = array('scope' => $this->scope, 'deadline' => $deadline);
        if (!empty($this->callbackUrl)) {
            $policy['callbackUrl'] = $this->callbackUrl;
        }
        if (!empty($this->callbackBody)) {
            $policy['callbackBody'] = $this->callbackBody;
        }
        if (!empty($this->returnUrl)) {
            $policy['returnUrl'] = $this->returnUrl;
        }
        if (!empty($this->returnBody)) {
            $policy['returnBody'] = $this->returnBody;
        }
        if (!empty($this->asyncOps)) {
            $policy['asyncOps'] = $this->asyncOps;
        }
        if (!empty($this->endUser)) {
            $policy['endUser'] = $this->endUser;
        }
        return json_encode($policy);
    }
}
