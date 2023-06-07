<?php
class Subject_FulltimeController extends HM_Controller_Action {

	use HM_Controller_Action_Trait_Grid;

    const ACTION_INSERT    = 1;
    const ACTION_UPDATE    = 2;
    const ACTION_DELETE    = 3;
    const ACTION_DELETE_BY = 4;

    protected $teachersCache = array();
    protected $citiesCache   = array();

    protected $_baseType;
    protected $_subjectId = 0;
    protected $_subject = null;

    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    protected $baseId      = 0;
    protected $providerId  = 0;
    protected $sessionId   = 0;

    protected $form;

    public function init()
    {
        //поля provider_id и base_id могут приходить через форму создания курса/сессии
        //чтобы не было путаницы - убираем их
        $requestSources = $this->getRequest()->getParamSources();
        $this->getRequest()->setParamSources(array());
        $getParams = $this->getRequest()->getParams();
        $this->baseId     = isset($getParams['base_id'])     ?  $getParams['base_id']     : ($getParams['subid'] ? $getParams['subid'] : 0);
        $this->providerId = isset($getParams['provider_id']) ?  $getParams['provider_id'] : 0;
        $this->sessionId  = isset($getParams['session_id'])  ?  $getParams['session_id']  : 0;
        $this->getRequest()->setParamSources($requestSources);

        $baseType  = $this->_getParam('base', false);
        $subjectId = (int) $this->_getParam('subject_id', $this->_getParam('subid', 0));
        $category  = 0;

        $this->_defaultService = $this->getService('TcSubject');

        if ($subjectId > 0) {
            $this->_subjectId  = $subjectId;
            $this->_subject    = $this->getOne(
                $this->_defaultService->fetchAllDependence(array('AtCriterion', 'AtCriterionTest'), 'subid='.$subjectId)
            );

            if ($this->_subject) {
                $this->view->subject_id = $subjectId;
                $category = $this->_subject->category;

                if ($baseType === false) {
                    $baseType = $this->_subject->getBaseType();
                }

                $currentRole = $this->getService('User')->getCurrentUserRole();
                $actionName = $this->getRequest()->getActionName();

                if (in_array($actionName, array ('delete', 'edit'))) {
                    if ($this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
                        parent::init();
                        $this->_redirectToIndex();
                    }

                    if ($this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
                        if ($this->_subject->created_by != $this->getService('User')->getCurrentUserId()) {
                            parent::init();
                            $this->_redirectToIndex();
                        }
                    }
                }
            }
        }

        $form = new HM_Form_Fulltime();
        $this->addModifier($form, $category, $baseType);
        $this->form = $form;

        $this->_baseType = $baseType;

        if ($this->providerId) {
            HM_Subject_View_ExtendedView::init($this);
        }
        parent::init();
    }

    public function getSubjectId()
    {
        return $this->_subjectId;
    }

    public function isBaseCase()
    {
        return $this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION && $this->getBaseId() > 0;
    }
    public function getBaseId()
    {
        return $this->baseId;
    }

    public function isProviderCase()
    {
        return !$this->isBaseCase() && $this->getProviderId() > 0;
    }

    public function isSessionCase()
    {
        return ($this->sessionId > 0);
    }

    public function isSessionAdditionalCourseCase()
    {
        return ($this->isSessionCase() && $this->_getParam('is_new_additional_course', 0));
    }

    public function getProviderId()
    {
        return $this->providerId;
    }

    protected function addModifier($form, $category, $baseType)
    {
        switch ($category) {
            case HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY:
                $form->addModifier(new HM_Form_Modifier_FulltimeNecessary());
                break;

            case HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_ADDITION:
                $form->addModifier(new HM_Form_Modifier_FulltimeAddition());
                break;

            case HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_CORPORATE:
                $form->addModifier(new HM_Form_Modifier_FulltimeCorporate());
                break;
            default:
                $form->addModifier(new HM_Form_Modifier_FulltimeNew());
                break;
        }

        if ($baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $form->addModifier(new HM_Form_Modifier_FulltimeSession());
        } else {
            $form->addModifier(new HM_Form_Modifier_FulltimeBase());
        }
    }

    protected function _redirectToIndex($baseType = false)
    {
        if ($this->isSessionAdditionalCourseCase()) {
            $this->_redirector->gotoSimple('index', 'new-subjects', 'session', array(
                'session_id' => $this->sessionId
            ));
        }

        $url = array(
            'action'     => 'index',
            'controller' => 'fulltime',
            'module'     => 'subject',
            'baseUrl'    => 'tc',

            'base'       => $baseType !== false ? $baseType
                    :  ($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION ? HM_Tc_Subject_SubjectModel::BASETYPE_SESSION : NULL),

            'provider_id' => $this->isProviderCase() ? $this->getProviderId() : null,
            'base_id'     => $this->isBaseCase()     ? $this->getBaseId()     : null,

        );

        $this->_redirector->gotoUrl($this->view->url($url, null, true), array('prependBase' => false));
    }

