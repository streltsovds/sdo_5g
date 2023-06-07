<?php
class Profile_CriterionController extends HM_Controller_Action_Profile
{
    use HM_Controller_Action_Trait_Grid;

    protected $_criterionType;
    
    public function init()
    {
        $this->_criterionType = $this->_getParam('criterion-type', HM_At_Criterion_CriterionModel::TYPE_CORPORATE);
        return parent::init();
    }
    
    // context.xml хочет, чтобы URL'ы были разные
    public function corporateAction()
    {
        $this->_criterionType = HM_At_Criterion_CriterionModel::TYPE_CORPORATE;
        $grid = $this->_index();
        $this->view->grid = $grid;
    }
    
    public function professionalAction()
    {
        $parent = (int) $this->_getParam('key', 0);
        $this->_criterionType = HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL;

        $gridId = "grid_{$this->_profile->profile_id}_{$this->_criterionType}";

        $default = new Zend_Session_Namespace('default');
        if (!isset($default->grid['profile-criterion-index'][$gridId])) {
            $default->grid['profile-criterion-index'][$gridId]['filters']['profile'] = $this->_profile->profile_id;
        }

        $order = $this->_getParam("order{$gridId}");

        if ($order == '') {
            $this->_request->setParam("order{$gridId}", $this->_profile->profile_id ? 'profile_DESC' : 'name_ASC');
        }

        $fields = array(
            'criterion_id',
            'ac.name',
            'profile' => 'apcv.profile_id',
            'name_test' => 'qt.name',
            'name_psycho' => 'qp.name'
        );

        $select = $this->getService('AtProfileCriterionValue')->getSelect($this->_criterionType, $fields)
            ->order('ac.name');

        $select
            ->joinLeft(array('qt' => 'questionnaires'), 'ac.quest_id = qt.quest_id', array())
            ->joinLeft(array('qp' => 'questionnaires'), 'ac.quest_id = qp.quest_id', array());

        $switcher = $this->getSwitcherSetOrder($this->_profile->profile_id, 'assigned_DESC');

        if (!$switcher) {
            $select->joinInner(array('apcv' => 'at_profile_criterion_values'), '
                ac.criterion_id = apcv.criterion_id AND 
                apcv.criterion_type = ' . $this->_criterionType . ' AND 
                apcv.profile_id = ' . $this->_profile->profile_id
                , array('assigned' => '0'));
        } else {
            $select->joinLeft(array('apcv' => 'at_profile_criterion_values'), '
                ac.criterion_id = apcv.criterion_id AND 
                apcv.criterion_type = ' . $this->_criterionType . ' AND 
                apcv.profile_id = ' . $this->_profile->profile_id
                , array('assigned' => '0'));
        }

        $level = 0;

        if ($parent) {
            $criterion = $this->getOne($this->getService('AtCriterionTest')->find($parent));
            if ($criterion) {
                $level = $criterion->level + 1;

                $select->where('lft >= ?', $criterion->lft);
                $select->where('rgt <= ?', $criterion->rgt);
            }
        }

        $select->where('level = ?', $level);

        $grid = $this->getGrid($select, array(
            'criterion_id' => array('hidden' => true),
            'name_test' => array('hidden' => true),
            'name_psycho' => array('hidden' => true),
            'name' => array(
                'title' => HM_At_Criterion_CriterionModel::getCompetenceType($this->_criterionType),
            ),
            'profile' => array(
                'title' => _('Назначена'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params'=> array('{{profile}}')
                ),
            ),
        ),
            array(
                'name' => null,
            ),
            $gridId
        );

        $title = _('квалификации');

        $grid->addMassAction(
            array(
                'module' => 'profile',
                'controller' => 'criterion',
                'action' => 'assign',
                'criterion-type' => $this->_criterionType,
            ),
            sprintf(_('Назначить %s профилю'), $title),
            sprintf(_('Вы действительно хотите назначить %s профилю? При этом они будут включены во все методики оценки, входящие в программы подбора и регулярной оценки, использующие %s'), $title, $title)
        );

        $grid->addMassAction(
            array(
                'module' => 'profile',
                'controller' => 'criterion',
                'action' => 'unassign',
                'criterion-type' => $this->_criterionType,
            ),
            _('Отменить назначение'),
            sprintf(_('Вы действительно хотите отменить назначение? При этом %s будут исключены из всех методики оценки, входящих в программы подбора и регулярной оценки'), $title)
        );

        $grid->setGridSwitcher([
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'label' => _('Показать все'),
            'title' => _('Показать все критерии, в том числе не назначенные данному профилю'),
        ]);


        $grid->setClassRowCondition("'{{profile}}' != {$this->_profile->profile_id}", '', 'selected');
        
        $this->view->grid = $grid;

        if (!$this->isAjaxRequest()) {
            $tree = $this->getService('AtCriterionTest')->getTreeContent();

            $tree = array(
                0 => array(
                    'title' => _('Квалификации'),
                    'count' => 0,
                    'key' => 0,
                    'isLazy' => true,
                    'isFolder' => true,
                    'expand' => true
                ),
                1 => $tree
            );

            $this->view->tree = $tree;
            $rubricatorValue = null;
            $gridUrl = $this->view->url(array(
                'module' => 'profile',
                'controller' => 'criterion',
                'action' => 'professional',
                'profile_id' => $this->_profileId,
                'gridmod' => 'ajax',
                'treeajax' => 'true',
                'keyType' => null,
            ), null, true);
            $rubricatorUrl = $this->view->url(array(
//              'baseUrl' => 'at',
                'module' => 'criterion',
                'controller' => 'test',
                'action' => 'get-tree-branch'
            ), null, true);

            /** @see HM_View_Helper_VueRubricatorGridButton */
            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Классификация'),
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ]
            );
        }

        $this->view->gridAjaxRequest = $this->isAjaxRequest();
        $this->view->gridmod = $this->_getParam('gridmod', false);
        $this->view->parent = $parent;
    }
    
    public function personalAction()
    {
        $this->_criterionType = HM_At_Criterion_CriterionModel::TYPE_PERSONAL;
        $grid = $this->_index();
        $grid->updateColumn('name_psycho', array('title' => _('Психологический опрос'), 'hidden' => false));
        
        $this->view->grid = $grid;
    }
    
    public function updateAdaptationTimeAction()
    {
        $request = $this->getRequest();
        $profileID = (int)$this->_getParam('profile_id');
        if($this->isAjaxRequest() && $request->isPost()){
            $profiles = $this->getService('AtProfile')->fetchAll();
            foreach ($profiles as $profile) {
                if ($profile->profile_id == $profileID) {
                    $doubleTime = ($profile->double_time == 0) ? 1 : 0;
                    $this->getService('AtProfile')->updateWhere(
                        array('double_time' => $doubleTime),
                        array('profile_id=?'  => $profileID)
                    );
                }
            }

            $this->_helper->viewRenderer->setNoRender();
        }
    }
    
    public function _index()
    {
        $isAtCompetenceRemoved = (bool)$this->getService('Extension')->getRemover("HM_Extension_Remover_AtCompetenceRemover");

        $this->gridId = $gridId = "grid_{$this->_profile->profile_id}_{$this->_criterionType}";
        
        $default = new Zend_Session_Namespace('default');
    	if (!isset($default->grid['profile-criterion-index'][$gridId])) {
    		$default->grid['profile-criterion-index'][$gridId]['filters']['profile'] = $this->_profile->profile_id;
    	}
    	
        $order = $this->_getParam("order{$gridId}");
        
        if ($order == ''){
            $this->_request->setParam("order{$gridId}", $this->_profile->profile_id ? 'profile_DESC' : 'name_ASC');
        }
        
        $fields = array(
            'criterion_id',
            'ac.name',
            'profile' => 'apcv.profile_id',
        );
        
        if ($this->_criterionType != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
            $fields['name_test'] = 'qt.name';
            $fields['name_psycho'] = 'qp.name';
        }
        
        $select = $this->getService('AtProfileCriterionValue')->getSelect($this->_criterionType, $fields)
            ->order('ac.name');

        if ($this->_criterionType != HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
            $select
            ->joinLeft(array('qt' => 'questionnaires'), 'ac.quest_id = qt.quest_id', array())
            ->joinLeft(array('qp' => 'questionnaires'), 'ac.quest_id = qp.quest_id', array());
        } else {
//            if ($this->_profile->category_id){
//                $select->where('ac.category_id = ?', $this->_profile->category_id);
//            }
            if ($this->_criterionType == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
// @todo: реализовать soft-delete и вернуть условие, оно правильное
//                $select->where('ac.status = ?', HM_At_Criterion_CriterionModel::STATUS_ACTUAL);
            }
        }

        $switcher = $this->getSwitcherSetOrder($this->_profile->profile_id, 'assigned_DESC');

        if ($switcher) {
            $select->joinLeft(['apcv' => 'at_profile_criterion_values'], '
                ac.criterion_id = apcv.criterion_id AND 
                apcv.criterion_type = ' . $this->_criterionType . ' AND 
                apcv.profile_id = ' . $this->_profile->profile_id
                , ['assigned' => '0']);
        } else {
            $select->joinInner(['apcv' => 'at_profile_criterion_values'], '
                ac.criterion_id = apcv.criterion_id AND 
                apcv.criterion_type = ' . $this->_criterionType . ' AND 
                apcv.profile_id = ' . $this->_profile->profile_id
                , ['assigned' => '0']);
        }

        $titleArray = array( 'title' => HM_At_Criterion_CriterionModel::getCompetenceType($this->_criterionType));
        if (!$isAtCompetenceRemoved && $this->_criterionType == HM_At_Criterion_CriterionModel::TYPE_CORPORATE) {
            $titleArray['decorator'] = '<a href="'.$this->view->url(array(
                'module' => 'criterion',
                'controller' => 'indicator',
                'action' => 'index',
                'criterionId' => '{{criterion_id}}',
                'profile_id' => null
            ), null, false, false).'">{{name}}</a>';
        }

        $grid = $this->getGrid($select, array(
            'criterion_id' => array('hidden' => true),
            'name_test' => array('hidden' => true),
            'name_psycho' => array('hidden' => true),
            'name' => $titleArray,
            'profile' => array(
                'title' => _('Назначена'),
                'callback' => array(
                    'function'=> array($this, 'updateStatus'),
                    'params'=> array('{{profile}}')
                ),
            ),
        ),
            array(
                'name' => null,
            ),
            $gridId
        );
        
        switch ($this->_criterionType) {
            case HM_At_Criterion_CriterionModel::TYPE_CORPORATE:
                $title = _('компетенции');
                break;
            case HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL:
                $title = _('квалификации');
                break;
            case HM_At_Criterion_CriterionModel::TYPE_PERSONAL:
                $title = _('личностные характеристики');
                break;
        }

        $grid->addMassAction(
            array(
                'module' => 'profile',
                'controller' => 'criterion',
                'action' => 'assign',
                'criterion-type' => $this->_criterionType,
            ),
            sprintf(_('Назначить %s профилю'), $title),
            $isAtCompetenceRemoved
                ? sprintf(_('Вы действительно хотите назначить %s профилю?'), $title)
                : sprintf(_('Вы действительно хотите назначить %s профилю? При этом они будут включены во все методики оценки, входящие в программы подбора и регулярной оценки, использующие %s'), $title, $title)
        );

        $grid->addMassAction(
            array(
                'module' => 'profile',
                'controller' => 'criterion',
                'action' => 'unassign',
                'criterion-type' => $this->_criterionType,
            ),
            _('Отменить назначение'),
            sprintf(_('Вы действительно хотите отменить назначение? При этом %s будут исключены из всех методики оценки, входящих в программы подбора и регулярной оценки'), $title)
        );

        $grid->setGridSwitcher([
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'label' => _('Показать все'),
            'title' => _('Показать все критерии, в том числе не назначенные данному профилю'),
        ]);

        $grid->setClassRowCondition("'{{profile}}' != {$this->_profile->profile_id}", '', 'selected');

        return $grid;
    }

    public function assignAction()
    {
        $gridId = "grid_{$this->_profile->profile_id}_{$this->_criterionType}";
        
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {

            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $this->getService('AtProfileCriterionValue')->assign($this->_profile->profile_id, $ids, $this->_criterionType);
                $this->_flashMessenger->addMessage(_('Назначение выполнено успешно'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = "grid_{$this->_profile->profile_id}_{$this->_criterionType}";
        
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
    	if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $this->getService('AtProfileCriterionValue')->unassign($this->_profile->profile_id, $ids, $this->_criterionType);
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $action = 'corporate';
        switch ($this->_criterionType) {
            case HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL:
                $action = 'professional';
            break;
            case HM_At_Criterion_CriterionModel::TYPE_PERSONAL:
                $action = 'personal';
            break;
        }
        $this->_redirector->gotoSimple($action, 'criterion', 'profile', array('profile_id' => $this->_profile->profile_id));
    }    
    
    public function updateStatus($status)
    {
        return ($status != '') ?  _('Да') : _('Нет');
    }      
}
