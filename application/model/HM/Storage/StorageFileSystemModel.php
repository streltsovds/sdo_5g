<?php

class HM_Storage_StorageFileSystemModel extends HM_Model_Abstract
{
    const CONTEXT_SUBJECT = 'subject';
    const CONTEXT_PROJECT = 'probject';
    const CONTEXT_SUBJECT_MATERIALS = 'subject-materials';

    protected $_primaryName = 'id';

    public function getName()
    {
        return iconv(Zend_Registry::get('config')->charset, "UTF-8", $this->name);
    }

    public function getInfo()
    {
        $sc = Zend_Registry::get('serviceContainer');
        $write = ($sc->getService('StorageFileSystem')->isCurrentUserActivityModerator() || 
                    $this->user_id == $sc->getService('User')->getCurrentUserId());
        return array(
            'hash'  => $this->id,
            'name'  => $this->getName(),
            'read'  => true,
            'write' => $write,
            'dirs'  => array()
        );
    }
}