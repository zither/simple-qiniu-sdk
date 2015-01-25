<?php
namespace Qiniu;

use Qiniu\Config;
use Qiniu\Http\Request;
use Qiniu\Http\Response;
use Qiniu\Http\Error;

class Http
{
    public function callMultiRequest($token, $params, $key)
    {
        $params = is_string($params) ? array('file' => $params) : $params;
        $data = $this->getMultiData($token, $params, $key);

        list($contentType, $body) = $data;
        $header = array('Content-Type' => $contentType);
        $request = new Request(Config::QINIU_UP_HOST, $header, $body);

        return $this->call($request);
    }

    protected function getMultiData($token, $body, $key)
    {
        $fields = array('token' => $token);
        if (!is_null($key)) {
            $fields['key'] = $key;
        }

        if (!isset($body['file'])) {
            return array('application/x-www-form-urlencoded', $fields);
        }

        $fileInfo = pathinfo($body['file']);
        $fname = is_null($key) ? $fileInfo['basename'] : $key;
        $files = array(array('file', $fname, file_get_contents($body['file'])));
        return $this->buildMultipartForm($fields, $files);
    }

    protected function call($request)
    {
        list($response, $error) = $this->makeRequest($request);
        if (!is_null($error)) {
            return array(null, $error);
        }
        return $this->responseData($response);
    }

    protected function callNoResponse($request) // => $error
    {
        list($response, $error) = $this->makeRequest($request);
        if (!is_null($error)) {
            return array(null, $error);
        }
        return $response->statusCode === 200 ? null : $this->responseError($response);
    }

    protected function getHeader($header, $key)
    {
        if (!isset($header[$key])) {
            return null;
        }
        return is_array($header[$key]) ? $header[$key][0] : $header[$key];   
    }

    protected function responseError($response) // => $error
    {
        $header = $response->header;
        $details = $this->getHeader($header, 'X-Log');
        $reqId = $this->getHeader($header, 'X-Reqid');
        $error = new Error($response->statusCode, null);

        $contentType = $this->getHeader($header, 'Content-Type');
        if (
            $error->code > 299 
            && $response->contentLength > 0 
            && $contentType === 'application/json'
        ) {
            $body = json_decode($response->body, true);
            $error->error = $body['error'];
        }
        return $error;
    }

    protected function responseData($response)
    {
        $statusCode = $response->statusCode;
        $data = is_null($response->body) ? null : json_decode($response->body, true);
        if ($statusCode === 200) {
            return array($data, null);
        }
        if ($statusCode >= 200 && $statusCode <= 299 && is_null($data)) {
            return array(null, new Error(0, null));
        }
        return array($data, $this->responseError($response));
    }

    protected function incBody(Request $request)
    {
        if (!isset($request->body)) {
            return false;
        }
        $contentType = $this->getHeader($request->header, 'Content-Type');
        return $contentType === 'application/x-www-form-urlencoded' ? true : false;
    }

    protected function getStringData($body, $token, $key)
    {
        $fields = array('token' => $token, 'key' => $key);
        $files = array(array('file', $key, $body));
        return array('fields' => $fields, 'files' => $files);
    }

    protected function makeRequest($request)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->getCurlOptions($request));
        $result = curl_exec($ch);
        $errorNumber = curl_errno($ch);
        if ($errorNumber) {
            $error = new Error($errorNumber, curl_error($ch));
            curl_close($ch);
            return array(null, $error);
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        $header = array('Content-Type' => $contentType);
        $response = new Response($httpCode, $header, $result);
        return array($response, null);
    }

    protected function getCurlOptions($request) 
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_URL => $request->url
        );
        if (!empty($request->header)) {
            foreach($request->header as $key => $value) {
                $options[CURLOPT_HTTPHEADER][] = sprintf("%s: %s", $key, $value);
            }
        }
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }
        return $options;
    }

    protected function buildMultipartForm($fields, $files)
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

    protected function escapeQuotes($string)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");
        return str_replace($find, $replace, $string);
    }
}
