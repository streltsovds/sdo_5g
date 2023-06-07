<?php
class Subject_FeedbackController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_subjectId   = 0;
    protected $_providerId  = 0;
    protected $_subject;
    protected $_provider;

    protected $_feedback;

    public function init() {
        $requestSources = $this->getRequest()->getParamSources();
        $this->getRequest()->setParamSources(array());
        $getParams = $this->getRequest()->getParams();
        $this->_providerId = isset($getParams['provider_id']) ? $getParams['provider_id'] : 0;
        $this->_subjectId  = isset($getParams['subject_id'])  ? $getParams['subject_id']  : (isset($getParams['subid']) ? $getParams['subid']  : 0);

        $this->getRequest()->setParamSources($requestSources);

        if ($this->_subjectId > 0) {
            $this->_subject    = $this->getOne(
                $this->getService('TcSubject')->find($this->_subjectId)
            );
        } elseif ($this->_providerId > 0) {
            $this->_provider    = $this->getOne(
                $this->getService('TcProvider')->find($this->_providerId)
            );
        }

        HM_Subject_View_FeedbackExtendedView::init($this);
        parent::init();

        if (!$this->_subject && !$this->_provider &&
            !$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
        ) {
            $this->_redirector->gotoUrl($this->view->url(
                array('baseUrl' => '/', 'action' => 'index', 'controller' => 'index', 'module' => 'index'), null, true));
        };
    }

    public function getSubjectId()
    {
        return $this->_subjectId;
    }
    public function getProviderId()
    {
        return $this->_providerId;
    }

    public function isProviderCase()
    {
        return $this->getProviderId() > 0;
    }

    protected function _redirectToIndex()
    {
        $url = array(
            'baseUrl'    => '',
            'module'     => 'subject',
            'controller' => 'feedback',
            'action'     => $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
                    HM_Role_Abstract_RoleModel::ROLE_ENDUSER) ? 'my' : 'index',
            'subject_id' => $this->_subjectId
        );

        $this->_redirector->gotoUrl($this->view->url($url, null, true));
    }

    public function indexAction()
    {
        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $this->getHelper('viewRenderer')->setNoRender();
            $this->myAction();
            echo $this->view->render('feedback/my.tpl');
            return true;
        }

        $select = $this->getService('TcFeedback')->getSelect();
        $select->from(
                array('tcf' => 'tc_feedbacks'),
                array(
                    'tcf.user_id',
                    'session' => 's.name',
                    's.subid',
                    'tcf.text',
                    'fio'  => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
                    'mark'      => 'sv.value',
                    'mark_text' => 'sv.text',
                    'graduted'  => 'gr.end',
                    'datemark'  => 'tcf.date'
                ))
            ->joinInner(
                array('p' => 'People'),
                'tcf.user_id = p.MID',
                array())
            ->joinInner(
                array('gr' => 'graduated'),
                'gr.MID=p.MID and gr.CID=tcf.subject_id',
                array())
            ->joinInner(
                array('sv' => 'scale_values'),
                'sv.value_id = tcf.mark',
                array())
            ->joinInner(
                array('s' => 'subjects'),
                'tcf.subject_id = s.subid AND s.provider_type = '. HM_Tc_Provider_ProviderModel::TYPE_PROVIDER,
                array());
        if ($this->isProviderCase()) {
            $select->where('s.provider_id=' . $this->_providerId);
        } elseif ($this->_subject) {
            $select->where('s.subid=' . $this->_subjectId . ' OR s.base_id=' . $this->_subjectId);
        }

        $urlSubject = array('module' => 'subject',  'controller' => 'fulltime', 'action' => 'view', 'subject_id' => '{{subid}}');
        $urlDetails = array('module' => 'subject',  'controller' => 'feedback', 'action' => 'my', 'subject_id' => '{{subid}}');

        $grid = $this->getGrid(
            $select,
            array(
                'text'       => array('hidden' => true),
                'subid'      => array('hidden' => true),
                'session'    => ($this->isProviderCase() || empty($this->_subject) || ($this->_subject->base != HM_Subject_SubjectModel::BASETYPE_SESSION))
                    ? array(
                            'title'     => _('Название сессии'),
                            'decorator' => $this->view->cardLink($this->view->url(array('controller' => 'fulltime', 'action' => 'card', 'subject_id' => '')) . '{{subid}}', _('Карточка сессии')) . ' <a href="' . $this->view->url($urlSubject, null, true, false) . '">{{session}}</a>'
                        )
                    : array('hidden' => true),
                'user_id'    => array('hidden' => true),
                'fio'        => $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))
                    ? array('hidden' => true)
                    : array(
                            'title' => _('Слушатель'),
                            'decorator' => ' <a href="' . $this->view->url($urlDetails, null, true, false) . '/user_id/{{user_id}}">{{fio}}</a>'
                        ),
                'mark'       => array(
                    'title' => _('Оценка'),
                    'decorator' => $this->view->dialogLinkOld('{{fio}}', '{{text}}', '{{mark}}', array('id' => '{{user_id}}'))
                ),
                'mark_text'  => array(
                    'title' => _('Значение оценки'),
                ),
                'graduted'  => array(
                    'title'  => _('Дата окончания обучения'),
                    'format' => array(
                        'DateTime',
                        array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                    ),
                ),
                'datemark' => array(
                    'title' => _('Дата выставления отзыва'),
                    'format' => array(
                        'DateTime',
                        array('date_format' => Zend_Locale_Format::getDateTimeFormat())
                    ),
                )
            ),
            array(
                'fio'  => true,
                'session'  => true,
                'mark' => array('values' => $this->getService('ScaleValue')->fetchAll('scale_id=' . HM_Scale_ScaleModel::TYPE_TC_FEEDBACK)->getList('value_id', 'value')),
                'mark_text' => true,
                'graduted' => array('render' => 'DateTimeStamp'),
                'datemark' => array('render' => 'DateTimeStamp')
            )
        );

        $this->view->grid = $grid;
    }

    public function myAction()
    {
        $userId = $this->getService('User')->getCurrentUserId();

        $form = new HM_Form_Feedback();

        if($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(),
            array(HM_Role_Abstract_RoleModel::ROLE_DEAN, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $userId   = $this->_getParam('user_id', 0);
            $feedback = $this->getOne($this->getService('TcFeedback')->fetchAll("subject_id=" . $this->_subjectId . " AND user_id=" . $userId));
            $url = $this->view->url(array('action' => 'index', 'controller' => 'feedback', 'module' => 'subject', 'subject_id' => $this->_subjectId), null, true);
            if (!$userId || !$feedback) {
                $this->_redirector->gotoUrl($url, array('prependBase' => false));
            }

            $user = $this->getService('User')->getOne($this->getService('User')->find($userId));
            $this->view->setHeader($user->LastName . ' ' . $user->FirstName . ' ' . $user->Patronymic);
            $this->view->setSubHeader('<a href="' . $url . '">' . _('Назад') . '</a>');


            $form->populate($feedback->getValues());
            $form->addModifier(new HM_Form_Modifier_FeedbackDisabled());

        } elseif (!$this->getService('TcSubject')->isGraduated($this->_subjectId, $userId)) {
            $this->view->notifications(array(array('type' => HM_Notification_NotificationModel::TYPE_ERROR, 'message' => _('Форма оценки станет доступна после окончания курса'))));
            $form->addModifier(new HM_Form_Modifier_FeedbackDisabled());
        } else {
            $feedback = $this->getOne($this->getService('TcFeedback')->fetchAll("subject_id=" . $this->_subjectId . " AND user_id=" . $userId));
            if (!empty($feedback)) {
                $form->populate($feedback->getValues());
                $form->addModifier(new HM_Form_Modifier_FeedbackDisabled());

                $this->view->notifications(array(_('Ваша оценка учтена')));
            } else {
                if ($this->_request->isPost() && $form->isValid($this->_request->getParams())) {
                    $data = $form->getValues();
                    $data['subject_id'] = $this->_subjectId;
                    $data['user_id']    = $userId;
                    $data['date']       = HM_Date::now()->toString(HM_Date::SQL);

                    $this->getService('TcFeedback')->insert($data);
                    $this->_redirectToIndex();
                }
            }
        }
        $this->_setForm($form);
        $this->view->form = $form;
    }
}

