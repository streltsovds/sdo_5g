<?php

class Session_EducationController extends HM_Controller_Action {

    const ACTION_INSERT    = 1;
    const ACTION_UPDATE    = 2;
    const ACTION_DELETE    = 3;
    const ACTION_DELETE_BY = 4;

    protected $_sessionId;
    protected $_session;

    protected $_defaultService;

    protected $coursesCache = array();
    protected $citiesCache  = array();
    protected $classesCache  = array();

    protected $gridCategory;

    public function init() {
        //$this->getService('TcSession')->createApplications($this->_sessionId); die();

        $this->_defaultService = $this->getService('TcSession');
        $this->_sessionId = $this->_getParam('session_id', 0);
        $session = $this->getOne($this->_defaultService->fetchAllDependence(
                array('Cycle', 'Department'),
                $this->quoteInto('session_id = ?', $this->_sessionId))
        );

        if ($session) {
            $this->_session = $session;
            HM_Session_View_ExtendedView::init($this);
        }
        parent::init();
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function getGridCategory()
    {
        return $this->gridCategory;
    }

    protected function _redirectToIndex()
    {
        $url = array(
            'action'     => 'view',
            'controller' => 'list',
            'module'     => 'session',
            'baseUrl'    => '',
            'session_id' => $this->_sessionId
        );
        $this->_redirector->gotoUrl($url);
    }

    public function recomendedAction()
    {
        if (!$this->_session) {
            $this->_redirectToIndex();
        }

        /** @var HM_Tc_Application_ApplicationService $defaultService */
        $defaultService = $this->getService('TcApplication');

        $this->gridCategory = HM_Tc_Application_ApplicationModel::CATEGORY_RECOMENDED;
        $listSource = $defaultService->getListSource($this->_sessionId);
        // после подачи заявки строка исчезает из грида
        $listSource->where('ap.status=?', HM_Tc_Application_ApplicationModel::STATUS_INACTIVE);
        $listSource->where('ap.category = ?', $this->gridCategory);

        $grid = HM_Session_Grid_EducationGrid::create(array(
            'controller' => $this,
            'view'       => $this->view,
        ));

        $this->view->assign(array(
            'grid' => $grid->init($listSource)
        ));
    }

    public function requiredAction()
    {
        if (!$this->_session) {
            $this->_redirectToIndex();
        }

        /** @var HM_Tc_Application_ApplicationService $defaultService */
        $defaultService = $this->getService('TcApplication');

        $this->gridCategory = HM_Tc_Application_ApplicationModel::CATEGORY_REQUIRED;
        $listSource = $defaultService->getListSource($this->_sessionId);
        $listSource->where('ap.category = ?', $this->gridCategory);

        if (!$this->_request->getParam("ordergrid")) {
            $this->_request->setParam("ordergrid", 'fio_ASC');
        }

        $grid = HM_Session_Grid_EducationGrid::create(array(
            'controller' => $this,
            'view'       => $this->view
        ));

        $this->view->assign(array(
            'grid' => $grid->init($listSource)
        ));
    }

    public function additionalAction()
    {
        if (!$this->_getParam('ordergrid', '')) {
            $this->_setParam('ordergrid', 'name_ASC');
        }

        $select = $this->getService('TcSubject')->getSelect();
        $select->from(array('s' => 'subjects'),
            array(
                'subid'        => 's.subid',
                'subject_name' => 's.name',
                'provider_id'  => 'pr.provider_id',
                'provider'     => 'pr.name',
                'direction' => 'c.name',
                'price'        => 's.price',
                'category_id'  => 's.category',
                'status'       => 's.status',
            ))
            ->joinLeft(
                array('pr' => 'tc_providers'),
                's.provider_id = pr.provider_id',
                array()
            )->joinLeft(
                array('c' => 'classifiers'),
                's.direction_id = c.classifier_id',
                array())
            ->where($this->quoteInto(
                array(
                    's.period_restriction_type = ? OR ',
                    's.period_restriction_type = ? OR ',
                    's.end > ?',
                ),
                array(
                    HM_Tc_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                    HM_Tc_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                    $this->getService('TcSubject')->getDateTime()
                )
            ))
            ->where('s.base IS NULL OR s.base <> ?',    HM_Tc_Subject_SubjectModel::BASETYPE_SESSION)
            ->where($this->quoteInto(
                array(
                    '(s.type = ? OR ',
                    's.provider_id = ?)',
                ),
                array(
                    HM_Tc_Subject_SubjectModel::TYPE_FULLTIME,
                    HM_Tc_Provider_ProviderModel::HARDCODED_ID_INTERNAL_STUDY
                )
            ))
            ->where('s.category IN (?)', array(HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_ADDITION))
            ->group(array('s.subid','s.category', 's.name','s.status','s.price','pr.provider_id','pr.name','rt.rating', 'c.name'));

        $select->where('s.base != ? OR s.base IS NULL', HM_Tc_Subject_SubjectModel::BASETYPE_SESSION);
        $select->joinLeft(
            array('rt' => 'subjects_fulltime_rating'),
            'rt.subid=s.subid',
            array(
                'rating'    => 'rt.rating',
            ));

        $select->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME);
        $select->where('s.type = ?', HM_Tc_Subject_SubjectModel::TYPE_FULLTIME);

        $urlSubject   = array('module' => 'subject',  'controller' => 'fulltime', 'action' => 'view', 'subject_id' => '{{subid}}');
        $cardSubject  = array('module' => 'subject',  'controller' => 'fulltime', 'action' => 'card', 'subject_id' => '');
        $urlProvider  = array('module' => 'provider', 'controller' => 'list',     'action' => 'view', 'provider_id' => '{{provider_id}}');
        $cardProvider = array('module' => 'provider', 'controller' => 'list',     'action' => 'card', 'provider_id' => '');

        $statusValues = array(0 => _('Нет'), 1 => _('Да'));

        $grid = $this->getGrid($select, array(
                'subid'        => array('hidden' => true),
                'subject_name' => array(
                    'title'     => _('Название'),
                    'decorator' => $this->view->cardLink($this->view->url($cardSubject) . '{{subid}}', _('Карточка внешнего курса')) . ' <a href="' . $this->view->url($urlSubject, null, true, false) . '">{{subject_name}}</a>'
                ),
                'provider_id'  => array('hidden' => true),
                'provider'     => array(
                    'title'     => _('Провайдер'),
                    'decorator' => $this->view->cardLink($this->view->url($cardProvider) . '{{provider_id}}', _('Карточка провайдера')) . ' <a href="' . $this->view->url($urlProvider, null, true, false) . '">{{provider}}</a>'
                ),
                'city'         => array('hidden' => true),
                'direction'  => array(
                    'title'     => _('Направление обучения'),
                ),
                'price'        => array(
                    'title'     => _('Стоимость'),
                    'style'     => "text-align:right",
                    'callback'  => array(
                        'function' => 'number_format',
                        'params'   => array('{{price}}', 0, '.', ' '))
                ),
                'category'      => array('title' => _('Категория обучения')),
                'category_id'   => array('hidden' => true),
                'rating'        => array(
                    'title' => _('Рейтинг'),
                    'callback' => array(
                        'function' => array($this, 'updateRating'),
                        'params'   => array('{{subid}}', '{{rating}}')),
                ),
                'status'       => array('title' => _('Согласован службой обучения'))
            ),
            array(
                'subject_name'  => true,
                'provider'      => true,
                'city'          => array('callback' => array('function' => array($this, 'filterCities'))),
                'direction'     => true,
                'category'      => array('values' => HM_Tc_Subject_SubjectModel::getVariants('FulltimeCategories')),
                'price'         => array('render' => 'Number'),
                'status'        => array('values' => array(0 => _('Нет'), 1 => _('Да')))
            )

        );

        $grid->updateColumn('category', array(
                'callback' => array(
                    'function' => array($this, 'updateVariants'),
                    'params' => array('{{category}}', 'FulltimeCategories')
                )
            )
        );

        $view = $this->view;

        if ($this->_session) {

            $createAction = $this->getService('TcSession')->isApplicable($this->_session);
            if ($createAction) {

                $grid->addAction(array(
                    'module'     => 'application',
                    'controller' => 'list',
                    'action'     => 'create',
                    'category' => HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION,
                    'gridmod' => null,
                ),
                    array(
                        'subid'
                    ),
                    _('Подать заявку'),
                    $this->getService('User')->getCurrentUserId() ? _('Данное действие может быть необратимым. Вы действительно хотите продолжить?') : null
                );
            }

            $view->assign(array(
                'gridMenuActions' => array(
                    array(
                        'url' => $view->url(array(
                                'module'     => 'session',
                                'controller' => 'suggest-provider',
                                'action'     => 'new',
                                'session_id' => $this->_sessionId
                            )),
                        'title'     => 'Предложить свой курс',
                        'target'    => false
                    )
                )
            ));
        }

        $grid->updateColumn('status', array(
                'callback' => array(
                    'function' => array($this, 'updateVariant'),
                    'params' => array('{{status}}', $statusValues)
                )
            )
        );

        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid;

    }

    public function updateVariant($value, $array)
    {
        return isset($array[$value]) ? $array[$value] : '';
    }

    public function updateRating($subid, $rating)
    {
        $cardRating  = _('Рейтинг внешнего курса');
        if (!$rating) {
            return 0;
        }
        return $this->view->cardLink($this->view->url(
            array(
                'module' => 'subject',
                'controller' => 'fulltime',
                'action' => 'rating',
                'subject_id' => $subid
            )),
            $cardRating
        ) . ' ' . number_format($rating, 10, '.', ' ');
    }

    public function updateVariants($variantId, $method)
    {
        return HM_Tc_Subject_SubjectModel::getVariant($variantId, $method);
    }

    public function monthDate($date, $checkSession = true)
    {
        $tst = strtotime($date);
        if (!$date || !$tst || (date('Y-m-d', $tst) == '1900-01-01')) {
            return '';
        }
        if (($checkSession && $date<$this->_session->date_begin)) {
            return '';
        }

        return month_name((int) date('m', $tst)) . " " . date('Y', $tst);
    }
}