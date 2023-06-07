<?php
class Subject_CatalogController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $_classifiers = Null;
    protected $_itemType;

    public function init()
    {
        $this->_itemType = HM_Classifier_Link_LinkModel::TYPE_SUBJECT;

        if (!$this->isAjaxRequest()) $this->view->setSubHeader(_('Каталог учебных курсов'));

        parent::init();
    }

    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet($config->url->base.'css/content-modules/subjects.css');

        $this->addSearchSidebar();

        // получаем метки БЗ
        $tags = $this->getService('Tag')->getTagsRating(array_keys(HM_Tag_Ref_RefModel::getBZTypes()));

        // получаем информацию о классификаторах с областью применения "Курсы"
        $subjectsClassifiers = $this->getService('Classifier')->getSubjectsClassifiers();

        $subjects = $this->getService('Subject')->fetchAll(
            $this->quoteInto(
                [
                    ' status   != ? ',
                    ' AND base != ?',
                ],
                [
                    HM_Subject_SubjectModel::STATE_CLOSED,
                    HM_Subject_SubjectModel::BASETYPE_SESSION,
                ]
            )
        );

        // статистика Всего уч. курсов
        $statIRCount = count($subjects);

        // статистика Всего пользователей
        $statUCount =  $this->getService('User')->countAll('blocked != 1');

        $this->view->statUCount   = $statUCount;
        $this->view->statIRCount  = $statIRCount;
        $this->view->tags         = $tags;
        $this->view->classifiers  = $subjectsClassifiers;
    }

    public function indexWithTreeAction()
    {

        $confSubject = null;
        $key = (int)$this->_getParam('key', $this->_getParam('classifier_id', $this->_getParam('item', 0)));
        $programId = (int)$this->_getParam('program_id', 0);
        $type = (int)$this->_getParam('type', 0);

        $keyType = $this->_getParam('keyType', (!$key && $type)
            ? HM_Classifier_ClassifierModel::FILTER_TYPE
            : HM_Classifier_ClassifierModel::FILTER_CLASSIFIER
        );

        if(!$key) {
            $key = HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID;
        }

        if (!$this->_getParam('ordergrid', '')) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

        //подтверждение перехода при подаче заявки на один курс
        $confId = $this->_getParam('confirm_id', 0);
        if ($confId && $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_STUDENT)) {
            $confSubject = $this->getService('Subject')->getOne($this->getService('Subject')->find($confId));

            if ($confSubject) {
                $curDate   = new HM_Date();
                $isCurrent = false;
                if (strtotime($confSubject->begin)) {
                    $startDate = new HM_Date($confSubject->begin);
                    $isCurrent = ($curDate->getTimestamp() >= $startDate->getTimestamp())? true : false;
                }
                if (
                    (in_array($confSubject->period, array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED))) ||
                    ($confSubject->period == HM_Subject_SubjectModel::PERIOD_DATES && $isCurrent) ||
                    ($confSubject->period_restriction_type == HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL && $confSubject->state == HM_Subject_SubjectModel::STATE_ACTUAL)
                ) {
                    $this->view->confirmID = $confId;
                }
            }
        }

        $select = $this->getService('Subject')->getSelect();

        $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(
            array('classifier_id = ?' => $key)
        ));

        $types = $this->getService('Classifier')->getTypes($this->_itemType);

        if (!$type) {
            if($classifier) {
                $type = $classifier->type;
            } elseif (count($types)) {
                $arrayKeys = array_keys($types);
                $type = array_shift($arrayKeys);
            }
        }

        if($type){
            /** @var HM_Classifier_Type_TypeModel $classifierType */
            $classifierType = $this->getService('ClassifierType')->find($type)->current();
        }

        $select->from(array('s'   => 'subjects'), array(
            'subid'               => 's.subid',
            'name'                => 's.name',
            'reg_type'            => 's.reg_type',
            'claimant_process_id' => 's.claimant_process_id',
            'classLeft'           => new Zend_Db_Expr('MIN(class.lft)'),
            'classes'             => new Zend_Db_Expr("''"),
            'student'             => 'st.SID',
            'claimant'            => 'cl.SID',
            'limit_reached'       => new Zend_Db_Expr(
                "
                CASE 
                    WHEN s.plan_users=0 THEN 0
                    ELSE 
                        CASE WHEN (s.plan_users - COUNT(sts.MID)- COUNT(cls.MID)) > 0 THEN 0 ELSE 1 END 
                END"
            )))
            ->joinLeft(array('sts' => 'Students'),
                'sts.CID=s.subid',
                array())
            ->joinLeft(array('cls' => 'claimants'),
                's.subid = cls.CID AND cls.status = ' . HM_Role_ClaimantModel::STATUS_NEW,
                array())
            ->where($this->quoteInto(
                array(
                    's.period IN (?) OR ',
                    's.period_restriction_type = ? OR ',
                    '(s.period_restriction_type = ?',' AND (s.state = ? ',' OR s.state = ? OR s.state is null) ) OR ',
                    '(s.period = ? AND ',
                    's.end > ?)',
                ),
                array(
                    array(HM_Subject_SubjectModel::PERIOD_FREE, HM_Subject_SubjectModel::PERIOD_FIXED),
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                    HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                    HM_Subject_SubjectModel::STATE_ACTUAL,
                    HM_Subject_SubjectModel::STATE_PENDING,
                    HM_Subject_SubjectModel::PERIOD_DATES,
                    $this->getService('Subject')->getDateTime()
                )
            ))
        ->where('s.reg_type = ?', HM_Subject_SubjectModel::REGTYPE_SELF_ASSIGN)
        ->order('classLeft ASC')
        ->group(array('s.subid', 's.name', 's.reg_type', 's.claimant_process_id','st.SID', 'cl.SID', 's.plan_users'));

        if (HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID == $key && $keyType == HM_Classifier_ClassifierModel::FILTER_CLASSIFIER) {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('c.classifier_id IS NULL');

        } elseif ($key && $keyType == HM_Classifier_ClassifierModel::FILTER_TYPE) {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id', array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('class.type = ' . ($key > 0 ? $key : $type));

        } elseif ($key && ($classifier->type == HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS)) {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('s.direction_id = ?', $key);

        } elseif ($key && $keyType == HM_Classifier_ClassifierModel::FILTER_CLASSIFIER) {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('class.classifier_id = ' . $key);

        } elseif ($key && $classifier->level >= 0) {

            $classifier = $this->getService('Classifier')->getOne($this->getService('Classifier')->fetchAll(array('classifier_id = ?' => $key)));
            $select
                ->joinInner(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = '. HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinInner(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array())
                ->where('class.lft >= ?', $classifier->lft)
                ->where('class.rgt <= ?', $classifier->rgt);

        } else {
            $select
                ->joinLeft(array('c' => 'classifiers_links'), 's.subid = c.item_id AND c.type = '. HM_Classifier_Link_LinkModel::TYPE_SUBJECT, array())
                ->joinLeft(array('class' => 'classifiers'), 'c.classifier_id = class.classifier_id', array());
        }

        if ($programId > 0) {
            $select->joinInner(array('prog' => 'programm_events'), 'prog.item_id = s.subid AND prog.isElective = 1 AND prog.type = ' . HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT . ' AND prog.programm_id = ' . $programId, array());
        }

        //if ($this->getService('User')->getCurrentUserId()) {
        // при таком условии гость вообще никогда не увидит
            $userId = (int)$this->getService('User')->getCurrentUserId();
            $select->joinLeft(array('st' => 'Students'), 's.subid = st.CID AND st.MID = ' . $userId, array())
                   /*->where('st.CID IS NULL')*/;
            $select->joinLeft(array('cl' => 'claimants'), 's.subid = cl.CID AND cl.status = ' . HM_Role_ClaimantModel::STATUS_NEW . ' AND cl.MID = ' . $userId, array())
                   /*->where('cl.CID IS NULL')*/;
        //}

        $grid = $this->getGrid($select, array(
            'subid' => array('hidden' => true),
            'classLeft' => array('hidden' => true),
            'limit_reached' => array('hidden' => true),
            'classes' => array('title' => _('Классификация')),
            'name' => array(
                'title' => _('Название'),
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'subject_id' => ''), null, true) . '{{subid}}', _('Карточка учебного курса')) . '{{name}}'
            ),
            'reg_type' => array('hidden' => true),
            'claimant_process_id' => array(
                'title' => _('Согласование заявок'),
                'callback' => array('function' => array($this, 'updateClaimProcType'), 'params' => array('{{claimant_process_id}}'))
            ),
                'student' => array('hidden' => true),
                'claimant' => array('hidden' => true)
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
                    'params' => array('{{subid}}', $select, $type)
                )
            )
        );

        $grid->updateColumn('name',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{name}}', '{{subid}}', '{{student}}', '{{student}}', '{{limit_reached}}')
                )
            )
        );

