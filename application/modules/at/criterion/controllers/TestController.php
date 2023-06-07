<?php
class Criterion_TestController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_parent;
    
    protected $_adapter;
    protected $_childrenMap = array();

    public function init() {
        $this->_setForm(new HM_Form_Test());
        parent::init();
    }

    protected function _redirectToIndex()
    {
        $parent = (int) $this->_getParam('parent', 0);
        $this->_redirector->gotoSimple('index', 'test', 'criterion', array('parent' => $parent), null, true);
    }

    public function indexAction()
    {

//$this->repairAction();// запускаем, если сломалась структура. Все parent_id д.б. правильно выставлены вручную!

        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'name_ASC');
        }        
        
        $parent = $this->_parent = (int) $this->_getParam('key', 0);
        $level = 0;

        $select = $this->getService('AtCriterionTest')->getSelect();
        $select->from(
            array('c' => 'at_criteria_test'), 
            array(
                'c.criterion_id',
                'c.name',
                'c.lft',
                'c.rgt',
                'test' => 'q.name',
                'c.required',
                'c.validity',
                'profiles' => new Zend_Db_Expr('COUNT(DISTINCT p.profile_id)')
        ))
        ->joinLeft(array('apcv' => 'at_profile_criterion_values'), 'apcv.criterion_id = c.criterion_id AND apcv.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST , array())
        ->joinLeft(array('p' => 'at_profiles'), 'apcv.profile_id = p.profile_id', array())
        ->where('c.status=?', HM_At_Criterion_Test_TestModel::STATUS_ACTUAL)
        ->group(array(
                'c.criterion_id',
                'c.name',
                'c.lft',
                'c.rgt',
                'q.name',
                'c.required',
                'c.validity',
            ));

        if ($parent) {
            $criterion = $this->getOne($this->getService('AtCriterionTest')->find($parent));
            if ($criterion) {
                $level = $criterion->level + 1;

                $select->where('c.lft >= ?', $criterion->lft);
                $select->where('c.rgt <= ?', $criterion->rgt);
            }
        }

        $select->where('c.level = ?', $level)
//            ->where('c.criterion_id != ?', HM_At_Criterion_Test_TestModel::BUILTIN_BRANCH_PROFILES)
            ->joinLeft(array('q' => 'questionnaires'), 'c.quest_id = q.quest_id', array());

        $grid = $this->getGrid(
            $select,
            array(
                'criterion_id' => array('hidden' => true),
                'name' => array(
                    'title' => _('Название'),
                    'callback' => array('function' => array($this, 'updateName'), 'params' => array('{{name}}', '{{lft}}', '{{rgt}}'))
                ),
                'test' => array('hidden' => true),
//                array(
//                    'title' => _('Тест'),
//                ),
                'lft' => array('hidden' => true),
                'rgt' => array('hidden' => true),
                'required' => array('title' => _('Обязательная'),
                    'callback' => array(
                        'function'=> array($this, 'updateRequired'),
                        'params'=> array('{{required}}'))
                ),
                'validity' => array('title' => _('Срок действия'),
                    'callback' => array(
                        'function'=> array($this, 'updateValidity'),
                        'params'=> array('{{validity}}'))
                ),
                'profiles' => array('title' => _('Входит в профили'),
                    'callback' => array(
                        'function'=> array($this, 'updateProfiles'),
                        'params'=> array('{{profiles}}'))
                ),

            ),
            array(
                'name' => null,
                'test' => null,
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'edit'
            ),
                array('criterion_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'check-before-delete'
            ),
                array('criterion_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );


            $grid->addMassAction(array(
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'check-before-delete-by'
            ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('AtCriterionTest')->getTreeContent(0, false, $parent);
            $tree = array(
                0 => array(
                    'title' => _('Квалификации'),
                    'count' => 0,
                    'key' => 0,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => false,
                ),
                1 => $tree
            );

            $gridUrl = $this->view->url(
                [
                    'module' => 'criterion',
                    'controller' => 'test',
                    'action' => 'index',
                    'gridmod' => 'ajax',
                    'treeajax' => 'true'
                ],
                null,
                true
            );

            $rubricatorUrl = $this->view->url(
                [
                    'module' => 'criterion',
                    'controller' => 'test',
                    'action' => 'get-tree-branch'
                ],
                null,
                true
            );

            $rubricatorValue = null;

            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Квалификации'),
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ]
            );
        }

        $this->view->grid = $grid;
        $this->view->tree = $tree;
        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->gridmod = $this->_getParam('gridmod', false);
        $this->view->parent = $parent;
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
            self::ACTION_INSERT => _('Квалификация успешно создана'),
            self::ACTION_UPDATE => _('Квалификация успешно обновлёна'),
            self::ACTION_DELETE => _('Квалификация успешно удалёна'),
            self::ACTION_DELETE_BY => _('Квалификации успешно удалены')
        );
    }

    public function setDefaults(Zend_Form $form) 
    {
        $parentCriterionId = (int) $this->_getParam('parent', 0);
        $parentCriterion = $this->getOne($this->getService('AtCriterionTest')->find($parentCriterionId));
        
        $criterionId = (int) $this->_getParam('criterion_id', 0);
        $criterion = $this->getOne($this->getService('AtCriterionTest')->find($criterionId));
        
        if ($criterion) {
            $form->setDefaults($criterion->getValues());
        } elseif (!empty($parentCriterion->quest_id)) {
            $form->setDefaults(array(
                'quest_id' => $parentCriterion->quest_id
            ));
        }
    }

    public function update(Zend_Form $form) {
     
        $criterion = $this->getService('AtCriterionTest')->update(
            array(
                'criterion_id'  => $form->getValue('criterion_id'),
                'name'          => $form->getValue('name'),
                'description'   => $form->getValue('description'),
                'quest_id'      => $form->getValue('quest_id'),
                'required'      => $form->getValue('required', 0),
                'validity'      => $form->getValue('validity', 0),
                'employee_type' => $form->getValue('employee_type', 0),
            )
        );
        
        $item = $this->getService('AtCriterionTest')->find($form->getValue('criterion_id'))->current();
        $this->getService('AtCriterionTest')->updateWhere(
          array(
              'quest_id' => $form->getValue('quest_id')
          ),
          array(
              $this->getService('AtCriterionTest')->quoteInto(
                  array('lft > ? ',' AND lft < ? '),
                  array($item->lft, $item->rgt)
              )
          )
        );
    }

    public function create(Zend_Form $form)
    {
        $parent = (int) $this->_getParam('key', 0);
        $criterion = $this->getService('AtCriterionTest')->insert(
            array(
                'name' => $form->getValue('name'),
                'status'        => HM_At_Criterion_Test_TestModel::STATUS_ACTUAL,
                'description'   => $form->getValue('description'),
                'quest_id'      => $form->getValue('quest_id'),
                'required'      => $form->getValue('required', 0),
                'validity'      => $form->getValue('validity', 0),
                'employee_type' => $form->getValue('employee_type', 0),
            ),
            $parent
        );
    }

    public function delete($id)
    {
        return $this->getService('AtCriterionTest')->deleteNode($id, true);
    }
    
    public function checkBeforeDeleteAction() {
        $criterion_id = $this->_getParam('criterion_id', 0);
        $type = HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST;
        $subjectService = $this->getService('Subject');
        
        $subjects = $subjectService->getSubjectsWithCompetences($criterion_id, $type);

        if ($subjects) {
            $this->view->url = $this->view->url(array(
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'delete',
            ));

            $this->view->subjects = $subjects;
            $this->view->criterion_id = $criterion_id;
        } else {
            $this->deleteAction();
        }
        
    }
    
    public function checkBeforeDeleteByAction() {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $type = HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST;
        $subjectService = $this->getService('Subject');
        
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $subjects = $subjectService->getSubjectsWithCompetences($ids, $type);
            }
        }

        if ($subjects) {
            $this->view->url = $this->view->url(array(
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'delete-by',
            ));

            $this->view->subjects = $subjects;
            $this->view->postMassIds = $postMassIds;

        } else {
            $this->deleteByAction();
        }
}
    
    
    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('AtCriterionTest')->getTreeContent($key, true);

        echo HM_Json::encodeErrorSkip($children);
	    exit;
    }   

    // нужно по дефолту установить id теста родительской квалификации
    public function newAction()
    {
        $form = $this->_getForm();
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($form->isValid($request->getParams())) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex();
                }
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function updateRequired($required)
    {
        if($required){
            return _('Да');
        }else{
            return _('Нет');
        }
    }

    public function updateValidity($validity)
    {
        return ($validity) ? $validity : _('Бессрочная') ;
    }

    public function updateProfiles($profiles)
    {
        return ($profiles) ? sprintf(_n('профиль plural', '%s профиль', $profiles), $profiles) : _('Нет');
    }



    // восстанавливет left-right-level по parent_id
    public function repairAction()
    {
        $this->_adapter = $this->getService('AtCriterionTest')->getMapper()->getAdapter()->getAdapter();
        
        $select = $this->getService('AtCriterionTest')->getSelect()->from('at_criteria_test', array('criterion_id', 'parent_id'));

        $rowset = $select->query()->fetchAll();
        foreach($rowset as $row) {
        
            if (++$_childrenMapCount%1000 == 0) Zend_Registry::get('log_system')->log('children map loop: ' . $_childrenMapCount, Zend_Log::ERR);
        
            if (!isset($this->_childrenMap[$row['parent_id']])) {
                $this->_childrenMap[$row['parent_id']] = array($row['criterion_id']);
            } else {
                $this->_childrenMap[$row['parent_id']][] = $row['criterion_id'];
            }
        }
        
        Zend_Registry::get('log_system')->log('recursive update start', Zend_Log::ERR);
        if (count($this->_childrenMap[0])) {
            $left = 0;
            foreach ($this->_childrenMap[0] as $soid) {
                $left = $this->_update($soid, ++$left);
            }
        }
        
        $this->_flashMessenger->addMessage(array(
            'type' => HM_Notification_NotificationModel::TYPE_SUCCESS,
            'message' => _('Структура квалификаций восстановлена')
        ));
    }
    
    protected function _update($soid, $left = 1, $level = 0, $ownerSoid = 0)
    {
        static $_updateCount;
        if (++$_updateCount%1000 == 0) Zend_Registry::get('log_system')->log('recursive update loop: ' . $_updateCount, Zend_Log::ERR);
    
        $right = $left + 1;
        if (is_array($this->_childrenMap[$soid]))
        foreach($this->_childrenMap[$soid] as $childSoid) {
            $right = $this->_update($childSoid, $right, $level + 1, $soid);
        }
        $this->_adapter->query("UPDATE at_criteria_test SET lft={$left}, rgt={$right}, level={$level} WHERE criterion_id={$soid}");
        return ++$right;
    }    

}