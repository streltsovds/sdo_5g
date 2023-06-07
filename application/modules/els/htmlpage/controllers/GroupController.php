<?php
class Htmlpage_GroupController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid {
        newAction as protected gridTraitNewAction;
    }

    public function init() {
        $this->_setForm(new HM_Form_GroupPage());
        parent::init();

        $session = new Zend_Session_Namespace('default');
        $sessionKey = $session->htmlpage_key;

        $this->key = $this->_getParam('key', $sessionKey ?: 0);
    }

    public function indexAction()
    {
        $this->_redirector->gotoSimple('index', 'list', 'htmlpage');
    }

    public function editAction()
    {
        $groupId = (int) $this->_request->getParam('group_id', 0);
        $group = $this->getService('HtmlpageGroup')->getOne(
            $this->getService('HtmlpageGroup')->findDependence('Htmlpage', $groupId)
        );

        if ($group->is_single_page && count($group->pages)) {
            $page = $group->pages->current();
            $this->_redirector->gotoSimple('edit', 'list', 'htmlpage', ['page_id' => $page->page_id]);
        }

        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_UPDATE));
                $this->_redirectToIndex();
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }
    
    public function update(Zend_Form $form)
    {
        $this->getService('HtmlpageGroup')->update(
            [
			    'group_id' => $form->getValue('group_id'),
                'name' => $form->getValue('name'),
                'role' => $form->getValue('role'),
                'ordr' => $form->getValue('ordr'),
            ]
        );
    }

	public function setDefaults(Zend_Form $form)
    {
        $groupId = (int)$this->_request->getParam('group_id', 0);
        $group = $this->getService('HtmlpageGroup')->getOne($this->getService('HtmlpageGroup')->find($groupId));
        if ($group) {
            $values = $group->getValues();
            $form->populate($values);
        }
    }
	
    public function delete($id)
    {
        $this->getService('HtmlpageGroup')->delete($id);
    }


    public function create(Zend_Form $form) {

        $group = $this->getService('HtmlpageGroup')->insert(
            [
                'name' => $form->getValue('name'),
                'role' => $form->getValue('role'),
                'ordr' => $form->getValue('ordr'),
            ],
            0
        );
        
        if($group)
            $page = $this->getService('Htmlpage')->insert(
                array(
                    'group_id' => $group->group_id,
                    'name' => $group->name, 
                    'ordr' => HM_Htmlpage_HtmlpageModel::ORDER_DEFAULT, 
                )
            );

        if ($page) {
            $this->_flashMessenger->addMessage(_('Группа страниц успешно создана'));
            $this->_redirector->gotoSimple('index', 'list', 'htmlpage');
        }

    }

    public function newAction()
    {
        $this->gridTraitNewAction();

        $role = $this->_request->getParam('key', $this->key);
        $form = $this->_getForm();

        $form->setDefault('role', $role);
        $this->view->form = $form;
    }

}