//        if ($this->getService('Option')->getOption('regAllow') !== '0') {
        $grid->addAction(array(
            'module' => 'user',
            'controller' => 'reg',
            'action' => 'subject'
        ),
            array('subid'),
            _('Подать заявку'),
            $this->getService('User')->getCurrentUserId() ? _('Данное действие может быть необратимым. Вы действительно хотите продолжить?') : null
        );

        $grid->addMassAction(
            array(
                'module' => 'user',
                'controller' => 'reg',
                'action' => 'subjects',
                'status' => array('status' => isset($confSubject->state) ? $confSubject->state : null)
            ),
            _('Подать заявку'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $grid->setActionsCallback(
            array('function' => array($this,'updateActions'),
                'params'   => array('{{student}}', '{{limit_reached}}')
            )
        );

        $tree = $this->getService('Classifier')->getTreeContent($this->_itemType, 0, $type, false, $key);
        if (!$this->isAjaxRequest()) {
            $classifierName = $classifier ? $classifier->getValue('name') : $classifierType->getValue('name');
            $tree = array(
                0 => array(
                    'title'    => $classifierName,
                    'count'    => 0,
                    'key'      => $classifierType->getValue('type_id'),
                    'keyType'  => HM_Classifier_ClassifierModel::FILTER_TYPE,
                    'isLazy'   => true,
                    'isFolder' => true,
                    'expand'   => true
                ),
                1 => $tree
            );
            $this->view->tree = $tree;

//            $grid->autoGetHeaderActions = false;

            $url = array(
                'module'        => 'subject',
                'controller'    => 'catalog',
                'action'        => 'index-with-tree',
                'classifier_id' => $key
            );

            $gridUrl = array_merge($url, array(
                'gridmod' => 'ajax',
                'treeajax' => 'true',
            ));

            /**
             * TODO запоминание выбранного классификатора по аналогии с
             * @see Orgstructure_ListController::indexAction()
             */
            $rubricatorValue = !empty($classifier)
                ? $this->getService('Classifier')->classifierToFrontendData(
                    $classifier,
                    $classifier->classifier_id
                )
                : null;

            $gridUrl = $this->view->url($gridUrl, null, true);
            $rubricatorUrl = $this->view->url($url, null, true);

            $grid->headerActionsBeforeHtml = $this->view->vueRubricatorGridButton(
                _('Классификация'), // $classifierName
                $rubricatorValue,
                [ // rubricator props
                    'itemsData' => $tree,
                    'gridId' => $grid->getGridId(),
                    'gridUrl' => $gridUrl,
                    'url' => $rubricatorUrl,
                ]
            );
        }

        $this->view->types = $types;
        $this->view->type = $type;
        $this->view->tree = $tree;

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
    }

    public function updateActions($isStudent, $limitreached, $actions)
    {
        if ($isStudent) {
            $msg = _('уже назначен');
            return "<span class='grid-row-actions-msg'>{$msg}</span>";
        }
        return $actions;
    }

    private function addSearchSidebar()
    {
        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');
        $classifiersGroups = $classifierService->getSubjectsClassifiers();
        $classifiersResults = [];
        $checkedClassifiersIds = HM_Search_FilterState::getValue(
            HM_Subject_SubjectModel::SUBJECT_CATALOG_FILTER_NAMESPACE,
            HM_Search_FilterState::CLASSIFIERS_FILTER
        );
        $searchQuery = HM_Search_FilterState::getValue(
            HM_Subject_SubjectModel::SUBJECT_CATALOG_FILTER_NAMESPACE,
            HM_Search_FilterState::QUERY_FILTER
        );

        $allClassifiers = [];
        foreach ($classifiersGroups as $classifiersGroup) {
            $allClassifiers = array_merge($allClassifiers, $classifiersGroup['items']->asArrayOfObjects());
        }
        $classifiersCount = $classifierService
            ->getElementCount(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $allClassifiers);

        if (is_array($classifiersGroups)) {
            foreach ($classifiersGroups as $classifierGroupName => $classifiers) {

                $resultItemsBag = [];
                $subjectsInGroupCount = 0;

                foreach ($classifiers['items'] as $classifierKey => $classifier) {

                    $subjectsInClassifierCount =
                        ($classifier->classifier_id && isset($classifiersCount[$classifier->classifier_id])) ?
                        (int) $classifiersCount[$classifier->classifier_id] :
                        0;
                    $subjectsInGroupCount += $subjectsInClassifierCount;

                    $classifierUrl = Zend_Registry::get('view')->url([
                        'module' => 'subject',
                        'controller' => 'catalog',
                        'action' => 'index-with-tree',
                        'classifier_id' => $classifier->classifier_id,
                    ], null, true);

                    if ($subjectsInClassifierCount) {
                        $resultItemsBag[] = [
                            "id" => $classifier->classifier_id,
                            "name" => $classifier->name,
                            "url" => $classifierUrl,
                            "checked" => in_array($classifier->classifier_id, $checkedClassifiersIds),
                        ];
                    }
                }

                if ($subjectsInGroupCount) {
                    $classifiersResults[] = [
                        'title' => $classifiers['title'],
                        'items' => $resultItemsBag
                    ];
                }
            }
        }

        $withoutClassifierUrl = Zend_Registry::get('view')->url([
            'module' => 'subject',
            'controller' => 'catalog',
            'action' => 'index-with-tree',
            'classifier_id' => HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID,
        ], null, true);

        $classifiersResults[] = [
            "title" => 'Без классификатора',
            "items" => [[
                "id" => HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID,
                "name" => _('Без классификатора'),
                "url" => $withoutClassifierUrl,
                "checked" => in_array(HM_Classifier_ClassifierModel::NO_CLASSIFIER_ID, $checkedClassifiersIds),
            ]],
        ];

        $subjectsSearch = $this->view->partial("partials/search.tpl", [
            "classifiers" => json_encode($classifiersResults),
            "searchQuery" => htmlspecialchars($searchQuery),
        ]);
        $this->view->addSidebar('search', ['content' => $subjectsSearch]);
    }

    public function updateClaimProcType($claimProcId)
    {
        $titles = HM_Subject_SubjectModel::getClaimantProcessTitles();
        return $titles[$claimProcId];
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


    public function updateClassifiers($subjectId,Zend_Db_Select $select, $type)
    {

        if ($this->_classifiers == Null) {

            $query = $select->query();
            $fetch = $query->fetchAll();
            $ids = array();
            foreach($fetch as $value) {
                $ids[] = $value['subid'];
            }
            $values = $select->getAdapter()->quote($ids);
            $temp = $this->getService('ClassifierLink')->fetchAllDependenceJoinInner('Classifier', 'self.item_id IN (' . $values . ') AND self.type = ' . HM_Classifier_Link_LinkModel::TYPE_SUBJECT . ' AND Classifier.type = ' . (int) $type);
            $this->_classifiers = $temp;

        }

        $ret = array();
        foreach($this->_classifiers as $val) {
            if ($val->item_id == $subjectId) {
                foreach($val->classifiers as $class) {
                    $ret[] = $class->name;
                }
            }

        }

        return implode(',', $ret);
    }


    public function updateName($name, $subjectId, $isStudent, $isclaimant, $limitReached)
    {
        if (
            in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_GUEST, HM_Role_Abstract_RoleModel::ROLE_USER, HM_Role_Abstract_RoleModel::ROLE_STUDENT)) ||
            $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)
        ) {
            $type = $this->_getParam('type', null);
            $item = $this->_getParam('item', null);
            $classifierId = $this->_getParam('classifier_id', null);

            $marker = '';
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_STUDENT)) {
                if ($isStudent) {
                    $marker = HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Вы уже зачислены на этот курс'), 1);
                } elseif ($isclaimant) {
                    $marker = HM_View_Helper_Footnote::marker(2);
                    $this->view->footnote(_('Вы уже подали заявку на этот курс'), 2);
                } elseif ($limitReached) {
                    $marker = HM_View_Helper_Footnote::marker(3);
                    $this->view->footnote(_('На курсе достигнуто максимальное количество слушателей'), 3);
                }
                /*
                if (empty($this->subjectsCache)) {
                    $this->subjectsCache = Zend_Registry::get('serviceContainer')->getService('User')->getSubjects()->getList('subid');
                }
                if (in_array($subjectId, $this->subjectsCache)) {
                    $marker = HM_View_Helper_Footnote::marker(1);
                    $this->view->footnote(_('Вы уже зачислены на этот курс'), 1);
                }*/
            }

            return '<a href="' . $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'description', 'subject_id' => $subjectId, 'item' => $item, 'type' => $type, 'classifier_id' => $classifierId)) . '">' . $name . '</a>' /* . $marker */;
        }else{
            return $name;
        }
    }


}
