<?php
namespace Qiniu;

class FileList extends Resource
{
    protected $bucket;
    protected $queryParams = [
        'prefix' => '',
        'limit' => 1000,
        'delimiter' => '',
        'marker' => '',
    ];
    protected $marker;
    protected $items = [];
    protected $commonPrefixes = [];

    public function __construct(Config $config, $bucket, array $params = []) 
    {
        parent::__construct($config);
        $this->bucket = $bucket;
        $this->queryParams = array_merge($this->queryParams, $params);
    }


    public function asArray()
    {
        return $this->items;
    }

    public function fetch()
    {
        $params = array_merge(
            $this->queryParams, 
            ['bucket' => $this->bucket]
        );
        $url = $this->uriWithRsfHost($this->config->listUri($params));
        $request = $this->createSignedRequest($url);
        $response = $this->http->sendRequest($request);

        if ($response->statusCode === 200) {
            $data = json_decode($response->getContent());
            $this->marker = $data->marker;
            $this->commonPrefixes = $data->commonPrefixes;
            foreach ($data->items as $item) {
                $file = new File(
                    $this->config, 
                    ['scope' => sprintf('%s:%s', $this->bucket, $item->key)]
                );
                $file->setMetadata((array)$item);
                $this->items[] = $file;
            }
            
            return $this->items;
        }

        return false;
    }
}
