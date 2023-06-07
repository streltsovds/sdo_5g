<?php
class Project_CatalogController extends HM_Controller_Action
{
    protected $_classifiers = Null;

    protected $_itemType;

    public function init(){
        $this->_itemType = HM_Classifier_Link_LinkModel::TYPE_SUBJECT;
        parent::init();
    }

    public function indexAction()
    {
        $classifierId = (int) $this->_getParam('classifier_id', 0);
        $item = (int) $this->_getParam('item', 0);
        $classifierId = (!$classifierId) ? $item : $classifierId;
        $type = (int) $this->_getParam('type', 0);

        if (!$this->_getParam('ordergrid', '')) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

        //подтверждение перехода при подаче заявки на один курс
        $confId = $this->_getParam('confirm_id', 0);
        if ($confId && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)) {
            $confProject = $this->getService('Project')->getOne($this->getService('Project')->find($confId));

            if ($confProject) {
                $curDate   = new HM_Date();
                $isCurrent = false;
                if (strtotime($confProject->begin)) {
                    $startDate = new HM_Date($confProject->begin);
                    $isCurrent = ($curDate->getTimestamp() >= $startDate->getTimestamp())? true : false;
                }
                if(
                    (in_array($confProject->period, array(HM_Project_ProjectModel::PERIOD_FREE, HM_Project_ProjectModel::PERIOD_FIXED))) ||
                    ($confProject->period == HM_Project_ProjectModel::PERIOD_DATES && $isCurrent) ||
                    ($confProject->period_restriction_type == HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL && $confProject->state == HM_Project_ProjectModel::STATE_ACTUAL)

                ) {
                    $this->view->confirmID = $confId;
                }
            }



        }

        $select = $this->getService('Project')->getSelect();

