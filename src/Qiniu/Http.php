<?php
namespace Qiniu;

class Http
{
    public $error = null;
    public $request = null;
    public $response = null;

    public function call($request) // => ($data, $error)
    {
        list($response, $error) = $this->makeRequest($request);
        if (!is_null($error)) {
            return array(null, $error);
        }
        return $this->responseData($response);
    }

    public function callNoResponse($request) // => $error
    {
        list($response, $error) = $this->makeRequest($request);
        if (!is_null($error)) {
            return array(null, $error);
        }
        return $response->statusCode === 200 ? null : $this->responseError($response);
    }

    public function getHeader($header, $key)
    {
        if (!isset($header[$key])) {
            return null;
        }
        return is_array($header[$key]) ? $header[$key][0] : $header[$key];   
    }

    public function responseError($response) // => $error
    {
        $header = $response->header;
        $details = $this->getHeader($header, 'X-Log');
        $reqId = $this->getHeader($header, 'X-Reqid');
        $error = new \Qiniu\Http\Error($response->statusCode, null);

        $contentType = $this->getHeader($header, 'Content-Type');
        if ($error->code > 299 && $response->ContentLength > 0 && $contentType === 'application/json') {
            $body = json_decode($response->body, true);
            $error->error = $body['error'];
        }
        return $error;
    }

    public function responseData($response) // => ($data, $error)
    {
        $statusCode = $response->statusCode;
        $data = is_null($response->body) ? null : json_decode($response->body, true);
        if ($statusCode === 200) {
            return array($data, null);
        }
        if ($statusCode >= 200 && $statusCode <= 299 && is_null($data)) {
            $error = new \Qiniu\Http\Error(0, null);
            return array(null, $error);
        }
        return array($data, $this->responseError($response));
    }

    public function incBody(\Qiniu\Http\Request $request) // => $incbody
    {
        if (!isset($request->body)) {
            return false;
        }
        $contentType = $this->getHeader($request->header, 'Content-Type');
        return $contentType === 'application/x-www-form-urlencoded' ? true : false;
    }

    public function getStringData($body, $token, $key)
    {
        $fields = array('token' => $token, 'key' => $key);
        $files = array(array('file', $key, $body));
        return array('fields' => $fields, 'files' => $files);
    }

    public function getMultiData($body, $token, $key)
    {
        $fields = array('token' => $token);
        if (!is_null($key)) {
            $fields['key'] = $key;
        }

        if (!isset($body['file'])) {
            return array('multipart/form-data', $fields);
        }

        $fileInfo = pathinfo($body['file']);
        $fname = is_null($key) ? $fileInfo['basename'] : $key;
        $files = array(array('file', $fname, file_get_contents($body['file'])));
        return $this->buildMultipartForm($fields, $files);
    }

    public function makeRequest($request) // => ($resp, $error)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->getCurlOptions($request));
        $result = curl_exec($ch);
        $errorNumber = curl_errno($ch);
        if ($errorNumber) {
            $error = new \Qiniu\Http\Error($errorNumber, curl_error($ch));
            curl_close($ch);
            return array(null, $error);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        $header = array('Content-Type' => $contentType);
        $response = new \Qiniu\Http\Response($httpCode, $header, $result);
        return array($response, null);
    }

    public function getCurlOptions($request) 
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_URL => $request->url
        );
        if (!empty($request->header)) {
            $header = array();
            foreach($request->header as $key => $value) {
                $header[] = "$key: $value";
            }
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        return $options;
    }

    public function buildMultipartForm($fields, $files) // => ($contentType, $body)
    {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $name => $value) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"");
            array_push($data, '');
            array_push($data, $value);
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
