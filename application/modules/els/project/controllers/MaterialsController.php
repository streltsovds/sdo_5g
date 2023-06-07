<?php
class Project_MaterialsController extends HM_Controller_Action_Project
{
    protected $_projectId = 0;
    protected $_project = 0;

    public function init()
    {
        $this->_projectId = (int) $this->_getParam('project_id', 0);
        $this->_project = $this->getService('Project')->find($this->_projectId)->current();
        return parent::init();
    }

    public function indexAction()
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $this->view->setHeaderOptions(array(
                'pageTitle' => _('Все материалы'),
                'panelTitle' => $this->view->getPanelShortname(array('project' => $this->_project, 'projectName' => 'project')),
            ));
        }

        $sections = $this->getService('Section')->getSectionsMaterials($this->_projectId, HM_Section_SectionModel::ITEM_TYPE_PROJECT);
        $this->view->sections = $sections;
    }

    public function editAction()
    {
        $form = new HM_Form_Materials();
        $request = $this->getRequest();
        $projectId = (int) $this->_getParam('project_id', 0);
        if ($lesson = $this->getService('Meeting')->find((int) $this->_getParam('SHEID', 0))->current()) {
            $form->setDefaults(array(
                'project_id' => $projectId,
                'SHEID' => $lesson->SHEID,
                'title' => $lesson->title,
                'descript' => $lesson->descript,
            ));
        } else {
            $this->_flashMessenger->addMessage(_('Материал не найден'));
            return false;
        }

        if ($request->isPost() && $form->isValid($request->getPost())) {

            $this->getService('Meeting')->updateWhere(array(
                'title' => $form->getValue('title'),
                'descript' => $form->getValue('descript'),
            ), array(
                'SHEID = ?' => $form->getValue('SHEID'),
            ));

            $this->_flashMessenger->addMessage(_('Материалы курса успешно обновлены'));
            $this->_redirector->gotoSimple('index', 'materials', 'project', array('project_id' => $projectId));
        }
        $this->view->form = $form;
    }

    public function editSectionAction()
    {
        $form = new HM_Form_Sections();
        $request = $this->getRequest();
        $projectId = (int) $this->_getParam('project_id', 0);
        $sectionId = (int) $this->_getParam('section_id', 0);

        $sections = $this->getService('Section')->find($sectionId);
        if (count($sections)) {
            $section = $this->getService('Section')->getOne($sections);
            $form->setDefaults(array(
                'project_id' => $projectId,
                'section_id' => $section->section_id,
                'title' => $section->title,
            ));
        }

        if ($request->isPost() && $form->isValid($request->getPost())) {

            if ($sectionId) {
                $this->getService('Section')->updateWhere(array(
                    'name' => $form->getValue('name'),
                ), array(
                    'section_id = ?' => $sectionId,
                ));
            } else {
                $order = $this->getService('Section')->getCurrentSectionOrder($projectId, HM_Section_SectionModel::ITEM_TYPE_PROJECT);
                $this->getService('Section')->insert(array(
                    'name' => $form->getValue('name'),
                    'project_id' => $projectId,
                    'order' => ++$order,
                ));
            }
            $this->_flashMessenger->addMessage(_('Группа материалов успешно сохранена'));
            if ($this->_getParam('return', '') == 'lesson') {
                $this->_redirector->gotoSimple('index', 'list', 'lesson', array('project_id' => $projectId, 'switcher' => 'my'));
            } else {
            $this->_redirector->gotoSimple('index', 'materials', 'project', array('project_id' => $projectId));
        }
        }
        $this->view->form = $form;
    }

    public function deleteSectionAction()
    {
        $projectId = (int) $this->_getParam('project_id', 0);
        $sectionId = (int) $this->_getParam('section_id', 0);
        if ($sectionId) {
            $this->getService('Section')->deleteBy(array(
                'section_id = ?' => $sectionId,
            ));
        }
        $this->_flashMessenger->addMessage(_('Группа материалов успешно удалена'));
        if ($this->_getParam('return', '') == 'lesson') {
            $this->_redirector->gotoSimple('index', 'list', 'lesson', array('project_id' => $projectId, 'switcher' => 'my'));
        } else {
        $this->_redirector->gotoSimple('index', 'materials', 'project', array('project_id' => $projectId));
    }
    }

    public function orderSectionAction()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getHelper('viewRenderer')->setNoRender();

        $sectionId = $this->_getParam('section_id', array());
        $materials = $this->_getParam('material', array());
        echo $this->getService('Section')->setMaterialsOrder($sectionId, $materials, HM_Section_SectionModel::ITEM_TYPE_PROJECT) ? 1 : 0;
    }
}
