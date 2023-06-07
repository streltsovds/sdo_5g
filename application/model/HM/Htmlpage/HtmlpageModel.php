<?php

class HM_Htmlpage_HtmlpageModel extends HM_Model_Abstract implements HM_Model_BannerDisplayed
{
    const ORDER_DEFAULT = 10;
    
    static public function getActionsPath()
    {
        return APPLICATION_PATH . '/../data/temp/actions_extended.xml';
    }


    public static function getIconFolder($pageId = null)
    {

        if (! $pageId ) $pageId = 0;
        $folder = rtrim(Zend_Registry::get('config')->path->upload->info, '/') . '/';

        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;

        $par = floor($pageId / $maxFilesPerFolder);

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }

        $folder = $folder . $par . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return $folder;
    }



    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        if ($this->description) {
            return strip_tags($this->description);
        } else {
            return mb_substr(strip_tags($this->text), 0, 200);
        }
    }

    public function getUserIcon()
    {
        return $this->icon_url;
    }


    public function getUrl()
    {
        return  Zend_Registry::get('view')->url(array('module' => 'htmlpage', 'controller' => 'index', 'action' => 'view', 'htmlpage_id' => $this->page_id));
    }


}