<?php
class Event_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

	public function init()
    {
        parent::init();
        $this->_setForm(new HM_Form_Event());
    }

    public function indexAction()
    {
        $select = $this->getService('Event')->getSelect();

        $select->from('events', array('event_id', 'title', 'tool', 'weight'));

        $grid = $this->getGrid(
            $select,
            array(
                'event_id' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'tool' => array('title' => _('Инструмент обучения'), 'callback' => array('function' => array($this, 'updateTool'), 'params' => array('{{tool}}'))),
                'weight' => array('title' => _('Вес')),
            ),
            array(
                'title' => null,
                'tool' => array('values' => HM_Event_EventModel::getTypes())
            )
        );

        $grid->addAction(array(
           'module' => 'event',
           'controller' => 'list',
           'action' => 'edit'
       ),
           array('event_id'),
           $this->view->svgIcon('edit', 'Редактировать')
       );


       $grid->addAction(array(
           'module' => 'event',
           'controller' => 'list',
           'action' => 'delete'
       ),
           array('event_id'),
           $this->view->svgIcon('delete', 'Удалить')
       );

        $grid->addMassAction(array(
            'module' => 'event',
            'controller' => 'list',
            'action' => 'delete-by'
        ),
            _('Удалить'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(
            array(
                'function' => array($this, 'updateActions'),
                'params'   => array('{{event_id}}')
            )
        );

        $grid->autoGetHeaderActionsResourceString = 'mca:event:list:new';

        $this->view->grid = $grid;
        $this->view->isAjaxRequest = $this->isAjaxRequest();
    }


    public function updateActions($eventId, $actions)
    {

        if (in_array($eventId, array(HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY, HM_Event_EventModel::TYPE_OLYMPOX_EXAM, HM_Event_EventModel::TYPE_OLYMPOX_INTRO))) return '';

        return $actions;
    }



    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Тип успешно создан'),
            self::ACTION_UPDATE => _('Тип успешно обновлён'),
            self::ACTION_DELETE => _('Тип успешно удалён'),
            self::ACTION_DELETE_BY => _('Типы успешно удалены')
        );
    }

    public function create(Zend_Form $form)
    {
        $event = $this->getService('Event')->insert(
            array(
                'title' => $form->getValue('title'),
                'tool' => $form->getValue('tool'),
                'scale_id' => $form->getValue('scale_id'),
                'weight' => $form->getValue('weight')
            )
        );

        if ($event) {
            $this->getService('Event')->updateIcon($event->event_id, $form->getElement('icon'));
            return true;
        }
        return false;
    }

    public function update(Zend_Form $form)
    {
        $event = $this->getService('Event')->update(
            array(
                'title' => $form->getValue('title'),
                'tool' => $form->getValue('tool'),
                'scale_id' => $form->getValue('scale_id'),
                'event_id' => $form->getValue('event_id'),
                'weight' => $form->getValue('weight'),
            )
        );

        if ($event) {
            $this->getService('Event')->updateIcon($form->getValue('event_id'), $form->getElement('icon'));
            return true;
        }

        return false;

    }

    public function delete($id)
    {
        return $this->getService('Event')->delete($id);
    }

    public function setDefaults(Zend_Form $form)
    {
        $eventId = (int) $this->_getParam('event_id', 0);
        if ($eventId) {
            $event = $this->getOne($this->getService('Event')->find($eventId));
            if ($event) {
                $values = $event->getValues();
                $values['icon'] = $event->getIcon();
                $form->setDefaults($values);
            }
        }
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                if (count(array_intersect($ids, array(HM_Event_EventModel::TYPE_OLYMPOX_SELFSTUDY, HM_Event_EventModel::TYPE_OLYMPOX_EXAM, HM_Event_EventModel::TYPE_OLYMPOX_INTRO)))) {
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Нельзя удалять системные записи')));
                    $this->_redirectToIndex();
                } else {
                    foreach($ids as $id) {
                        $this->delete($id);
                    }
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function updateTool($tool)
    {
        $tools = HM_Event_EventModel::getTypes();
        return $tools[$tool];
    }
}