<?php

class HM_DataType_Form_Element_Vue_FileInfo extends HM_DataType_Abstract
{
    public $name;
    public $size;

    /** @see HM_Files_FilesModel */
    public $type;

    public $previewUrl;
    public $url;
    public $mimeType;
    public $deleteUrl;

    public $convertableToPdf = false;
}