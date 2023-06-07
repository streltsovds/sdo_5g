<?php

class HM_Storage_StorageModel extends HM_Model_Abstract
{
    const CONTEXT_SUBJECT = 'subject';
    const CONTEXT_PROJECT = 'project';
    const CONTEXT_SUBJECT_MATERIALS = 'subject-materials';
    const CONTEXT_SUBJECT_EXTRA_MATERIALS = 'subject-extra-materials';
    const CONTEXT_SUBJECT_LESSONS = 'subject-lessons';

    protected $_primaryName = 'id';

    public function getName()
    {
        return iconv(Zend_Registry::get('config')->charset, "UTF-8", $this->alias);
    }

    public function getInfo()
    {
        $sc = Zend_Registry::get('serviceContainer');
        $write = ($sc->getService('Storage')->isCurrentUserActivityModerator() ||
                    $this->user_id == $sc->getService('User')->getCurrentUserId());
        return array(
            'hash'  => $this->hash,
            'name'  => $this->getName(),
            'read'  => true,
            'write' => $write,
            'dirs'  => array()
        );
    }
}