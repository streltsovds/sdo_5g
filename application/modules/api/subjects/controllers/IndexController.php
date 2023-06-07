<?php
class Subjects_IndexController extends HM_Controller_Action_RestOauth
{
    /** @var HM_Subject_SubjectService _defaultService */
    protected $_defaultService = null;

    public function init()
    {
        parent::init();
        $this->_defaultService = $this->getService('Subject');
    }

    public function getAction()
    {
        $ids = $this->_defaultService->getFreeSubjects(0);
        $collection = $this->_defaultService->fetchAll(['subid IN (?)' => $ids]);

        $result = $collection->getList('subid', 'getRestDefinition');

        // key обязателен для getList, поэтому откинем его здесь, чтобы не ломать HM_Collection_Abstract
        $this->view->assign(array_values($result));
    }



}