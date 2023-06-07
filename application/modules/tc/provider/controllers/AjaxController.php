<?php

class Provider_AjaxController extends HM_Controller_Action
{
    public function init()
    {
        parent::init();

        $this
            ->_helper
            ->ContextSwitch()
            ->setAutoJsonSerialization(true)

            ->addActionContext('city',     'json')
            ->addActionContext('provider', 'json')

            ->initContext('json');

    }

    public function cityAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $q = strtolower(trim($this->_request->getParam('tag')));

        $res = array();

        if (!empty($q)) {

            $cities = $this->getService('Classifier')->fetchAll($this->quoteInto(
                array('LOWER(name) LIKE LOWER(?) ',' AND type = ?'),
                array('%'.$q.'%', HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES)
            ));

            foreach($cities as $city) {
                $res[] = array(
                    'key'   => $city->name,
                    'value' => $city->classifier_id
                );
            }
        }

        $this->view->assign($res);

    }

    public function providerAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $q = strtolower(trim($this->_request->getParam('tag')));

        $result = array();

        if (!empty($q)) {

            /** @var HM_Tc_Provider_ProviderService $providerService */
            $providerService = $this->getService('TcProvider');

            $providers = $providerService->fetchAll($this->quoteInto(
                array('LOWER(name) LIKE ?', ' AND status = ?'),
                array('%'.strtolower($q).'%', 1)
            ));

            foreach($providers as $provider) {
                $result[] = array(
                    'key'   => $provider->name,
                    'value' => $provider->provider_id
                );
            }
        }

        $this->view->assign($result);

    }
}