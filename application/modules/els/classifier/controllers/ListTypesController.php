<?php
class Classifier_ListTypesController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    public function init()
    {
        $this->_setForm(new HM_Form_ClassifiersTypes());
        parent::init();
    }

    public function indexAction()
    {
        $select = $this->getService('ClassifierType')->getSelect();
        $select->from(
        array('ct' => 'classifiers_types'),
        array(
                    'type_id' => 'ct.type_id',
                    'name' => 'ct.name',
                    'link_types' => 'ct.link_types'
        ));

        $grid = $this->getGrid(
            $select,
            array(
                'type_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url([
                        'module' => 'classifier',
                        'controller' => 'list',
                        'action' => 'index',
                        'keyType' => 'type',
                        'key' => '{{type_id}}'
                    ], null, false, false).'">{{name}}</a>'),
                'link_types' => array('title' => _('Область применения'))
            ),
            array(
                    'type_id' => null,
                    'name' => null,
                    'link_types' => array('values' => HM_Classifier_Link_LinkModel::getTypes())
            )
        );

        $grid->updateColumn('link_types',
                array(
                    'callback' =>
                    array(
                        'function' => array('HM_Classifier_Link_LinkModel', 'IdsToNames'),
                        'params' => array('{{link_types}}')
                    ))
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $grid->addAction(
                array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'edit'),
                array('type_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(
                array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'delete'),
                array('type_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(array(
                'module' => 'classifier',
                'controller' => 'list-types',
                'action' => 'delete-by'
            ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

            $grid->setActionsCallback(
                array('function' => array($this,'updateActions'),
                    'params'   => array('{{type_id}}')
                )
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT => _('Классификатор успешно создан'),
            self::ACTION_UPDATE => _('Классификатор успешно обновлён'),
            self::ACTION_DELETE => _('Классификатор успешно удалён'),
            self::ACTION_DELETE_BY => _('Классификаторы успешно удалены')
        );
    }


    public function setDefaults(Zend_Form $form)
    {
        $typeId = (int) $this->_request->getParam('type_id', 0);

        $cltype = $this->getService('ClassifierType')->getOne($this->getService('ClassifierType')->find($typeId));
        if ($cltype)
        {
            $values = $cltype->getValues();
            $values['link_types'] = $cltype->getTypes();
            $form->populate($values);
            //$form->setDefaults($subject->getValues());
        }
    }

    public function create(Zend_Form $form)
    {
        $cltype = $this->getService('ClassifierType')->insert(
                    array(
                            'name' => $form->getValue('name')
                    )
        );

        if ($cltype) {
            $cltype->setTypes($form->getValue('link_types'));
            $cltype = $this->getService('ClassifierType')->update(
                $cltype->getValues()
            );
        }

        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($cltype->type_id);
    }

    public function update(Zend_Form $form)
    {

        /*
        $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));
        $this->_redirectToIndex();
        */

        $cltype = $this->getService('ClassifierType')->update(
                    array(
                            'type_id' => $form->getValue('type_id'),
                            'name' => $form->getValue('name')
                    )
        );

        if ($cltype) {
            $cltype->setTypes($form->getValue('link_types'));
//RED/////////////////////
            $oldTypes     = $cltype->getTypes();
            $newTypes     = $cltype->getTypes();
            $allowedTypes = HM_Classifier_Link_LinkModel::getEditTypes($this->getService('User')->getCurrentUserRole());
            foreach($oldTypes as $oldType) {
                if (!isset($allowedTypes[$oldType])) {
                    $newTypes[] = $oldType;
                    $cltype->setTypes($newTypes);
                }
            }
///////////////////////
            $cltype = $this->getService('ClassifierType')->update(
                $cltype->getValues()
            );
        }
        /*
         * Удалить все ссылки на рубрики, которые не относятся к области применения текущего классификатора.
         */
        $link_types = $form->getValue('link_types');
        if(is_string($link_types))
            $link_types = array();
        else{
            if(in_array(HM_Classifier_Link_LinkModel::TYPE_RESOURCE, $link_types))
                $link_types = array_merge ($link_types, HM_Classifier_Link_LinkModel::getResourceTypes());
            if(in_array(HM_Classifier_Link_LinkModel::TYPE_UNIT, $link_types))
                $link_types = array_merge ($link_types, HM_Classifier_Link_LinkModel::getUnitTypes());
        }
        $classifiers = $this->getService('Classifier')->fetchAll('type = '. intval($cltype->type_id));
        if(count($classifiers)){
            foreach($classifiers as $classifier){
                $this->getService('ClassifierLink')
                    ->deleteBy(array('not type IN(?)' => count($link_types) ? $link_types : array(0), 'classifier_id = ?' => $classifier->classifier_id));
            }
        }

        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($cltype->type_id);
    }

    public function delete($id)
    {
        if (in_array($id, HM_Classifier_Type_TypeModel::getBuiltInTypes())) return false;

        $ret = $this->getService('ClassifierType')->delete($id);
        $this->getService('DeanResponsibility')->checkForUnlimitedClassifiers($id);
        return $ret;
    }

    public function deleteAction()
    {
        $params = $this->_getAllParams();
        foreach($params as $key => $value) {
            if (substr($key, -3) == '_id') {
                $this->_setParam('id', $value);
                break;
            }

            if (in_array($key, array('subid', 'projid'))) { // hack
                $this->_setParam('id', $value);
            }
        }

        $id = (int) $this->_getParam('id', 0);
        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(HM_Controller_Action::ACTION_DELETE));
        }

        $this->_redirector->gotoSimple('index');
    }

    public function updateActions($type, $actions)
    {
        if (in_array($type, HM_Classifier_Type_TypeModel::getBuiltInTypes())) {
            $this->unsetAction($actions, array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'edit'));
            $this->unsetAction($actions, array('module' => 'classifier', 'controller' => 'list-types', 'action' => 'delete'));
        }
        return $actions;
    }
}

