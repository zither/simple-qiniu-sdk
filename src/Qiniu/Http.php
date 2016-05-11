<?php
namespace Qiniu;

use Qiniu\Http\Request;
use Qiniu\Http\Response;
use InvalidArgumentException;

class Http
{

    public function callMultiRequest($token, $file, array $params = [])
    {
        list($contentType, $body) = $this->getMultiData($token, $file, $params);
        $headers = ['Content-Type' => $contentType];
        $request = new Request(Config::UP_HOST, $headers, $body);

        return $this->sendRequest($request);
    }

    protected function getMultiData($token, $file, array $params)
    {
        if (isset($params['key']) && empty($params['key'])) {
            unset($params['key']);
        }

        $fields = array_merge(['token' => $token], $params);

        if (!file_exists($file)) {
            throw new InvalidArgumentException(
                sprintf('%s does not exists.', $file)
            );
        }

        $fileInfo = pathinfo($file);
        $fname = isset($fields['key']) ? $fields['key'] : $fileInfo['basename'];
        $files = [
            [
                'file', 
                $fname, 
                file_get_contents($file)
            ],
        ];

        return $this->buildMultipartForm($fields, $files);
    }

    public function sendRequest(Request $request)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->getCurlOptions($request));
        $result = curl_exec($ch);

        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errorCode) {
            $body = sprintf(
                '{"code":"%s", "error":"%s"}', 
                $errorCode, 
                $errorMessage
            );
        } else {
            $body = $result;
        }

        return new Response(
            $statusCode, 
            ['Content-Type' => 'application/json'], 
            $body
        );
    }

    protected function getCurlOptions(Request $request) 
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_URL => $request->url
        );
        if (!empty($request->headers)) {
            foreach($request->headers as $key => $value) {
                $options[CURLOPT_HTTPHEADER][] = sprintf('%s: %s', $key, $value);
            }
        }
        if (!empty($request->body)) {
            $options[CURLOPT_POSTFIELDS] = $request->body;
        }

        return $options;
    }

    protected function buildMultipartForm($fields, $files)
    {
        $data = [];
        $mimeBoundary = md5(microtime());

        foreach ($fields as $name => $value) {
            array_push($data, '--' . $mimeBoundary);
            array_push($data, sprintf(
                'Content-Disposition: form-data; name="%s"', 
                $name
            ));
            array_push($data, '');
            array_push($data, $value);
        }

        foreach ($files as $file) {
            array_push($data, '--' . $mimeBoundary);
            list($name, $fileName, $fileBody) = $file;
            $fileName = $this->escapeQuotes($fileName);
            array_push($data, sprintf(
                'Content-Disposition: form-data; name="%s"; filename="%s"', 
                $name, 
                $fileName)
            );
            array_push($data, 'Content-Type: application/octet-stream');
            array_push($data, '');
            array_push($data, $fileBody);
        }

        array_push($data, '--' . $mimeBoundary . '--');
        array_push($data, '');

        $body = implode("\r\n", $data);
        $contentType = 'multipart/form-data; boundary=' . $mimeBoundary;

        return [$contentType, $body];
    }

    protected function escapeQuotes($string)
    {
        $find = ["\\", "\""];
        $replace = ["\\\\", "\\\""];

        return str_replace($find, $replace, $string);
    }
}
