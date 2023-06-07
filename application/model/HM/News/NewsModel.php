<?php
class HM_News_NewsModel extends HM_Lesson_LessonModel
{
    const BANNER_WIDTH = 1024;
    const MOBILE_PAGE_SIZE = 20;

    public function getType()
    {
        return HM_Activity_ActivityModel::ACTIVITY_NEWS;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        return Zend_Registry::get('config')->url->base.'images/events/redmond_test.png';
    }

    public function isExternalExecuting() {
        return true;
    }

    public function getExecuteUrl() {
        return "http://www.yandex.ru";
    }

    public function getResultsUrl($options = array())
    {

    }

    public function getFilteredMessage()
    {
        return strip_tags($this->message);
    }

    public function getCut()
    {
        $mPos = strpos($this->message, '<!--more-->');
        if($mPos === false) {
            $mPos = strpos($this->message, '<!-- pagebreak -->');
        }
        $body = $this->message;
        if($mPos !== false) $body = substr($this->message, 0, $mPos);
        return stripslashes($body);
    }

    public function getAnnounce()
    {
        if ($this->announce) {
            return $this->announce;
        } else {
            return mb_substr($this->message, 0, 200);
        }
    }

    public function getUrl()
    {
        return  Zend_Registry::get('view')->url(array('module' => 'news', 'controller' => 'index', 'action' => 'view', 'news_id' => $this->id));
    }

    public static function getIconFolder($pageId = null)
    {
        if (! $pageId ) $pageId = 0;
        $folder = rtrim(Zend_Registry::get('config')->path->upload->news, '/') . '/';
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
}