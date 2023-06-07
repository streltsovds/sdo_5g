<?php

class Session_SuggestProviderController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    /** @var HM_Tc_Provider_ProviderService */
    protected $_providerService = null;
    /** @var HM_Subject_SubjectService */
    protected $_subjectService = null;

    protected $_sessionId  = 0;
    /**
     * @var HM_Tc_Session_SessionModel
     */
    protected $_session = null;

    protected function _getMessages()
    {
        return array(
            self::ACTION_INSERT    => _('Курс успешно создан'),
            self::ACTION_UPDATE    => _('Курс успешно обновлён'),
            self::ACTION_DELETE    => _('Курс успешно удалён'),
            self::ACTION_DELETE_BY => _('Курсы успешно удалены')
        );
    }

    protected function _getErrorMessages()
    {
        return array(
            self::ERROR_COULD_NOT_CREATE => _('Курс не был создан'),
            self::ERROR_NOT_FOUND        => _('Курс не найден')
        );
    }

    public function init()
    {
        parent::init();

        $this->_defaultService = $this->getService('TcSession');
        $this->_subjectService  = $this->getService('TcSubject');
        $this->_providerService = $this->getService('TcProvider');

        /** @var HM_Controller_Request_Http $request */
        $request = $this->getRequest();

        $requestSources = $request->getParamSources();
        $request->setParamSources(array());

        $this->_sessionId  = (int) $request->getParam('session_id', 0);
        $this->_session = $this->getOne(
            $this->_defaultService->find($this->_sessionId)
        );

        $request->setParamSources($requestSources);

        HM_Session_View_ExtendedView::init($this);

        $this->_initForm();

    }

    public function getSession()
    {
        return $this->_session;
    }

    protected function _initForm()
    {
        $this->_setForm(new HM_Form_SuggestProviderForm(array(
            'cancelUrl' => array(
                'baseUrl'    => 'tc',
                'module'     => 'session',
                'controller' => 'education',
                'action'     => 'additional',
                'session_id' => $this->_sessionId
            )
        )));
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('additional', 'education', 'session', array(
            'session_id' => $this->_sessionId
        ));
    }

    public function setDefaults(HM_Form_SuggestProviderForm $form)
    {

    }


    public function newAction()
    {
        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = $this->_providerService;
        /** @var HM_Classifier_ClassifierService $classifierService */
        $classifierService = $this->getService('Classifier');

        $request = $this->getRequest();

        if ($request->isPost()) {

            // посстанавливаем значение автокомплита провайдеров

            $providers = $request->getParam('provider', array());

            if (count($providers)) {
                $providerId = $providers[0];

                if ($providerId > 0) {

                    $providerItem = $this->getOne($providerService->find($providerId));

                    if ($providerItem) {
                        $request->setParam('provider', array(
                            $providerItem->provider_id => $providerItem->name
                        ));
                    } else {
                        $request->setParam('provider', array(
                            $providerId => $providerId
                        ));
                    }

                } else {
                    $request->setParam('provider', array(
                        $providerId => $providerId
                    ));
                }
            }

            // посстанавливаем значение автокомплита городов

            $cities = $request->getParam('city', array());

            $newCities = array();

            foreach ($cities as $cityId) {

                $cityItem = $this->getOne($classifierService->find($cityId));

                if ($cityItem) {
                    $newCities[$cityItem->classifier_id] = $cityItem->name;
                }
            }

            $request->setParam('city', $newCities);
        }

        parent::newAction();

    }

    public function create(HM_Form_SuggestProviderForm $form)
    {
        /** @var HM_Subject_SubjectService $subjectService */
        $subjectService = $this->_subjectService;
        /** @var HM_Classifier_Link_LinkService $classifierLinkService */
        $classifierLinkService = $this->getService('ClassifierLink');

        $values = $form->getValues();

        $provider = $this->_createProvider($values['provider'], $values['provider_contacts']);

        $data = array(
            'name'                      => $values['subject_name'],
            'price'                     => $values['subject_cost'],
            'price_currency'            => 'RUB',
            'criterion_id'              => 0,
            'criterion_type'            => 0,
            'feedback'                  => 1,
            'base'                      => HM_Tc_Subject_SubjectModel::BASETYPE_BASE,
            'period_restriction_type'   => HM_Tc_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
            'type'                      => HM_Tc_Subject_SubjectModel::TYPE_FULLTIME,
            'city'                      => implode(',', array_keys($values['city'])),
            'end'                       => HM_Date::now()->addYear(1)->toString(HM_Date::SQL),
            'provider_id'               => $provider->provider_id,
            'status'                    => 0,
            'category'                  => HM_Tc_Subject_SubjectModel::FULLTIME_CATEGORY_ADDITION,
            'create_from_tc_session'    => $this->_sessionId
        );

        $subject = $subjectService->insert($data);

        $cities = $values['city'];

        if (!empty($cities)) {

            $providerIds = array_keys($values['provider']);
            $providerId  = $providerIds[0];

            foreach ($cities as $classifierId => $cityName) {

                $classifierLinkService->insert(array(
                    'item_id'       => $subject->subid,
                    'classifier_id' => $classifierId,
                    'type'          => HM_Classifier_Link_LinkModel::TYPE_SUBJECT
                ));

                //Если провайдер новый ,то ему тоже добавляем города
                if ($provider->provider_id && ($providerId !== $provider->provider_id )) {
                    $classifierLinkService->insert(array(
                        'item_id'       => $provider->provider_id,
                        'classifier_id' => $classifierId,
                        'type'          => HM_Classifier_Link_LinkModel::TYPE_TC_PROVIDER
                    ));
                }
            }
        }

    }

    /**
     * @param $provider
     * @param $providerContacts
     *
     * @return HM_Tc_Provider_ProviderModel
     */
    protected function _createProvider($provider, $providerContacts)
    {
        /** @var HM_Tc_Provider_ProviderService $providerService */
        $providerService = $this->_providerService;

        $providerIds = array_keys($provider);
        $providerId = $providerIds[0];

        if (is_numeric($providerId) && $providerId > 0 && $providerId !== $provider[$providerId]) {
            // провайдер существует
            return $this->getOne($providerService->find($providerId));
        }

        return $providerService->insert(array(
            'name'                   => $providerId,
            'description'            => $providerContacts,
            'status'                 => 0,
            'address_legal'          => '',
            'address_postal'         => '',
            'inn'                    => '',
            'kpp'                    => '',
            'bik'                    => '',
            'account'                => '',
            'account_corr'           => '',
            'create_from_tc_session' => $this->_sessionId
        ));

    }

}