<?php
class HM_File_FileModel extends HM_Model_Abstract
{
    private $_id;
    private $_path;
    private $_url;
    private $_size;
    private $_type;
    private $_displayName;
    private $_created_by;

    public function __construct($options)
    {
        $this->_id          = isset($options['id'])          ? $options['id']          : null;
        $this->_path        = isset($options['path'])        ? $options['path']        : null;
        $this->_type        = isset($options['type'])        ? $options['type']        : null;
        $this->_url         = isset($options['url'] )        ? $options['url']         : null;
        $this->_size         = isset($options['size'] )        ? $options['size']         : null;
        $this->_displayName = isset($options['displayName']) ? $options['displayName'] : null;
        $this->_created_by  = isset($options['created_by'])  ? $options['created_by']  : null;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getDisplayName()
    {
        return $this->_displayName;
    }

    public function getSize()
    {
        return $this->_size;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getCreator()
    {
        if (!$this->_created_by) {
            return '';
        }

        $userService = Zend_Registry::get('serviceContainer')->getService('User');
        $creator = $userService->getOne($userService->find($this->_created_by));
        if (!$creator) {
            return '';
        }

        return array (
            'user_id' => $creator->MID,
            'is_me'   => ($creator->MID == $userService->getCurrentUserId()),
            'fio'     => $creator->LastName . ' ' . $creator->FirstName . ' ' . $creator->Patronymic)
            ;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getFileName()
    {
    }

    public function getUrl()
    {
        return $this->_url;
    }
}