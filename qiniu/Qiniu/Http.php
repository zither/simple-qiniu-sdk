<?php
namespace Qiniu;

class Http
{
    public $client = null;
    public $error = null;
    public $request = null;
    public $response = null;

    public function headerGet($header, $key)
    {
        $val = @$header[$key];
        if (isset($val)) {
            if (is_array($val)) {
                return $val[0];
            }
            return $val;
        } else {
            return '';
        }        
    }

    public function responseError($response) // => $error
    {
        $header = $response->header;
        $details = $this->headerGet($header, 'X-Log');
        $reqId = $this->headerGet($header, 'X-Reqid');
        $error = new \Qiniu\Http\Error($response->statusCode, null);

        if ($error->code > 299) {
            if ($response->contentLength !== 0) {
                if ($this->headerGet($header, 'Content-Type') === 'application/json') {
                    $return = json_decode($response->body, true);
                    $error->error = $return['error'];
                }
            }
        }
        return $error;
    }

    public function ret($response) // => ($data, $error)
    {
        $code = $response->statusCode;
        $data = null;
        if ($code >= 200 && $code <= 299) {
            if ($response->contentLength !== 0) {
                $data = json_decode($response->body, true);
                if ($data === null) {
                    $error = new \Qiniu\Http\Error(0, json_last_error_msg());
                    return array(null, $error);
                }
            }
            if ($code === 200) {
                return array($data, null);
            }
        }
        return array($data, $this->responseError($response));
    }

    public function call($request) // => ($data, $error)
    {
        list($response, $error) = $this->makeRequest($request);
        if ($error !== null) {
            return array(null, $error);
        }
        return $this->ret($response);
    }

    public function callNoRet($request) // => $error
    {
        list($response, $error) = $this->makeRequest($request);
        if ($error !== null) {
            return array(null, $error);
        }
        if ($response->statusCode === 200) {
            return null;
        }
        return $this->responseError($response);
    }

    public function incBody(\Qiniu\Http\Request $request) // => $incbody
    {
        $body = $request->body;
        if (!isset($body)) {
            return false;
        }
        $ct = $this->headerGet($request->header, 'Content-Type');
        if ($ct === 'application/x-www-form-urlencoded') {
            return true;
        }
        return false;
    }

    public function getStringData($bucket, $key, $body, $putExtra)
    {
        $fields = array('token' => $bucket->token);
        if ($key === null) {
            $fileName = '?';
        } else {
            $fileName = $key;
            $fields['key'] = $key;
        }
        if ($putExtra->checkCrc) {
            $fields['crc32'] = $putExtra->crc32;
        }
        $files = array(array('file', $fileName, $body));
        return array('fields' => $fields, 'files' => $files);
    }

    public function getMultiData($bucket, $key, $body, $putExtra)
    {
        $fields = array_merge($body, array('token' => $bucket->token));
        //$fields = array('token' => $bucket->token, 'file' => '@' . $localFile);
        if ($key === null) {
            $fname = '?';
        } else {
            $fname = $key;
            $fields['key'] = $key;
        }
        if ($putExtra->checkCrc) {
            if ($putExtra->checkCrc === 1) {
                $hash = hash_file('crc32b', $localFile);
                $array = unpack('N', pack('H*', $hash));
                $putExtra->crc32 = $array[1];
            }
            $fields['crc32'] = sprintf('%u', $putExtra->crc32);
        }
        return $fields;
    }

    public function makeRequest($req) // => ($resp, $error)
    {
        $ch = curl_init();
        $url = $req->url;
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_URL => $url['path']
        );
        $httpHeader = $req->header;
        if (!empty($httpHeader))
        {
            $header = array();
            foreach($httpHeader as $key => $parsedUrlValue) {
                $header[] = "$key: $parsedUrlValue";
            }
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        $body = $req->body;
        if (!empty($body)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            $err = new \Qiniu\Http\Error(0, curl_error($ch));
            curl_close($ch);
            return array(null, $err);
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        $response = new \Qiniu\Http\Response($code, $result);
        $response->header['Content-Type'] = $contentType;
        return array($response, null);
    }

    public function buildMultipartForm($fields, $files) // => ($contentType, $body)
    {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $name => $val) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"");
            array_push($data, '');
            array_push($data, $val);
        }

        foreach ($files as $file) {
            array_push($data, '--' . $mimeBoundary);
            list($name, $fileName, $fileBody) = $file;
            $fileName = $this->escapeQuotes($fileName);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$fileName\"");
            array_push($data, 'Content-Type: application/octet-stream');
            array_push($data, '');
            array_push($data, $fileBody);
        }

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;
        return array($contentType, $body);
    }

    public function escapeQuotes($str)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $str);
    }
}