    public function indexAction()
    {
        $gridId = 'gridFulltime';

        $restricredEdit =
            (($this->_baseType != HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) &&
            $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) ? 1 : 0;
        $onlyMy = null;

        if ($restricredEdit) {
            $default = new Zend_Session_Namespace('default');
            $onlyMy  = $this->_getParam('all', isset($default->grid['subject-fulltime-index'][$gridId]['all'])
                ? $default->grid['subject-fulltime-index'][$gridId]['all']
                : null);
        }
        
        $sorting = $this->_request->getParam("order{$gridId}");
        if ($sorting == ""){
            $this->_request->setParam("order{$gridId}", 'subject_name_ASC');
        }
        

        $this->_request->setParam("slaveOrder{$gridId}", 'subid ASC');

        $isSession = $this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION;

        $select = $this->getService('TcSubject')->getSelect();

        $citiesSelect = clone $select;
        $citiesSelect
            ->from(
                array('cl' => 'classifiers_links'),
                array('cl.classifier_id', 'cl.item_id', 'cl.type'))
            ->joinInner(
                array('c' => 'classifiers'),
                'cl.classifier_id = c.classifier_id AND c.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                array());

        $select->from(array('s' => 'subjects'),
            array(
                'created_by'   => 's.created_by',
                'subid'        => 's.subid',
                'subject_name' => 's.name',
                'begin_date'   => 's.begin',
                'end_date'     => 's.end',
                'base_type'    => 's.base',
                'tcprovider'   => 'pr.provider_id',
                'provider'     => 'pr.name',
                'status'       => 's.status',
                'category'     => 's.category',
                'format'       => 's.format',
                'competence'   => new Zend_Db_Expr("CASE WHEN (s.criterion_type = " . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION. ") THEN atc.name ELSE CASE WHEN (s.criterion_type = " .HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST. ") THEN atct.name ELSE '' END END"),
                'city'         => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT cl.classifier_id)'),
                'price'        => 's.price',
                'tags'         => 's.subid',
                'teachers'     => new Zend_Db_Expr('GROUP_CONCAT(tpts.teacher_id)'),
                'students'     => new Zend_Db_Expr('COUNT(DISTINCT st.MID)'),
                'rating'       => 's.rating'
                )
            )
            ->joinLeft(
                array('cl' => $citiesSelect),
                'cl.item_id = s.subid AND cl.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                array()
            )
            ->joinLeft(
                array('pr' => 'tc_providers'),
                's.provider_id = pr.provider_id',
                array()
            )
            ->joinLeft(
                array('st' => 'students'),
                's.subid = st.CID',
                array()
            )
            ->joinLeft(
                array('atc' => 'at_criteria'),
                's.criterion_id = atc.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION,
                array()
            )
            ->joinLeft(
                array('atct' => 'at_criteria_test'),
                's.criterion_id = atct.criterion_id AND s.criterion_type=' . HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST,
                array()
            )
            ->joinLeft(
                array('tpts' => 'tc_provider_teachers2subjects'),
                'tpts.subject_id = s.subid',
                array()
            )
            ->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME)
            ->group(array(
                's.subid',
                's.created_by',
                's.name',
                's.begin',
                's.end',
                's.category',
                's.criterion_type',
                'atc.name',
                'atct.name',
                's.begin',
                's.end',
                's.price',
                's.format',
                'pr.provider_id',
                'pr.name',
                's.city',
                's.status',
                's.base',
                's.rating'
            ));

        if ($onlyMy) {
            $select->where('s.created_by='.$this->getService('User')->getCurrentUserId());
        }

        if ($isSession) {
//            $ratingSelect = $this->getService('TcSubject')->getSelect();
//            $ratingSelect->from(
//                array('gr' => 'graduated'),
//                array(
//                    'subid'     => 'gr.CID',
//                    'rating'    => new Zend_Db_Expr('COUNT(gr.mid) * AVG(gr.effectivity) * AVG(sv.value)'),
//                    'graduated' => new Zend_Db_Expr('COUNT(gr.mid)'),
//                ))
//                ->joinLeft(
//                    array('f' => 'tc_feedbacks'),
//                    'gr.CID = f.subject_id AND gr.MID=f.user_id',
//                    array())
//                ->joinLeft(
//                    array('sv' => 'scale_values'),
//                    'sv.value_id = f.mark',
//                    array())
//                ->group('gr.CID')
//            ;
//            $select->joinLeft(array('rt' => $ratingSelect), 'rt.subid = s.subid', array(
////                'graduated' => 'rt.graduated',
//                'rating'    => 'rt.rating',
//            ));
            $select->where('s.base = ?', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
        } else {
            $select->where('s.base != ? OR s.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
//            $select->joinLeft(
//                array('rt' => 'subjects_fulltime_rating'),
//                'rt.subid=s.subid',
//                array(
////                    'graduated' => 'rt.graduated',
//                    'rating'    => 'rt.rating',
//                ));
        }

        $urlSubject  = array('module' => 'subject',  'controller' => 'index', 'action' => 'card', 'baseUrl' => '', 'subject_id' => '{{subid}}');
        $urlProvider = array('module' => 'provider', 'controller' => 'list', 'action' => 'view', 'provider_id' => '{{tcprovider}}');
        $cardName    = $isSession ? _('Карточка внешней сессии') : _('Карточка внешнего курса');

        $fieldsDisplay = array(
            'subid'         => array('hidden' => true),
            'created_by'    => array('hidden' => true),
            'subject_name'  => array(
                'title' => _('Название'),
//                'decorator' => $this->view->cardLink($this->view->url(array('action' => 'card', 'subject_id' => '')) . '{{subid}}', $cardName) . ' <a href="' . $this->view->url($urlSubject, null, true, false) . '">{{subject_name}}</a>'
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'baseUrl' => '', 'subject_id' => '')) . '{{subid}}', $cardName) . ' <a href="' . $this->view->url($urlSubject, null, true, false) . '">{{subject_name}}</a>'
            ),
            'begin_date'        => $isSession ? array('title' => _('Дата начала')) : array('hidden' => true),
            'end_date'        => $isSession ? array('title' => _('Дата окончания')) : array('hidden' => true),
            'status'        => array('hidden' => true),//array('title' => _(' Утвержден')),
            'base_type'     => array('hidden' => true),
            'tcprovider'    => array('hidden' => true),
            'provider'      => array(
                'title' => _('Провайдер'),
                'decorator' => $this->view->cardLink($this->view->url(array('module' => 'provider', 'controller' => 'list','action' => 'card', 'provider_id' => '')) . '{{tcprovider}}', _('Карточка провайдера')) . ' <a href="' . $this->view->url($urlProvider, null, true, false) . '">{{provider}}</a>'
            ),
            'city'          => array('hidden' => true),
//            array(
//                'title' => _('Город'),
//                'callback' => array(
//                    'function'=> array($this, 'citiesCache'),
//                    'params'=> array('{{city}}', $select))
//            ),
            'category'      => $isSession ? array('hidden' => true) : array('title' => _('Категория обучения')),
            'format'        => $isSession ? array('hidden' => true) : array('title' => _('Формат')),
            'competence'    => $isSession ? array('hidden' => true) : array('title' => _('Компетенция / квалификация')),
            'price'         => array(
                'title'    => _('Стоимость'),
                'style'    => "text-align:right",
                'callback' => array(
                    'function' => 'number_format',
                    'params'   => array('{{price}}', 0, '.', ' '))
            ),
            'tags'          => array('hidden' => true),
//                ($isSession)
//                                ? array('hidden' => true)
//                                : array('title' => _('Метки')),
            'teachers'      => array('hidden' => true), //array('title' => _('Тьюторы')),
            'students'      => $isSession
                    ? array('title' => _('Количество слушателей'))
                    : array('hidden' => true),
            'rating' => array(
                'title' => _('Рейтинг'),
                'callback' => array(
                    'function' => array($this, 'updateRating'),
                    'params' => array('{{rating}}', $select)
                )
            ),

        );

        if ($this->isProviderCase()) {
            $fieldsDisplay['provider'] = array('hidden' => true);
            $select->where('s.provider_id=' . $this->providerId);
        }
        if ($this->isBaseCase()) {
            $fieldsDisplay['provider'] = array('hidden' => true);
            $select->where('s.base_id=' . $this->baseId);
        }

