<?php
namespace Qiniu;

use Qiniu\Config;
use Qiniu\Http\Request;
use Qiniu\Http\Response;
use InvalidArgumentException;

class Http
{
    /**
     * Call multipart request
     *
     * @param string $token
     * @param string $file
     * @param array $params
     * @return Response
     */
    public function callMultiRequest($token, $file, array $params)
    {
        list($contentType, $body) = $this->getMultiData($token, $file, $params);
        $header = array("Content-Type" => $contentType);
        $request = new Request(Config::QINIU_UP_HOST, $header, $body);

        return $this->makeRequest($request);
    }

    /**
     * Get multipart data
     *
     * @param string $token
     * @param string $file
     * @param array $params
     * @return array
     * @throws InvalidArgumentException for invalid file path
     */
    protected function getMultiData($token, $file, array $params)
    {
        if (isset($params["key"]) && empty($params["key"])) {
            unset($params["key"]);
        }

        $fields = array_merge(array("token" => $token), $params);

        if (!file_exists($file)) {
            throw new InvalidArgumentException(
                sprintf("%s does not exists.", $file)
            );
        }

        $fileInfo = pathinfo($file);
        $fname = isset($fields["key"]) ? $fields["key"] : $fileInfo["basename"];
        $files = array(
            array(
                "file", 
                $fname, 
                file_get_contents($file)
            )
        );

        return $this->buildMultipartForm($fields, $files);
    }

    /**
     * Make request
     *
     * @param Request $request
     * @return Response
     */
    protected function makeRequest(Request $request)
    {
        $ch = curl_init();
        curl_setopt_array($ch, $this->getCurlOptions($request));
        $result = curl_exec($ch);

        $errorCode = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $statusCode =  $errorCode > 0 ? $errorCode : $httpCode;
        $body = $errorCode > 0 ? sprintf('{"error":"%s"}', $errorMessage) : $result;

        return new Response(
            $statusCode, 
            array("Content-Type" => "application/json"), 
            $body
        );
    }

    /**
     * Get curl options
     *
     * @param Request $request
     * @return array
     */
    protected function getCurlOptions(Request $request) 
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

    /**
     * Build multipart form
     *
     * @param array $fields
     * @param array $files
     * @return array
     */
    protected function buildMultipartForm($fields, $files)
    {
        $data = array();
        $mimeBoundary = md5(microtime());

        foreach ($fields as $name => $value) {
            array_push($data, "--" . $mimeBoundary);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"");
            array_push($data, "");
            array_push($data, $value);
        }

        foreach ($files as $file) {
            array_push($data, "--" . $mimeBoundary);
            list($name, $fileName, $fileBody) = $file;
            $fileName = $this->escapeQuotes($fileName);
            array_push($data, "Content-Disposition: form-data; name=\"$name\"; filename=\"$fileName\"");
            array_push($data, "Content-Type: application/octet-stream");
            array_push($data, "");
            array_push($data, $fileBody);
        }

        array_push($data, "--" . $mimeBoundary . "--");
        array_push($data, "");

        $body = implode("\r\n", $data);
        $contentType = "multipart/form-data; boundary=" . $mimeBoundary;

        return array($contentType, $body);
    }

    /**
     * Escape quotes
     *
     * @param string $string
     * @return string
     */
    protected function escapeQuotes($string)
    {
        $find = array("\\", "\"");
        $replace = array("\\\\", "\\\"");

        return str_replace($find, $replace, $string);
    }
}
