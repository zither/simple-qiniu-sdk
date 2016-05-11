<?php
namespace Qiniu;

class Batch extends Resource
{
    protected $operations = [];

    public function statList(FileList $list)
    {
        foreach ($list->asArray() as $file) {
            $this->operations[] = $file->statUri();
        }
        return $this;
    }

    public function deleteList(FileList $list)
    {
        foreach ($list->asArray() as $file) {
            $this->operations[] = $file->deleteUri();
        }
        return $this;
    }   

    public function stat(File $file)
    {
        $this->operations[] = $file->statUri();
        return $this;
    }

    public function delete(File $file)
    {
        $this->operations[] = $file->deleteUri();
        return $this;
    }   

    public function copy(File $file, $bucket, $filename, $force = false)
    {
        $this->operations[] = $file->copyUri($bucket, $filename, $force);
        return $this;
    }

    public function move(File $file, $bucket, $filename)
    {
        $this->operations[] = $file->moveUri($bucket, $filename);
        return $this;
    }

    public function run()
    {
        if (empty($this->operations)) {
            return true;
        }

        $url = $this->uriWithRsHost($this->config->batchUri($this->operations));
        $body = $this->batchBody($this->operations);
        $request = $this->createSignedRequest($url, [], $body);

        return $response = $this->http->sendRequest($request);
    }

    protected function batchBody($operations)
    {
        return 'op=' . implode($operations, '&op=');
    }

}
