<?php
class HM_Form_Development extends HM_Form {

    public function init() 
    {
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('user_development_form');
        
        if ($sessionUserId = $this->getParam('session_user_id', 0)) {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->find($sessionUserId)->current();
        } elseif ($sessionId = $this->getParam('session_id', 0)) {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAll(array(
                'session_id = ?' => $sessionId,
                'user_id = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
            ))->current();
        }
        
        $this->addElement('hidden', 'session_user_id', array(
            'Value' => $sessionUser->session_user_id,
        ));

        parent::init(); // required!
    }
}