<?php
class HM_Hr_Reserve_Position_PositionModel extends HM_Model_Abstract
{
    const THUMB_WIDTH = 640;
    const THUMB_HEIGHT = 480;

    protected $_primaryName = 'reserve_position_id';

    public function getUserIcon()
    {
        $path = $path = Zend_Registry::get('config')->path->upload->reserve_position . $this->reserve_position_id . '.jpg';
        if(is_file($path)){
            return preg_replace('/^.*public\//', Zend_Registry::get('config')->url->base, $path);
        }
        return null;
    }

    public function getIcon()
    {
        if ($icon = $this->getUserIcon()) {
            return $icon;
        } else {
            return $this->getDefaultIcon();
        }
    }

    public function getIconBanner()
    {
        $path = $this->banner_url;
        return $path;
    }

    public function getDefaultIcon() {
        return Zend_Registry::get('config')->url->base.'images/reserve-icons/reserve.png';
    }

    public function getIconHtml()
    {
        $defaultIconClass = $defaultIconStyle = '';
        if (!$icon = $this->getUserIcon()) {
            $icon = $this->getDefaultIcon();
            $defaultIconClass = 'hm-reserve-icon-default';
            $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');
        } else {
            $defaultIconClass = 'hm-reserve-icon-custom';
            $defaultIconStyle = sprintf('background-color: #%s;', $this->base_color ? $this->base_color : '555');
        }

        return sprintf('<div style="background-image: url(%s); %s; background-repeat: no-repeat; background-size: cover;   background-position: center;" class="hm-reserve-icon %s" title="%s"></div>', $icon, $defaultIconStyle, $defaultIconClass, $this->name);
    }

    public static function getIconFolder($positionId = 0)
    {
        $folder = Zend_Registry::get('config')->path->upload->reserve_position;
//        $maxFilesPerFolder = Zend_Registry::get('config')->path->upload->maxfilescount;
//        $folder = $folder . floor($positionId / $maxFilesPerFolder) . '/';

        if(!is_dir($folder)){
            mkdir($folder, 0774);
            chmod($folder, 0774);
        }
        return $folder;
    }
}
