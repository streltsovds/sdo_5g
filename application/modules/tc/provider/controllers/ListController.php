<?php

use HM_Provider_Grid_ProviderGrid as ProviderGrid;

class Provider_ListController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $service     = 'TcProvider';
    protected $idParamName = 'provider_id';
    protected $idFieldName = 'provider_id';
    protected $id           = 0;

    protected $_sessionId = 0;

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Провайдер успешно создан'),
            self::ACTION_UPDATE    => _('Провайдер успешно обновлён'),
            self::ACTION_DELETE    => _('Провайдер успешно удалён'),
            self::ACTION_DELETE_BY => _('Провайдеры успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Провайдер не был создан'),
            self::ERROR_NOT_FOUND        => _('Провайдер не найден')
        );
    }

    public function init()
    {
        $request = $this->getRequest();

        $requestSources = $request->getParamSources();
        $request->setParamSources(array());

        $providerId = $request->getParam('provider_id', 0);

        $this->_sessionId = $request->getParam('session_id', 0);

        $request->setParamSources($requestSources);

        $this->_defaultService = $this->getService('TcProvider');

        if ($providerId > 0) {
            $provider = $this->_defaultService->find($providerId)->current();
            if ($provider->type != HM_Tc_Provider_ProviderModel::TYPE_PROVIDER) {
                parent::init();
                $this->_redirectToIndex();
            }

            $actionName = $this->getRequest()->getActionName();

            if($actionName != 'description'){
                HM_Provider_View_ExtendedView::init($this);
            }

            $currentRole = $this->getService('User')->getCurrentUserRole();
            if (in_array($actionName, array ('delete', 'edit')) && $this->getService('Acl')->inheritsRole($currentRole, array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
                if ($provider->created_by != $this->getService('User')->getCurrentUserId()) {
                    parent::init();
                    $this->_redirectToIndex();
                }
            }
        }

        $formParams = array();

        if ($this->_sessionId) {
            $formParams['cancelUrl'] = $this->_getBackToSessionUrl();
        }

        $form = new HM_Form_TcProvider($formParams);
        $form->addModifier(new HM_Form_Modifier_Provider());
        $this->_setForm($form);

        parent::init();

    }

    protected function _getBackToSessionUrl()
    {
        $cancelUrlParams = array(
            'module'     => 'session',
            'controller' => 'new-providers',
            'action'     => 'index',
            'session_id' => $this->_sessionId
        );

        $view = $this->view;

        return $view->serverUrl($view->url($cancelUrlParams, null, true));

    }

    public function indexAction()
    {
        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = $this->getService('TcProvider');

        $view            = $this->view;
        $providerId       = $this->id;

        $gridId = 'TcProviders';

        $grid = ProviderGrid::create(array(
            'controller' => $this,
            'providerId' => $providerId,
            'gridId'     => $gridId
        ));


        $gridSwitcher = $grid->getSwitcher();

        if ($gridSwitcher->isVisible()) {
            $onlyMy = ($gridSwitcher->getValue() === ProviderGrid::SWITCHER_ONLY_MY);
        } else {
            $onlyMy = false;
        }

        $listSource = $providerService->getListSource(HM_Tc_Provider_ProviderModel::TYPE_PROVIDER, $onlyMy);

        $this->_request->setParam("slaveOrder{$gridId}", 'provider_id ASC');

        $view->assign(array(
            'grid' => $grid->init($listSource)
        ));

    }

    public function updateStatus($status)
    {
        if($status){
            return _('Да');
        }else{
            return _('Нет');
        }
    }

    public function updateName($name, $providerId)
    {
        return '<a href="' . $this->view->url(array('controller' => 'list', 'action' => 'view', 'provider_id' => $providerId)) . '">' . $this->view->escape($name) . '</a>';
    }



    public function create(Zend_Form $form)
    {
        $data = $form->getValues();

        $provider = $this->_defaultService->insert(array(
            'name'=> $data['name'],
            'description'=> $data['description'],
            'status'=> true,
            'address_legal'=> $data['address_legal'],
            'address_postal'=> $data['address_postal'],
            'inn'=> $data['inn'],
            'kpp'=> $data['kpp'],
            'bik'=> $data['bik'],
            'type' => HM_Tc_Provider_ProviderModel::TYPE_PROVIDER,
            'subscriber_fio'      => $data['subscriber_fio'],
            'subscriber_position' => $data['subscriber_position'],
            'subscriber_reason'   => $data['subscriber_reason'],
            'account'=> $data['account'],
            'account_corr'=> $data['account_corr'],
            'information' => $data['information'],
        ));

        $this->_defaultService->updateContacts($data['contacts'], $provider->provider_id);
        $this->_defaultService->setClassifiers($data['city'], $provider->provider_id);
    }

    public function update(Zend_Form $form)
    {
        $data = $form->getValues();
        $provider = $this->_defaultService->update(
            array(
                'provider_id' => $data['provider_id'],
                'name'=> $data['name'],
                'description'=> $data['description'],
                'status'=> $data['status'],
                'address_legal'=> $data['address_legal'],
                'address_postal'=> $data['address_postal'],
                'inn'=> $data['inn'],
                'kpp'=> $data['kpp'],
                'bik'=> $data['bik'],
                'subscriber_fio'      => $data['subscriber_fio'],
                'subscriber_position' => $data['subscriber_position'],
                'subscriber_reason'   => $data['subscriber_reason'],
                'account'=> $data['account'],
                'account_corr'=> $data['account_corr'],
                'information' => $data['information'],
            ));
        $this->_defaultService->updateContacts($data['contacts'], $provider->provider_id);
        $this->_defaultService->setClassifiers($data['city'], $provider->provider_id);
    }

    public function deleteAction()
    {
        $id = $this->_getParam('provider_id', 0);

        if ($id) {
            $this->delete($id);
            $this->_flashMessenger->addMessage($this->_getMessage(self::ACTION_DELETE));
        }

        $this->_redirectToIndex();
    }

    public function deleteByAction()
    {
        $postMassIds = $this->_getParam('postMassIds_TcProviders', '');
        if (!strlen($postMassIds)) {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
        }
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                if ($this->getService('Acl')->inheritsRole(
                    $this->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

                    $providers = $this->_defaultService->fetchAll(
                        $this->_defaultService->quoteInto(
                            array('provider_id in (?)', ' AND created_by=?'),
                            array($ids, $this->getService('User')->getCurrentUserId())));
                    $ids = $providers->getList('provider_id');
                }

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
        $postMassIds = $this->_getParam('postMassIds_TcProviders', '');
        if (!strlen($postMassIds)) {
            $postMassIds = $this->_getParam('postMassIds_grid', '');
        }
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                /*
                if ($this->getService('Acl')->inheritsRole(
                    $this->getService('User')->getCurrentUserRole(),
                    array(HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {

                    $providers = $this->_defaultService->fetchAll(
                        $this->_defaultService->quoteInto(
                            array('provider_id in (?)', ' AND created_by=?'),
                            array($ids, $this->getService('User')->getCurrentUserId())));
                    $ids = $providers->getList('provider_id');
                }
                */

                $this->_defaultService->updateWhere(array('status'=> HM_Tc_Provider_ProviderModel::STATUS_PUBLISHED),
                    $this->_defaultService->quoteInto(
                        array('provider_id in (?)'),
                        array($ids)));


                $this->_flashMessenger->addMessage(_('Провайдеры успешно утверждены'));
            }
        }
        $this->_redirectToIndex();
    }

    public function delete($id)
    {
        if ($id == HM_Tc_Provider_ProviderModel::HARDCODED_ID_INTERNAL_STUDY) {
            return false;
        }
        return $this->_defaultService->delete($id);
    }

    protected function _redirectToIndex()
    {
        if ($this->_sessionId) {
            $this->_redirector->gotoUrl($this->_getBackToSessionUrl());
        }

        $this->_redirector->gotoSimple('index');
    }



    public function viewAction()
    {
        $providerId = $this->_getParam('provider_id', 0);
        if (!$providerId) {
            $this->_redirector->gotoSimple('index');
        }
        $provider = $this->getOne($this->_defaultService->fetchAllDependence('Contact',
            $this->quoteInto('provider_id = ?', $providerId))
        );

        $view = $this->view;

        $contacts = array(array(_('ФИО'), _('Должность'), _('Телефон'), _('E-mail')));
        if (count($provider->contacts)) {
            foreach ($provider->contacts as $contact) {
                $contacts[] = $contact->getValues(array('name', 'position', 'phone', 'email'));
            }
        }

        $providerSubjects = $this->getService('TcSubject')->fetchAll(
            $this->quoteInto(
                array('provider_id = ? ', ' AND (base is NULL OR base != ?)', ' AND status = ?'),
                array($providerId, HM_Subject_SubjectModel::BASETYPE_SESSION, HM_Tc_Subject_SubjectModel::FULLTIME_STATUS_PUBLISHED)
            )
        );

        $subjects = array(array(_('Название'), _('Краткое описание'),  _('Стоимость'), _('Категория')));

        foreach($providerSubjects as $subject) {
            $subjArray = $subject->getValues(array('name', 'category', 'description', 'price'));
            $subjArray['category'] = HM_Tc_Subject_SubjectModel::getVariant($subjArray['category'], 'FulltimeCategories');
            $subjects[] = $subjArray;
        }

        $providerTeachers = $this->getService('TcProviderTeacher')->fetchAll(
            $this->quoteInto(
                array('provider_id = ? '),
                array($providerId)
            )
        );

        $teachers = array(array(_('ФИО'), _('Информация'), _('Контакты')));

        foreach($providerTeachers as $teacher) {
            $teachers[] = $teacher->getValues(array('name', 'description', 'contacts'));
        }

        $details = array(
            'contacts' => $contacts,
            'cities' => $provider->getCities(),
            'subjects' => $subjects,
            'teachers' => $teachers,
            'information' => $provider->information,
        );
        
        $aclService = $this->getService('Acl');
        
        $view->canEdit = $aclService->isCurrentAllowed('mca:provider:list:edit');
        $view->details = $details;
        $view->provider= $provider;
    }

    public function setDefaults(Zend_Form $form)
    {
        $providerId = $this->_getParam('provider_id', 0);
        $provider = $this->getOne($this->_defaultService->find($providerId));
        if ($provider){
            $values = $provider->getValues();
            $contacts = $this->getService('TcProviderContact')->fetchAll(
                $this->quoteInto('provider_id = ?', $providerId));
            if ($contacts) {
                foreach ($contacts as $contact) {
                    $values['contacts'][$contact->contact_id] = array(
                        'fio' => $contact->name,
                        'position' => $contact->position,
                        'phone' => $contact->phone,
                        'email' => $contact->email
                    );
                }
            }
            $cities = $provider->getCities();
            if ($cities) {
                $values['city'] = $cities;
            }
            $form->populate($values);
        }
    }

    public function contactCardAction()
    {
        $contactId = $this->_getParam('contact_id', 0);
        if (!$contactId) {
            return;
        }
        $contact = $this->getOne($this->getService('TcProviderContact')->find($contactId));
        /*if ($contact) {
            $data = array(
                _('ФИО') => $contact->name,
                _('Должность') => $contact->position,
                _('Телефон') => $contact->phone,
                _('E-mail') => $contact->email
            );

        }*/
        $this->view->contact = $contact;
    }

    public function cardAction()
    {
        $providerId = $this->_getParam('provider_id', 0);
        if (!$providerId) {
            $this->_redirector->gotoSimple('index');
        }
        $provider = $this->getOne($this->_defaultService->find($providerId));

        $view = $this->view;

        $view->provider = $provider;
    }

} 