        $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));

        $types = $this->getService('Classifier')->getTypes($this->_itemType);

        if (!$type) {
            if (count($types)) {
                $type = array_shift(array_keys($types));
            }
        }
        $select->from(array('s' => 'projects'),
            array(
                'projid' => 's.projid',
                'name' => 's.name',
                'reg_type' => 's.reg_type',
                'claimant_process_id' => 's.claimant_process_id',
                'classLeft' => new Zend_Db_Expr('MIN(class.lft)'),
                'classes' => new Zend_Db_Expr("''"),
                'participant' => 'st.SID'
            )
        )
        ->where($this->quoteInto(
            array(
                's.period IN (?) OR ',
                's.period_restriction_type = ? OR ',
                '(s.period_restriction_type = ?',' AND (s.state = ? ',' OR s.state = ?) ) OR ',
                '(s.period = ? AND ',
                's.end > ?)',
            ),
            array(
                array(HM_Project_ProjectModel::PERIOD_FREE, HM_Project_ProjectModel::PERIOD_FIXED),
                HM_Project_ProjectModel::PERIOD_RESTRICTION_DECENT,
                HM_Project_ProjectModel::PERIOD_RESTRICTION_MANUAL,
                HM_Project_ProjectModel::STATE_ACTUAL,
                HM_Project_ProjectModel::STATE_PENDING,
                HM_Project_ProjectModel::PERIOD_DATES,
                $this->getService('Project')->getDateTime()
            )
        ))
        ->where('s.reg_type = ?', HM_Project_ProjectModel::REGTYPE_SELF_ASSIGN)
        ->order('classLeft ASC')
        ->group(array('s.projid', 's.name', 's.reg_type', 's.claimant_process_id','st.SID'));

        if ($classifierId && $classifier->level >= 0) {

            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $classifierId)));

            $select
                ->joinInner(array('c' => 'classifiers_links'), 's.projid = c.item_id AND c.type = '.(int) HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinInner(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('class.lft >= ?', $classifier->lft)
                ->where('class.rgt <= ?', $classifier->rgt);

        } else {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.projid = c.item_id AND c.type = '.(int) HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array());
        }

        //if ($this->getService('User')->getCurrentUserId()) {
        // при таком условии гость вообще никогда не увидит
            $userId = (int)$this->getService('User')->getCurrentUserId();
            $select->joinLeft(array('st' => 'Participants'), 's.projid = st.CID AND st.MID = ' . $userId, array())
                   /*->where('st.CID IS NULL')*/;
            $select->joinLeft(array('cl' => 'claimants'), 's.projid = cl.CID AND cl.status = ' . HM_Role_ClaimantModel::STATUS_NEW . ' AND cl.MID = ' . $userId, array())
                   ->where('cl.CID IS NULL');
        //}

        $grid = $this->getGrid($select, array(
            'projid' => array('hidden' => true),
            'classLeft' => array('hidden' => true),
            'classes' => array('title' => _('Классификация')),
            'name' => array(
                'title' => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'project', 'controller' => 'list', 'action' => 'card', 'project_id' => ''), null, true) . '{{projid}}', _('Карточка учебного курса')) . '{{name}}'
            ),
            'reg_type' => array('hidden' => true),
            'claimant_process_id' => array(
                'title' => _('Согласование заявок'),
                'callback' => array('function' => array($this, 'updateClaimProcType'), 'params' => array('{{claimant_process_id}}'))
            ),
            'participant' => array('hidden' => true)
        ),
            array(
                'name' => null,
            )

        );
        $grid->updateColumn('classes',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateClassifiers'),
                    'params' => array('{{projid}}', $select, $type)
                )
            )
        );

        $grid->updateColumn('name',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{name}}', '{{projid}}')
                )
            )
        );

        if ($this->getService('Option')->getOption('regAllow') !== '0') {
            $grid->addAction(array(
                'module' => 'user',
                'controller' => 'reg',
                'action' => 'project'
            ),
                array('projid'),
                _('Подать заявку'),
                $this->getService('User')->getCurrentUserId() ? _('Данное действие может быть необратимым. Вы действительно хотите продолжить?') : null
            );

             $grid->addMassAction(array(
                    'module' => 'user',
                    'controller' => 'reg',
                    'action' => 'projects',
                    'status' => array(
                    'status' => $status)
                ),
                _('Подать заявку'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        if ($this->getService('User')->getCurrentUserId()) $grid->setClassRowCondition("'{{participant}}' != ''", "success");

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{participant}}')
            )
        );

        $tree = $this->getService('Classifier')->getTreeContent($this->_itemType, 0, $type, false, $classifierId);
        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->tree = $tree;

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }

    public function updateActions($isParticipant, $actions)
    {
        return ($isParticipant)? "" : $actions;
    }

    public function updateClaimProcType($claimProcId)
    {
        return $claimProcId ? _('Согласование организатором обучения') : _('Автоматическое назначение на курс');
    }

    public function getTreeBranchAction()
    {
        $key = (int) $this->_getParam('key', 0);

        $children = $this->getService('Classifier')->getTreeContent($this->_itemType, $key);

        echo HM_Json::encodeErrorSkip($children);
	    exit;
    }

    public function prepareAction()
    {
        /*
        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Школьная программа'),
                'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Математика'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Русский язык'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_RESOURCE
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Группы пользователей'),
                'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Группа 1'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Группа 2'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_GROUP
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Виды деятельности и темы обучения'),
                'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Тема 1'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Тема 2'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_ACTIVITY
                ),
                $node->classifier_id
            );
        }

        $node = $this->getService('Classifier')->insert(
            array(
                'name' => _('Образовательные огранизации'),
                'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
            )
        );

        if ($node) {
            $this->getService('Classifier')->insert(
                array(
                    'name' => _('ВУЗ'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
                ),
                $node->classifier_id
            );

            $this->getService('Classifier')->insert(
                array(
                    'name' => _('Школа'),
                    'type' => HM_Classifier_ClassifierModel::TYPE_EDUCATION
                ),
                $node->classifier_id
            );
        }

        //pr($this->getService('Classifier')->getTree('node.type = '.HM_Classifier_ClassifierModel::TYPE_RESOURCE));
        */
        die('done');

    }


    public function updateClassifiers($projectId,Zend_Db_Select $select, $type)
    {

        if($this->_classifiers == Null){

            $query = $select->query();
            $fetch = $query->fetchAll();
            $ids = array();
            foreach($fetch as $value){
                $ids[] = $value['projid'];
            }
            $values = $select->getAdapter()->quote($ids);
            $temp = $this->getService('ClassifierLink')->fetchAllDependenceJoinInner('Classifier', 'self.item_id IN (' . $values . ') AND self.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT . ' AND Classifier.type = ' . (int) $type);
            $this->_classifiers = $temp;

        }

        $ret = array();
        foreach($this->_classifiers as $val){
            if($val->item_id == $projectId){
                foreach($val->classifiers as $class){
                    $ret[] = $class->name;
                }
            }

        }

        return implode(',', $ret);
    }


    public function updateName($name, $projectId)
    {
        if (
            in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_GUEST, HM_Role_Abstract_RoleModel::ROLE_USER, HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        ){
            $type = $this->_getParam('type', null);
            $item = $this->_getParam('item', null);
            $classifierId = $this->_getParam('classifier_id', null);

            $marker = '';
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT)) {
                if (empty($this->projectsCache)) {
                    $this->projectsCache = Zend_Registry::get('serviceContainer')->getService('User')->getProjects()->getList('projid');
                }
                if (in_array($projectId, $this->projectsCache)) {
                    $marker = HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Вы уже зачислены на этот курс'), 1);
                }
            }

            return '<a href="' . $this->view->url(array('module' => 'project', 'controller' => 'list', 'action' => 'description', 'project_id' => $projectId, 'item' => $item, 'type' => $type, 'classifier_id' => $classifierId)) . '">' . $name . '</a>' . $marker;
        }else{
            return $name;
        }
    }


}