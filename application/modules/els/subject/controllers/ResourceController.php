<?php

class Subject_ResourceController extends HM_Controller_Action_Resource
{
    protected $_backUrl;

    protected $_subject;

    public function init()
    {
        parent::init();

        if ($this->_resource->subject_id) {
            $this->_subject = $this->getService('Subject')->findOne($this->_resource->subject_id);
            $this->initContext($this->_subject);
            $this->setActiveContextMenu('mca:subject:extra-materials:edit');
        }

        // пришли из плана занятий или из материалов

        if ($this->_getParam('lesson_id', 0)) {
            $this->_backUrl = $this->view->url([
                'module' => 'lesson',
                'controller' => 'list',
                'action' => 'index',
                'subject_id' => $this->_resource->subject_id,
            ], null, true);
        } else {
            $this->_backUrl = $this->view->url([
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'subject_id' => $this->_resource->subject_id,
            ], null, true);

        }

        $this->view->setBackUrl($this->_backUrl);
    }

}
