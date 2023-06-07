<?php

class Room_IndexController extends HM_Controller_Action
{
    /**
     * @throws Zend_Exception
     */
    public function indexAction()
    {
        $this->dataGrid = new HM_Room_DataGrid_RoomDataGrid($this->view);
    }

    public function newAction()
    {
        $form = new HM_Form_Room();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $values = $form->getValues();
                unset($values['rid']);
                $room = $this->getService('Room')->insert($values);
                $this->_flashMessenger->addMessage(_('Аудитория успешно создана'));
                $this->_redirector->gotoSimple('index', 'index', 'room');
            }
        }
        $this->view->form = $form;
    }

    public function deleteAction()
    {
        $roomId = (int) $this->_getParam('rid', 0);
        if ($roomId) {
            $this->getService('Room')->delete($roomId);
            $this->_flashMessenger->addMessage(_('Аудитория успешно удалена'));
        }
        $this->_redirector->gotoSimple('index', 'index', 'room');
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                foreach($ids as $id) {
                    $this->getService('Room')->delete($id);
                }
                $this->_flashMessenger->addMessage(_('Аудитории успешно удалены'));
            }
        }
        $this->_redirector->gotoSimple('index', 'index', 'room');
    }

    public function editAction()
    {
        $roomId = (int) $this->_getParam('rid', 0);
        $form = new HM_Form_Room();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $room = $this->getService('Room')->update($form->getValues());
                $this->_flashMessenger->addMessage(_('Аудитория успешно отредактирована'));
                $this->_redirector->gotoSimple('index', 'index', 'room');
            }
        } else {
            $collection = $this->getService('Room')->find($roomId);
            if (count($collection)) {
                $room = $collection->current();
                $form->setDefaults($room->getValues());
            }
        }
        $this->view->form = $form;
    }

    public function validateFormAction($form = null)
    {
        $form = new HM_Form_Room();
        parent::validateFormAction($form);
    }
}