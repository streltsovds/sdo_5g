<?php

class ElFinderVolumeHmLocalFileSystem extends elFinderVolumeLocalFileSystem
{
    public function setAdded(array $data)
    {
        $this->added = $data;
    }

    public function getAdded()
    {
        return $this->added;
    }

    public function getRootName()
    {
        return $this->rootName;
    }

    public function decode($hash)
    {
        return parent::decode($hash);
    }

    public function encode($path)
    {
        return parent::encode($path);
    }

    protected function _abspath($path)
    {
        $rootPath = $path;
        $result   = $this->root;
        if ($this->getRootName() !== 'files') {
            $parts    = explode($this->rootName, $path);
            $rootPath = $parts[0] . $this->rootName;
            $result   = $this->root . $parts[1];
        }

        if (false !== strpos($this->root, $rootPath)) return $result;
        else return parent::_abspath($path);
    }
}