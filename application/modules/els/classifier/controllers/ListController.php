<?php
class Classifier_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    private $_classifierType = null;
    private $_classifier = null;

    public function init()
    {
        $this->_setForm(new HM_Form_Classifier());
        parent::init();

        $this->gridId = 'classifierListGrid';

        $keyType = $this->_getParam('keyType', 'type');
        $key = (int)$this->_getParam('key', 0);

        if ($keyType == 'classifier') {
            $collection = $this->getService('Classifier')->findDependence('ClassifierType', $key);
            if (count($collection)) {
                $this->_classifier = $collection->current();
                $this->_classifierType = $this->_classifier->types->current();
            }
        } elseif ($keyType == 'type') {
            $this->_classifierType = $this->getService('ClassifierType')->findOne($key);
        }

        if ($this->_classifierType) {
            $this->view->setHeader($this->_classifierType->name);
        }
    }

    protected function _redirectToIndex()
    {
        if ($this->_classifier) {
            $this->_redirector->gotoSimple(
                'index',
                'list',
                'classifier', [
                'keyType' => 'classifier',
                'key' => $this->_classifier->classifier_id
            ]);
        } elseif ($this->_classifierType) {
            $this->_redirector->gotoSimple(
                'index',
                'list',
                'classifier', [
                'keyType' => 'type',
                'key' => $this->_classifierType->type_id
            ]);
        } else {
            $this->_redirector->gotoSimple(
                'index',
                'list-types',
                'classifier'
            );
        }
    }

    public function indexAction()
    {
        if (empty($this->_classifierType)) {
            $this->_flashMessenger->addMessage([
                'message' => _('Не указан классификатор'),
                'type' => HM_Notification_NotificationModel::TYPE_ERROR,
            ]);
            $this->_redirectToIndex();
        }

        $this->view->setSubHeader(_('Рубрики классификатора'));
        $this->view->setBackUrl($this->view->url([
            'module' => 'classifier',
            'controller' => 'list-types',
            'type' => null
        ]));

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }

        $level = 0;

        $select = $this->getService('Classifier')->getSelect();
        $select->from('classifiers', array('classifier_id', 'name', 'lft', 'rgt'));
        $select->where('type = ?', $this->_classifierType->type_id);

        if ($this->_classifier) {

            $level = $this->_classifier->level + 1;
            $select->where('lft >= ?', $this->_classifier->lft);
            $select->where('rgt <= ?', $this->_classifier->rgt);
        }

        $select->where('level = ?', $level);

//        echo $select->__toString(); exit();

        $grid = $this->getGrid(
            $select,
            array(
                'classifier_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'decorator' => '<a href="'.$this->view->url([
                            'module' => 'classifier',
                            'controller' => 'list',
                            'action' => 'index',
                            'keyType' => 'classifier',
                            'key' => '{{classifier_id}}'
                        ], null, false, false).'">{{name}}</a>'),
//                    'callback' => array('function' => array($this, 'updateName'), 'params' => array('{{name}}', '{{lft}}', '{{rgt}}'))
//                ),
                'lft' => array('hidden' => true),
                'rgt' => array('hidden' => true)
            ),
            array(
                'name' => null
            )
        );

        if (!$this->currentUserRole(HM_Role_LaborSafetyModel::ROLE_LABOR_SAFETY_LOCAL)) {

            $grid->addAction(array(
                'module' => 'classifier',
                'controller' => 'list',
                'action' => 'edit'
            ),
                array('classifier_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'classifier',
                'controller' => 'list',
                'action' => 'delete'
            ),
                array('classifier_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(array(
                'module' => 'classifier',
                'controller' => 'list',
                'action' => 'delete-by'
            ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $tree = [
            [
                'title' => $this->_classifierType->name,
                'count' => 0,
                'key' => $this->_classifierType->type_id,
                'keyType' => 'type',
                'isLazy' => true,
                'isFolder' => true,
                'expand' => false,
                'active' => false,
            ],
        ];

        if (!$this->isAjaxRequest()) {

            $rubricatorValue = !empty($this->_classifier)
                ? $this->getService('Classifier')->classifierToFrontendData(
                    $this->_classifier,
                    $this->_classifier->classifier_id
                )
                : null;

            /** @see HM_View_Helper_VueRubricatorGridButton */

            $urlParams = [
                'module' => 'classifier',
                'controller' => 'list',
                'action' => 'index',
                'gridmod' => 'ajax',
                'keyType' => null,
                'key' => null,
            ];

            $gridUrl = $this->view->url($urlParams);

            $rubricatorUrl = $this->view->url(array(   // url
                'module' => 'classifier',
                'controller' => 'ajax',
                'action' => 'get-tree-branch',
                'keyType' => null,
                'key' => null,
            ));

            $classifiers = $this->getService('Classifier')->fetchAll(['type = ?' => $this->_classifierType->type_id]);
            $autoOpen = (bool)count($classifiers);

            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Рубрики'), // buttonLabel
                $rubricatorValue, // current item
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ],
                $autoOpen
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateName($name, $lft, $rgt)
    {
        $class = 'icon-folder';
        if ($lft+1 == $rgt) {
            $class = 'icon-item';
        }
        return sprintf('<span class="%s"></span>', $class).$name;
    }

    protected function _getMessages() {

        return array(
            self::ACTION_INSERT => _('Рубрика успешно создана'),
            self::ACTION_UPDATE => _('Рубрика успешно обновлёна'),
            self::ACTION_DELETE => _('Рубрика успешно удалёна'),
            self::ACTION_DELETE_BY => _('Рубрики успешно удалены')
        );
    }

    public function setDefaults(Zend_Form $form)
    {
        $classifierId = (int) $this->_getParam('classifier_id', 0);
        $classifier = $this->getOne($this->getService('Classifier')->find($classifierId));
        if ($classifier) {
            $form->setDefaults($classifier->getValues());
        }
    }

    public function update(Zend_Form $form)
    {
        $this->view->setSubHeader('Редактировать рубрику');

        $this->_classifier = $this->getService('Classifier')->update(
            array(
                'classifier_id' => $form->getValue('classifier_id'),
                'name' => $form->getValue('name')
            )
        );
    }

    public function create(Zend_Form $form)
    {
        $this->view->setSubHeader('Создать рубрику');

        $this->_classifier = $this->getService('Classifier')->insert(
            array(
                'name' => $form->getValue('name'),
                'type' => $this->_classifierType->type_id
            ),
            $this->_classifier ? $this->_classifier->classifier_id : 0
        );
    }

    public function delete($id)
    {
        return $this->getService('Classifier')->deleteNode($id, true);
    }
}
