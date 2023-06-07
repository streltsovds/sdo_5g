<?php

class HM_Form_Element_Vue_File extends Zend_Form_Element_File
{
    public $helper = "vueFile";
    protected $_path;

    public function __construct($spec, $options = null)
    {
        $this->setTransferAdapter(new HM_File_Transfer_Adapter_Flash(array('name' => $spec)));
        parent::__construct($spec, $options);
    }

    public function setUploadedFileInfo(array $fileInfo)
    {
        $this->setAttrib('uploaded_file', $fileInfo);
    }

    public function setPreviewUrl($value)
    {
        $this->setAttrib('preview_url', $value);
    }

    public function setPreviewImg($imgUrl)
    {
        if(!$imgUrl) return false;

        $imgUrl = '/'.ltrim($imgUrl, '/');
        $uploadedItem = new HM_DataType_Form_Element_Vue_UploadedItem();
        $file = new HM_DataType_Form_Element_Vue_FileInfo();
        $uploadedItem->file = $file;
        $file->url = $imgUrl;
        $file->type = HM_Files_FilesModel::FILETYPE_IMAGE;

        $this->setUploadedFileInfo([$uploadedItem]);
    }

    public function isDeleted()
    {
        $result = false;
        $name = $this->getName() . '_delete';
        if (isset($_POST[$name]) && $_POST[$name] == '1') $result = true;
        return $result;
    }

    public function render(Zend_View_Interface $view = null)
    {

        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }

        return $content;
    }

    public function setValue($value)
    {
        $this->_path = $value;
        return parent::setValue($value);
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getDestination()
    {
        $adapter = $this->getTransferAdapter();
        $name    = $this->getName();
        $destination = $adapter->getDestination($name);
        if (is_array($destination)) {
            return array_pop($destination);
        }

        return parent::getDestination();
    }

    public function getValue()
    {
        $fileName = $this->getTransferAdapter()->getFileName();
        if (is_array($fileName)) {
            $fileName = array_pop($fileName);
        }

        /**
         * basename вырезает русскоязычные буквы из имени файла! см. #36703/2
         */
        return $fileName ? HM_Unicode::basename($fileName) : parent::getValue();
    }
}