//        $select->where('s.provider_type = ?', HM_Tc_Provider_ProviderModel::TYPE_PROVIDER);

        $grid = $this->getGrid($select,
            $fieldsDisplay,
            array(
                'subid'         => true,
                'subject_name'  => true,
                'provider'      => true,
                'city'          => array('callback' => array('function' => array($this, 'filterCities'))),
                'category'      => array('values' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeCategories')),
//                'status'        => array('values' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeStatesSimple')),
                'format'        => array('values' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeFormates')),
                'competence'    => true,
                'longtime'      => array('render' => 'Number'),
                'price'         => array('render' => 'Number'),
                'tags'          => array('callback' => array('function' => array($this, 'filterTags'))),
                'graduated'     => array('render' => 'Number'),
                'teachers'      => array('callback' => array('function' => array($this, 'filterTeachers'))),
                'students'      => array('render' => 'Number'),
                'rating'        => array('render' => 'Number'),
               	'begin_date' => array('render' => 'dateSmart'),
                'end_date' => array('render' => 'dateSmart'),
            ),
            $gridId
        );

        if ($restricredEdit) {
            $grid->setGridSwitcher(array(
                array('name' => 'fulltime_all', 'title' => _('все очные курсы'),            'params' => array('all' => 0)),
                array('name' => 'fulltime_my',  'title' => _('очные курсы, созданые мной'), 'params' => array('all' => 1)),
            ));
        }

        $grid->updateColumn('begin_date', array('format' => array('date',
                                                             array('date_format' => HM_Locale_Format::getDateFormat()))));

        $grid->updateColumn('end_date', array('format' => array('date',
                                                           array('date_format' => HM_Locale_Format::getDateFormat()))));

        $grid->updateColumn('tags', array(
            'callback' => array(
                'function'=> array($this, 'displayTags'),
                'params'=> array('{{tags}}', $this->getService('TagRef')->getSubjectType())
            )
        ));

        $grid->updateColumn('category', array(
                'callback' => array(
                    'function' => array($this, 'updateVariants'),
                    'params' => array('{{category}}', 'FulltimeCategories')
                )
            )
        );
/*
        $grid->updateColumn('status', array(
                'callback' => array(
                    'function' => array($this, 'updateVariants'),
                    'params' => array('{{status}}', 'FulltimeStatesSimple')
                )
            )
        );
*/
        $grid->updateColumn('format', array(
                'callback' => array(
                    'function' => array($this, 'updateVariants'),
                    'params' => array('{{format}}', 'FulltimeFormates')
                )
            )
        );

        $grid->updateColumn('teachers', array(
                'callback' => array(
                    'function' => array($this, 'teachersCache'),
                    'params' => array('{{teachers}}')
                )
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $grid->addAction(array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'edit',
                    'base_id'    => null,
                    'subject_id' => null,
                    'base'       => null
                ),
                array('subid'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'delete'
                ),
                array('subid'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            /*
            $grid->addAction(
                array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'sync',
                    'mode' => 'content',
                    'base_id'    => null,
                    'subject_id' => null,
                    'base'       => null
                ),
                array(
                    'subid' => 'subject_id'
                ),
                _('Перенести контент на внешний сервер')
            );

            $grid->addAction(
                array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'sync',
                    'mode' => 'assigns',
                    'base_id'    => null,
                    'subject_id' => null,
                    'base'       => null
                ),
                array(
                    'subid' => 'subject_id'
                ),
                _('Перенести назначения и настройки на внешний сервер')
            );
            */

            // if basetype вынесен в updateActions
            $grid->addAction(array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'new',
                    'nobase'     => null,
                    'base'       => HM_Tc_Subject_SubjectModel::BASETYPE_SESSION
                ),
                array('subid'),
                _('Создать сессию внешнего курса')
            );

            $grid->addMassAction(array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'delete-by'
                ),
                _('Удалить'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

            if (!$isSession) {
                $grid->addMassAction(array(
                    'module'     => 'subject',
                    'controller' => 'fulltime',
                    'action'     => 'approve'
                ),
                    _('Утвердить'),
                    _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
                );
            }

            $grid->setActionsCallback(
                array(
                    'function' => array($this, 'updateActions'),
                    'params'   => array('{{created_by}}', '{{base_type}}', '{{status}}')
                )
            );
        }

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;
        $this->view->baseType        = $this->_baseType;

    }

    public function syncAction()
    {
        $error = $this->getService('Extsync')->sync($this->_getParam('mode', false), $this->_subject->subid); 
        $this->_flashMessenger->addMessage($error ? (_('Ошибка передачи данных: ').$error) : _('Передача данных завершена'));
        $this->_redirectToIndex();
    }


    public function setDefaults(Zend_Form $form)
    {
        if ($this->_subject){
            $values = $this->_subject->getValues();
            $values['tags'] = $this->getService('Tag')->getTags($this->_subjectId, $this->getService('TagRef')->getSubjectType());

            $cities = $this->getService('Classifier')->fetchAllDependenceJoinInner('ClassifierLink',
                $this->quoteInto(array('ClassifierLink.type = ? ',' AND ClassifierLink.item_id = ?',' AND self.type = ?'),
                    array(HM_Classifier_Link_LinkModel::TYPE_SUBJECT, $this->_subjectId, HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES)
                )
            )->getList('classifier_id', 'name');
            $values['city'] =  $cities ? $cities : '';

            $values['files']     = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $this->_subjectId);
            $values['criterion'] = $this->setCriterionValue($values['criterion_type'], $values['criterion_id']);
            if ($values['criterion']) {
                $values['criterion_text'] = $values['criterion'][$values['criterion_type'] . '_' . $values['criterion_id']];
            }

            $values['begin'] = $this->_subject->date($values['begin']);
            $values['end']   = $this->_subject->date($values['end']);

            $form->getElement('icon')->setOptions(array('subject' => $this->_subject));
            $values['icon'] = $this->_subject->getIcon();

            $form->populate($values);
        }
    }

    public function editAction()
    {
        if ($this->isBaseCase()) {
            $this->view->setHeader(_('Редактирование сессии'));
        }

        $form = $this->form;
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getParams();
            if (($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) &&
                ($this->_subject->category == HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY)) {
                $post['primary_type'] = $this->_subject->primary_type;
            }

            if ($form->isValid($post)) {
                $this->update($form);

                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_UPDATE));

                $this->_redirectToIndex();
            } else {
                //ради этого блока переопределяем метод
                //круд теряет значения "сложнозаполняемых" полей
                $post['provider_id'] = $this->_subject->provider_id;
                $post['category']    = $this->_subject->category;
                //Файлы просто откатываем: меняют их редко, а процедура сопоставления списков будет слишком сложная
                $post['files']       = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $this->_subjectId);

                $post['tags'] = $this->getService('Tag')->convertAllToStrings($post['tags']);
                $post['city'] = empty($post['city']) ? '' : $this->getService('Classifier')->fetchAll($this->getService('Classifier')->quoteInto('classifier_id in (?)', array_values($post['city'])))->getList('classifier_id', 'name');

                if (!empty($post['criterion'])) {
                    $criterion = explode('_', $post['criterion'][0]);
                    $post['criterion'] = $this->setCriterionValue($criterion[0], $criterion[1]);
                }

                $form->populate($post);
            }
        } else {
            $this->setDefaults($form);
        }
        $this->view->form = $form;
    }

    public function newAction()
    {
        $form    = $this->form;
        $request = $this->getRequest();

        if ($this->isBaseCase()) {
            $this->view->setHeader(_('Создание сессии внешнего курса'));
        }

        if($this->isProviderCase()) {
            $elem = $form->getElement('provider_id');
            $elem->setValue($this->getProviderId());
            $elem->setOptions(array('disabled' => true));
        }

        if ($request->isPost()) {
            $post = $request->getParams();
            if (($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) &&
                ($this->_subject->category == HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_NECESSARY)) {
                $post['primary_type'] = $this->_subject->primary_type;
            }

            if ($form->isValid($post)) {
                $result = $this->create($form);
                if($result != NULL && $result !== TRUE){
                    $this->_flashMessenger->addMessage(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => $this->_getErrorMessage($result)));
                    $this->_redirectToIndex();
                }else{
                    $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_INSERT));
                    $this->_redirectToIndex(null);
                }
            } else {
                //ради этого блока переопределяем метод
                //круд теряет значения "сложнозаполняемых" полей
                if (!empty($post['criterion'])) {
                    $criterion = explode('_', $post['criterion'][0]);
                    $post['criterion'] = $this->setCriterionValue($criterion[0], $criterion[1]);
                }
                $form->populate($post);
            }
        } elseif ($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            if($this->_subject->status == 1){
                $this->setDefaults($form);
                //$baseId = $form->getElement('base_id');
                //$baseId->setValue($this->_subjectId);

                $form->removeElement('files');
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => 'Сессию нельзя создать на неопубликованном курсе.'
                ));

                $this->_redirectToIndex(null);
            }

        }

        if (($dateBegin = $form->getElement('begin')) && ($dateEnd = $form->getElement('end')) ) {
            $date1 = new DateTime();
            $date2 = clone $date1;
            $date2->add(new DateInterval('P10D'));
            $dateBegin->setValue($date1->format('d.m.Y'));
            $dateEnd->setValue($date2->format('d.m.Y'));
        }
        $this->view->form = $form;
    }

    public function create(Zend_Form $form)
    {
        $data = $form->getNonClassifierValues();
        $data['provider_type']    = HM_Tc_Provider_ProviderModel::TYPE_PROVIDER;
        $data['type']     = HM_Tc_Subject_SubjectModel::TYPE_FULLTIME;
        unset($data['subid']);
        unset($data['icon']);


        if ($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $data['base']           = HM_Tc_Subject_SubjectModel::BASETYPE_SESSION;
            $data['base_id']        = $this->_subject->subid;

            $data['category']       = $this->_subject->category;
            $data['provider_id']    = $this->_subject->provider_id;
            $data['criterion_type'] = $this->_subject->criterion_type;
            $data['criterion_id']   = $this->_subject->criterion_id;
            unset($data['criterion']);
            unset($data['criterion_text']);

            unset($data['files']);
            $subject = $this->_defaultService->insert($data);

            //сохраняем метки
            $tags = array_unique($form->getParam('tags', array()));
            $this->getService('Tag')->updateTags($tags, $subject->subid, $this->getService('TagRef')->getSubjectType());
            unset($data['tags']);

            //сохраняем города
            $classifiers = $form->getClassifierValues();
            $classifiers = array_merge($classifiers, $data['city']);

            //сохраняем города и классификаторы
            $this->getService('TcSubject')->linkClassifiers($subject->subid, $classifiers);

            try {
                $this->getService('TcSubject')->copyElements($data['base_id'], $subject->subid);
            } catch (HM_Exception $e) {
                // что-то не скопировалось..(
            }
        } else {
            $data['criterion_type'] = 0;
            $data['criterion_id']   = 0;
            if (!empty($data['criterion'])) {
                $criterion = explode('_', $data['criterion'][0]);
                $data['criterion_type'] = $criterion[0];
                $data['criterion_id']   = $criterion[1];
            }
            unset($data['criterion']);
            unset($data['criterion_text']);
            $data['feedback'] = 1;
            $data['base']     = HM_Tc_Subject_SubjectModel::BASETYPE_BASE;
            $data['period'] = HM_Subject_SubjectModel::PERIOD_FREE;
            $data['longtime'] = 0;

            if (!$data['begin']) {
                unset($data['begin']);
            }
            if (!$data['end']) {
                unset($data['end']);
            }

            $subject = $this->_defaultService->insert($data);

            $classifiers = $form->getClassifierValues();
            $classifiers = array_merge($classifiers, $data['city']);

            //сохраняем города и классификаторы
            $this->getService('TcSubject')->linkClassifiers($subject->subid, $classifiers);

            $this->getService('TcSubject')->updateIcon($subject->subid, $form->getElement('icon'));
        }
    }

    public function update(Zend_Form $form)
    {
        $data = $form->getNonClassifierValues();
        unset($data['type']);
        unset($data['tags']);
        unset($data['category']);
        unset($data['provider_id']);
        $data['provider_type'] = HM_Tc_Provider_ProviderModel::TYPE_PROVIDER;

        unset($data['files']);
        unset($data['icon']);

        if (empty($data['begin'])) {
            unset($data['begin']);
        }
        if (empty($data['end'])) {
            unset($data['end']);
        }

        if ($this->_baseType != HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $data['criterion_type'] = 0;
            $data['criterion_id']   = 0;
            if (!empty($data['criterion'])) {
                $criterion = explode('_', $data['criterion'][0]);
                $data['criterion_type'] = $criterion[0];
                $data['criterion_id']   = $criterion[1];
            }
        }
        unset($data['criterion']);
        unset($data['criterion_text']);

        $classifiers = $form->getClassifierValues();
        $classifiers = array_merge($classifiers, $data['city']);
        unset($data['city']);

        //сохраняем курс
        $res = $this->_defaultService->update($data);

        //сохраняем города и классификаторы
        $this->getService('TcSubject')->linkClassifiers($data['subid'], $classifiers);
        $this->getService('TcSubject')->updateIcon($data['subid'], $form->getElement('icon'));


        //сохраняем метки
        $tags = array_unique($form->getParam('tags', array()));
        $this->getService('Tag')->updateTags($tags, $this->_subjectId, $this->getService('TagRef')->getSubjectType());

        // нужно физически удалить файлы, которые удалили из формы нажатием на "х"
        $populatedFiles = $this->getService('Files')->getItemFiles(HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $this->_subjectId);
        $deletedFiles   = $form->files->updatePopulated($populatedFiles);
        if(count($deletedFiles))
        {
            $this->getService('Files')->deleteBy(array('file_id IN (?)' => array_keys($deletedFiles)));
        }

        //загружаем новые файлы
        if($form->files->isUploaded() && $form->files->receive() && $form->files->isReceived()){
            $files = $form->files->getFileName();
            if ($files && !is_array($files)) {
                $files = array($files);
            }

            foreach($files as $file){
                $fileInfo = pathinfo($file);
                $this->getService('Files')->addFile($file, $fileInfo['basename'], HM_Files_FilesModel::ITEM_TYPE_SUBJECT, $this->_subjectId);
            }
        };
    }


    public function deleteAction()
    {
        $subId = $this->_getParam('subid', 0);
        if ($subId) {
            $this->delete($subId);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }

        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_gridFulltime', '');
        if (!strlen($postMassIds)) {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
        }
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if ($this->getService('Acl')->inheritsRole(
                $this->getService('User')->getCurrentUserRole(),
                array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

                $providers = $this->_defaultService->fetchAll(
                    $this->_defaultService->quoteInto(
                        array('subid in (?)', ' AND created_by=?'),
                        array($ids, $this->getService('User')->getCurrentUserId())));
                $ids = $providers->getList('provider_id');
            }

            if (count($ids)) {
                foreach($ids as $id) {
                    $this->delete($id);
                }
                $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE_BY));
            }
        }
        $this->_redirectToIndex();
    }

    public function approveAction()
    {
        $postMassIds = $this->_getParam('postMassIds_gridFulltime', '');
        if (!strlen($postMassIds)) {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
        }
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                /*if ($this->getService('Acl')->inheritsRole(
                    $this->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

                    $providers = $this->_defaultService->fetchAll(
                        $this->_defaultService->quoteInto(
                            array('subid in (?)', ' AND created_by=?'),
                            array($ids, $this->getService('User')->getCurrentUserId())));
                    $ids = $providers->getList('subid');
                }*/

                $this->_defaultService->updateWhere(array('status'=> HM_Tc_Subject_SubjectModel::FULLTIME_STATUS_PUBLISHED),
                    $this->_defaultService->quoteInto(
                        array('subid in (?)'),
                        array($ids)));


                $this->_flashMessenger->addMessage(_('Внешние курсы успешно утверждены'));
            }
        }
        $this->_redirectToIndex();
    }

    public function delete($id)
    {
        return $this->_defaultService->delete($id);
    }

    public function citiesAction()
    {
        $where    = array(
            'type=?' =>  HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
            'LOWER(name) LIKE ?' => '%' . mb_strtolower(addslashes($this->_getParam('tag'))) . '%'
        );
        $collection = $this->getService('Classifier')->fetchAll($where);

        $res = array();
        foreach($collection as $city) {
            $o = new stdClass();
            $o->key = $city->name;
            $o->value = $city->classifier_id;
            $res [] = $o;
        }

        header('Content-type: application/json; charset=UTF-8');
        exit(HM_Json::encodeErrorSkip($res));
    }

    public function criteriaAction()
    {
        $where    = $this->getService('AtCriterion')->quoteInto('LOWER(name) LIKE ?', '%' . mb_strtolower(addslashes($this->_getParam('tag'))) . '%');

        $select1 = $this->getService('AtCriterion')->getSelect();
        $select1->from(
            'at_criteria',
            array(
                'criterion_id',
                'name',
                'type' => new Zend_Db_Expr(HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION)
            ))
            ->where($where)
            ->where('status=?', HM_At_Criterion_CriterionModel::STATUS_ACTUAL);
        $select2 = $this->getService('AtCriterionTest')->getSelect();
        $select2->from(
            'at_criteria_test',
            array(
                'criterion_id',
                'name',
                'type' => new Zend_Db_Expr(HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST)
            ))
            ->where($where)
            ->where('status=?', HM_At_Criterion_Test_TestModel::STATUS_ACTUAL);

        $subSelect = $this->getService('AtCriterion')->getSelect();
        $subSelect->union(array($select1, $select2), Zend_Db_Select::SQL_UNION);

        $select = $this->getService('AtCriterion')->getSelect();
        $select->from($subSelect, array('criterion_id', 'name', 'type'))->order('name');
        $collection = $subSelect->query()->fetchAll();

        $res = array();
        foreach($collection as $competence) {
            $o = new stdClass();
            $o->key = $competence['name'];
            $o->value = $competence['type'] . '_' . $competence['criterion_id'];
            $res [] = $o;
        }

        header('Content-type: application/json; charset=UTF-8');
        exit(HM_Json::encodeErrorSkip($res));
    }

    public function updateVariants($variantId, $method)
    {
        return HM_Tc_Subject_SubjectModel::getVariant($variantId, $method);
    }

    public function cardAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);
        $this->view->subject = $this->_subject;
    }

    public function ratingAction() {
        $this->_helper->getHelper('layout')->disableLayout();
        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
        $this->getResponse()->setHeader('Content-type', 'text/html; charset=' . Zend_Registry::get('config')->charset);

        if (!$this->_subject) {
            exit;
        }

        $ratingSelect = $this->getService('TcSubject')->getSelect();
        $ratingSelect->from(
            array('gr' => 'graduated'),
            array(
                'gr.CID',
                'graduated'   => new Zend_Db_Expr('COUNT(gr.mid)'),
                'effectivity' => new Zend_Db_Expr('AVG(gr.effectivity)'),
                'feedback'    => new Zend_Db_Expr('AVG(sv.value)'),
            ))
            ->joinLeft(
                array('f' => 'tc_feedbacks'),
                'gr.CID = f.subject_id AND gr.MID=f.user_id',
                array())
            ->joinLeft(
                array('sv' => 'scale_values'),
                'sv.value_id = f.mark',
                array())
            ->group('gr.CID');

        if ($this->_subject->base == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            $ratingSelect
                ->where ('gr.CID='.$this->_subjectId);
            $result = $ratingSelect->query()->fetch();
        } else {
            $select = $this->getService('TcSubject')->getSelect();
            $select->from(
                array('s' => 'subjects'),
                array(
                    'CID' => 's.base_id',
                    'graduated'   => new Zend_Db_Expr('SUM(rt.graduated)'),
                    'effectivity' => new Zend_Db_Expr('AVG(rt.effectivity)'),
                    'feedback'    => new Zend_Db_Expr('AVG(rt.feedback)'),
                ))
                ->joinLeft(
                    array('rt' => $ratingSelect),
                    's.subid=rt.CID',
                    array())
                ->where('s.base_id='.$this->_subjectId)
                ->group('s.base_id');

            $result = $select->query()->fetch();
        }

        $result['rating'] = $result['graduated'] * $result['effectivity'] * $result['feedback'];

        $rating = new stdClass();
        foreach ($result as $key => $value) {
            $rating->$key = round($value, 1);
        }

        $this->view->subject = $rating;
    }

    public function viewAction()
    {
        parent::init();
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getService('Subject')->find($subjectId)->current();
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))){
            $this->view->setExtended(
                array(
                    'subjectName' => 'Subject',
                    'subjectId' => $subjectId,
                    'subjectIdParamName' => 'subject_id',
                    'subjectIdFieldName' => 'subid',
                    'subject' => $subject
                )
            );
        }

        $form = $this->form;
        $this->setDefaults($form);

        $cards    = $form->getFieldsArray();

        if (isset($cards['classifiers'])) {
            $classifiers = $this->getService('Classifier')
                ->fetchAllDependenceJoinInner('ClassifierLink', $this->quoteInto(
                    array('ClassifierLink.item_id = ?', ' AND ClassifierLink.type = ?', ' AND self.type <> ?'),
                    array($this->_subjectId, HM_Classifier_Link_LinkModel::TYPE_SUBJECT, HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES)));
            $cardClassifiers = array();
            foreach ($classifiers as $classifier) {
                $cardClassifiers['classifier_' . $classifier->type] .=
                    ($cardClassifiers['classifier_' . $classifier->type] ? '<br>' : '') . $classifier->name;
            }
            foreach ($cards['classifiers']['fields'] as $legend => $value) {
                $cards['classifiers']['fields'][$legend] = isset($cardClassifiers[$value]) ? $cardClassifiers[$value] : "";
            }
        }


        $subjectTeachers = $this->getService('TcProviderTeacher')->fetchAllJoinInner('TeacherSubjects', 'TeacherSubjects.subject_id='.$this->_subjectId);
        $teachers = array(array(_('ФИО'), _('Информация'), _('Контакты')));
        foreach($subjectTeachers as $teacher) {
            $teachers[] = $teacher->getValues(array('name', 'description', 'contacts'));
        }

        $cards['Fulltime_teachers'] = array(
            'title'  => _('Тьюторы'),
            'fields' => $teachers,
            'type'   => 'table'
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            foreach ($cards as $cardId => $card) {
                $cards[$cardId]['edit'] = $this->view->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'edit', 'subject_id' => $this->_subjectId), null, true);
            }
            $cards['Fulltime_teachers']['edit'] = $this->view->url(array('module' => 'teacher', 'controller' => 'list', 'action' => 'index', 'subject_id' => $this->_subjectId), null, true);
        }

        $this->view->cards = $cards;
        $this->view->icon = $this->_subject->getIcon();
    }

    protected function _card($subject)
    {
        return $this->view->card(
            $subject,
            array(
                'name'              => _('Название курса'),
//                'getProviderName()' => _('Провайдер обучения'),
//                'contacts'          => _('Контактные данные'),
//                'description'       => _('Описание')
            ),
            array(
                'title' => _('Карточка внешнего курса'),
            )
        );
    }

    public function teachersCache($field)
    {
        if($this->teachersCache === array()){
            $teachers = $this->getService('TcProviderTeacher')->fetchAllJoinInner('TeacherSubjects');

            foreach ($teachers as $teacher) {
                $this->teachersCache[$teacher->teacher_id] = $teacher->name;
            }
        }


        $result   = _('Нет');
        $teachers = array();

        $teachersIds = explode(',', $field);
        foreach ($teachersIds as $teachersId) {
            if (isset($this->teachersCache[$teachersId])) {
                $teachers[$teachersId] = $this->teachersCache[$teachersId];

            }
        }

        if ($count = count($teachers)) {
            $result = $count > 1 ? '<p class="total">' . sprintf(_n('тьютор plural', '%s тьютор', $count), $count) . '</p>' : '';
            foreach ($teachers as $teacher) {
                $result .=  "<p>{$teacher}</p>";
            }
        }

        return $result;
    }

    public function filterTeachers($data)
    {
        $select = $data['select'];
        $search = trim($data['value']);
        if ($search) {
            $select->joinInner(
                array('tpts2' => 'tc_provider_teachers2subjects'),
                'tpts2.subject_id = s.subid',
                array())
                ->joinInner(
                    array('tpt2'  => 'tc_provider_teachers'),
                    'tpts2.teacher_id = tpt2.teacher_id',
                    array())
                ->where($this->getService('TcSubject')->quoteInto('LOWER(tpt2.name) LIKE ?', '%' . $search . '%'));
        }

        return $select;
    }

    public function filterCities($data)
    {
        $select = $data['select'];
        $search = trim($data['value']);
        if ($search) {
            $select->joinInner(
                    array('clf' => 'classifiers_links'),
                    'clf.item_id = s.subid AND clf.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
                    array())
                ->joinInner(
                    array('cf' => 'classifiers'),
                    'clf.classifier_id = cf.classifier_id AND cf.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES,
                    array())
                ->where($this->getService('TcSubject')->quoteInto('LOWER(cf.name) LIKE ?', '%' . $search . '%'));
        }

        return $select;
    }

    private function setCriterionValue($criterionType, $criterionId)
    {
        switch ($criterionType) {
            case HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION_TEST:
                $criterion = $this->getService('AtCriterionTest')->getOne($this->getService('AtCriterionTest')->find($criterionId));
                $result = array($criterionType . "_" . $criterionId => $criterion->name);
                break;
            case HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION:
                $criterion = $this->getService('AtCriterion')->getOne($this->getService('AtCriterion')->find($criterionId));
                $result = array($criterionType . "_" . $criterionId => $criterion->name);
                break;
            default:
                $result = '';
                break;
        }

        return $result;

    }


    protected function _getMessages() {
        if ($this->_baseType == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) {
            return array(
                self::ACTION_INSERT    => _('Сессия успешно создана'),
                self::ACTION_UPDATE    => _('Сессия успешно обновлена'),
                self::ACTION_DELETE    => _('Сессия успешно удалена'),
                self::ACTION_DELETE_BY => _('Сессии успешно удалены')
            );
        }else{
            return array(
                self::ACTION_INSERT    => _('Внешний курс успешно создан'),
                self::ACTION_UPDATE    => _('Внешний курс успешно обновлён'),
                self::ACTION_DELETE    => _('Внешний курс успешно удалён'),
                self::ACTION_DELETE_BY => _('Внешний курсы успешно удалены')
            );
        }
    }
    protected function _getMessage($type)
    {
        $messages = $this->_getMessages();
        return empty($messages[$type]) ? '' : $messages[$type];
    }

    public function citiesCache($field, $select) {

        if($this->citiesCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['city'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);

            $this->citiesCache = $this->getService('Classifier')->fetchAll(array('classifier_id IN (?)' => $tmp))->getList('classifier_id', 'name');
        }

        $citiesIds = array_unique(explode(',', $field));

        $cities = array();

        foreach ($citiesIds as $cityId) {
            if (isset($this->citiesCache[$cityId])) {
                $cities[$cityId] = $this->citiesCache[$cityId];

            }
        }

        $result   = _('Нет');
        if ($count = count($cities)) {
            $result = $count > 1 ? '<p class="total">' . sprintf(_n('город plural', '%s город', $count), $count) . '</p>' : '';
            foreach ($cities as $city) {
                $result .=  "<p>{$city}</p>";
            }
        }

        return $result;
    }

    public function updateActions($createdBy, $base, $status, $actions)
    {
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            return '';
        }

        $unsetUrl = $this->view->url(array('module' => 'subject', 'controller' => 'fulltime', 'action' => 'new'), null, true);

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
            && 
            ($createdBy != $this->getService('User')->getCurrentUserId())
        ) {
//#20395
            $urls     = explode('<li>', $actions);
            unset($urls[1]);//Халтура, но было полчаса перед отпуском-поездом - потом надо сделать нормально!!!!!
            unset($urls[2]);
            unset($urls[3]);
            return implode('<li>', $urls);
//
        }

        if (($base == HM_Tc_Subject_SubjectModel::BASETYPE_SESSION) || !$status) {
            $urls     = explode('<li>', $actions);
            foreach ($urls as $url) {
                if (!strpos($url, $unsetUrl.'"') && !strpos($url, $unsetUrl.'/')) {
                    $return[] = $url;
                }
            }
            $actions = implode('<li>', $return);
        }

        return $actions;
    }

    public function updateRating($rating)
    {
        $percent = round($rating * 100);
        return $percent.'%';
    }

    public function journalAction()
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array(
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'job'=>'j.name'
        ))
                ->joinInner(array('s' => 'students'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
                ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array());
        $destData1 = $select->query()->fetchAll();

        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array(
            'pos' => 'p.Position',
            'obr' => 'p.Information',
            'birth' => 'YEAR(p.BirthDate)',
            'dep' => 'jp.name',
            'blank'=>new Zend_Db_Expr("null")
        ))
                ->joinInner(array('s' => 'graduated'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
                ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array())
                ->joinLeft(array('jp' => 'structure_of_organ'), 'j.owner_soid = jp.soid', array());
        $destData2 = $select->query()->fetchAll();

        $destData = array_merge($destData1, $destData2);

        $this->getService('PrintForm')->makePrintForm(HM_PrintForm::TYPE_WORD, HM_PrintForm::FORM_STUDY_JOURNAL, array('subject'=>date('y'),'table_1'=>$destData1,'table_2'=>$destData2,'table_3'=>$destData), 'study_plan_'.$this->_getParam('session_id'));
    }

    public function protocolAction()
    {
        $select = $this->getService('User')->getSelect();
        $select->from(array('p' => 'People'), array('fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"), 'p.MID', 'p.Login'))
            ->joinInner(array('s' => 'graduated'), 'p.MID = s.MID AND s.cid='.$this->_subjectId, array())
            ->joinLeft(array('j' => 'structure_of_organ'), 'j.MID = p.MID', array('job'=>'j.name', 'blank'=>new Zend_Db_Expr("null")))
            ->joinLeft(array('oj' => 'structure_of_organ'), 'j.owner_soid = oj.soid', array('dep'=>'oj.name'))
            ->joinLeft(array('m' => 'courses_marks'), '(m.cid = s.CID AND m.mid = s.MID)', array('mark'=>'m.mark'));

        $graduatedIds = $this->_getParam('postMassIds_grid');

        if($graduatedIds) {
            $graduatedIdsList = explode(',', $graduatedIds);
            $select->where('s.SID in (?)', $graduatedIdsList);
        }


        $destData = $select->query()->fetchAll();

        // меняем числовое значение оценки на сдал/не сдал.
        // Про CASE WHEN THEN ELSE END знаю, но что-то упорно ломало запрос,
        // чтобы не залипнуть надолго решил пока так...
        // TODO: разобраться и переделать в виде запроса к БД
        foreach ($destData as $number => $result) {
            $item = array();
            foreach ($result as $key => $value) {
                $item[$key] = ($key != 'mark') ? $value : ($value != 1 ? 'не сдал' : 'сдал');
            }
            $destData[$number] = $item;
        }

        if (!count($destData)) {
            $this->_flashMessenger->addMessage(_('Нет данных для включения в печатную форму'));
            $this->_redirector->gotoUrl('/', array('prependBase' => false));
        }

        $subjectId = ($this->_subject->base == HM_Project_ProjectModel::BASETYPE_SESSION) ? $this->_subject->base_id : $this->_subject->subid;

        switch ($subjectId) {
            case HM_Subject_SubjectModel::BUILTIN_COURSE_LABOR_SAFETY:
                $template       = HM_PrintForm::FORM_LABOR_SAFETY_PROTOCOL;
                $data           = array('table_1' => $destData);
                $outputFileName = 'labor_safety_protocol';
                $this->getService('PrintForm')->makePrintForm(
                    HM_PrintForm::TYPE_WORD,
                    $template,
                    $data,
                    $outputFileName
                );
                break;
            case HM_Subject_SubjectModel::BUILTIN_COURSE_FIRE_SAFETY:
                $template       = HM_PrintForm::FORM_FIRE_SAFETY_PROTOCOL;
                $data           = array('table_1' => $destData);
                $outputFileName = 'fire_safety_protocol';
                $this->getService('PrintForm')->makePrintForm(
                    HM_PrintForm::TYPE_WORD,
                    $template,
                    $data,
                    $outputFileName
                );
                break;
            case HM_Subject_SubjectModel::BUILTIN_COURSE_ELECTRO_SAFETY:
                $template       = HM_PrintForm::FORM_ELECTRO_SAFETY_PROTOCOL;
                $data           = $destData;
                $outputFileName = 'electro_safety_protocol';
                foreach ($data as $key => $datum) {
                    $this->getService('PrintForm')->makePrintForm(
                        HM_PrintForm::TYPE_WORD,
                        $template,
                        $datum,
                        $outputFileName,
                        false,
                        false,
                        false,
                        ($key == count($data)-1) ? true : false
                    );
                }
                $fileZip = Zend_Registry::get('config')->path->upload->temp . $outputFileName . '.zip';
                if (file_exists($fileZip)) {
                    $this->_helper->SendFile(
                        $fileZip,
                        'application/zip',
                        array('filename' => $outputFileName .'.zip')
                    );
                    exit();
                }
                break;
            case HM_Subject_SubjectModel::BUILTIN_COURSE_INDUSTRIAL_SAFETY:
                $template       = HM_PrintForm::FORM_INDUSTRIAL_SAFETY_PROTOCOL;
                $data           = array('table_1' => $destData);
                $outputFileName = 'industrial_safety_protocol';
                $this->getService('PrintForm')->makePrintForm(
                    HM_PrintForm::TYPE_WORD,
                    $template,
                    $data,
                    $outputFileName
                );
                break;
            default:
                die();
                break;
        }
    }
}

