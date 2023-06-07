<?php
/*
 * 5G
 *
 */
class Subject_MaterialsController extends HM_Controller_Action_Subject
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        parent::init();

        $this->view->replaceSidebar('subject', 'subject-extras', [
            'model' => $this->_subject,
            'order' => 100, // после Subject
        ]);
    }

    public function indexAction()
    {
        $this->view->setSubSubHeader(_('Исходные материалы курса'));

// пока непонятно зачем этот switcher нужен здесь
//        $switcher = $this->getSwitcherSetOrder($this->_subjectId, 'title_ASC');

        try {
            $this->dataGrid = new HM_Subject_DataGrid_MaterialsDataGrid($this->view, [], [
                'subject_id' => $this->_subjectId,
            ]);

            $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        } catch (Zend_Exception $e) {}
    }

    protected function _redirectToIndex($action = 'index')
    {
        $this->_redirector->gotoSimple($action, 'materials', 'subject', array('subject_id' => $this->_subjectId));
    }

    // Удаляем действие из контекстного меню для данного контроллера
    public function getContextNavigationModifiers()
    {
        return array(new HM_Navigation_Modifier_Remove_Action('label', _('Создать раздел')));
    }

}
