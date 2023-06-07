<?php

class HM_Resource_Reader
{
    
    protected $_adapter;
    
    public function __construct($filePath, $fileName)
    {
        
        $pathParts = pathinfo($fileName);
        $fileExtension = isset($pathParts['extension']) ? ucfirst($pathParts['extension']) : '';
        if(file_exists(dirname(__FILE__) . '/Adapter/' . $fileExtension . '.php') && file_exists($filePath))
        {
            $class = 'HM_Resource_Adapter_' . $fileExtension;
            $adapter = new $class(array('file' => $filePath));
            $this->_adapter = $adapter;
        }else{
            $this->_adapter = Null;
        }
    }
    
    
    public function readFile()
    {
        if($this->_adapter != Null){
            $this->_adapter->readFile();
        }else{
            return false;
        }
    }
    
    
}