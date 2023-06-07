<?php

/**
 * Description of Hh
 *
 * @author tutrinov
 */
class HM_Recruit_RecruitingServices_Rest_Hh extends HM_Recruit_RecruitingServices_Rest_Abstract {

    const RESPONSE_BODY_TYPE_XML = 'xml';
    const RESPONSE_BODY_TYPE_JSON = 'json';
    
    protected $version = '1';
    protected $responseBodyType = null;
    protected $hhSettings = null;

    protected $authToken = null;

    protected $cache;

    public function init()
    {
        parent::init();
        $this->hhSettings = Zend_Registry::get('config')->vacancy->hh;
    }

    public function getAuthToken()
    {
        if (!$this->authToken) {
            $default = Zend_Registry::get('session_namespace_default');
            $this->authToken = $default->hhToken->access_token;

            if ($default->hhToken->expire - time() < 10) {
                //refresh token
                $baseUrl = $this->getUri();
                $this->setUri('https://m.hh.ru');

                $path  = '/oauth/token';
                $query = array(
                    'grant_type'     => 'refresh_token',
                    'refresh_token'  => $default->hhToken->refresh_token
                );

                $token = $this->sendPostRequest($path, $query);
                $this->setAuthToken($token);
                $this->authToken = $token->access_token;
                $this->setUri($baseUrl);
            }
        }

        return $this->authToken;
    }

    public function setAuthToken($authToken)
    {
        $authToken->expire = time() + $authToken->expires_in;
        $default = Zend_Registry::get('session_namespace_default');
        $default->hhToken = $authToken;

    }

    public function __construct($uri = 'https://api.hh.ru', $version = '1', $responseBodyType = self::RESPONSE_BODY_TYPE_JSON)
    {
        parent::__construct($uri);
        $this->setVersion($version);
        $this->setResponseBodyType($responseBodyType);
    }
    
    public function getVersion() {
        return $this->version;
    }

    public function getResponseBodyType() {
        return $this->responseBodyType;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setResponseBodyType($responseBodyType) {
        $this->responseBodyType = $responseBodyType;
    }

    /**
     * 
     * @param array $query
     * @param string $version
     * @param string $responseBodyType
     * @return string
     */
    public function getSpecializations() {
        if (empty($this->cache['specialization'])) {
            $result  = $this->sendGetRequest("/specializations/", array(), true);
            $allData = parent::decode($result->getBody());
            $this->cache['specialization'] = array();
            foreach ($allData as $spec) {
                $this->cache['specialization'][$spec->id] = $spec->name;
            }

            asort($this->cache['specialization']);
            foreach ($allData as $spec) {
                $this->cache['specialization'][$spec->id] = array('name' => $spec->name, 'specializations' => array());
                foreach ($spec->specializations as $subSpec) {
                    $this->cache['specialization'][$spec->id][ 'specializations'][$subSpec->id] = $subSpec->name;
                }
            }
        }

        return $this->cache['specialization'];
    }

    private function getDictionaries($type, $getObject = false)
    {
        if (empty($this->cache['dictionaries'])) {
            $result  = $this->sendGetRequest("/dictionaries/", array(), true);
            $this->cache['dictionaries'] = parent::decode($result->getBody());
        }

        if (!isset($this->cache['dictionaries']->$type)) {
            return array();
        }

        if ($getObject) {
            return $this->cache['dictionaries']->$type;
        }

        $data = $this->cache['dictionaries']->$type;
        $return = array();
        foreach ($data as $item) {
            $return[$item->id] = $item->name;
        }
        return $return;
    }

    public function getCurrency() {
        $currencies = $this->getDictionaries('currency', true);
        $return = array();
        foreach ($currencies as $currency) {
            $return[$currency->code] = $currency->name;
        }
        return $return;
    }

    public function getWorkExperience() {
        return $this->getDictionaries('experience');
    }

    public function getVacancyTypes() {
        return $this->getDictionaries('vacancy_type');
    }

    public function getVacancyBillingTypes() {
        return $this->getDictionaries('vacancy_billing_type');
    }

    public function getEmployment() {
        return $this->getDictionaries('employment');
    }
    
    public function getSchedule() {
        return $this->getDictionaries('schedule');
    }
    
    public function getRegions() {
        $result  = $this->sendGetRequest("/areas/", array(), true);
        $data = parent::decode($result->getBody());
        
        return $data;
    }
    
    public function getCountries() {
        $result  = $this->sendGetRequest("/areas/countries/", array(), true);
        $data = parent::decode($result->getBody());
        
        return $data;
    }
    
    public function getEducationLevel() {
        return $this->getDictionaries('education_level');
    }

    public function sendGetRequest($path, array $query = null, $noAuth = false) {
        if (!$noAuth) {
            if (!$this->getAuthToken()) {
                $this->getAuth();
            }
            $this->getHttpClient()->setHeaders('Authorization', 'Bearer '.$this->getAuthToken());
        }
        return parent::sendGetRequest($path, $query);
    }
    
    public function sendPutRequest($path, $query = null) {
        $this->getHttpClient()->setHeaders('Authorization', 'Bearer '.$this->getAuthToken());
        return parent::sendPutRequest($path, $query);
    }


    public function getDepartments()
    {
        $userData = $this->getUserData();
        $result   = $this->sendGetRequest('/employers/' . $userData->employer->id . '/departments');
        $data   = parent::decode($result->getBody());
        $return = array();
        foreach ($data->items as $item) {
            $return[$item->id] = $item->name;
        }
        return $return;
    }

    public function getTests()
    {
        $userData = $this->getUserData();
        $result   = $this->sendGetRequest('/employers/' . $userData->employer->id . '/tests');
        $data   = parent::decode($result->getBody());
        $return = array();
        foreach ($data->items as $item) {
            $return[$item->id] = $item->name;
        }
        return $return;
    }

    public function getManagers()
    {
        $userData = $this->getUserData();
        $result   = $this->sendGetRequest('/employers/' . $userData->employer->id . '/managers');
        $data   = parent::decode($result->getBody());
        $return = array();
        foreach ($data->items as $item) {
            $return[$item->id] = $item->name;
        }
        return $return;
    }

    public function getUserData()
    {
        if (!$this->cache['userData']) {
            $result = $this->sendGetRequest('/me');
            $this->cache['userData'] = parent::decode($result->getBody());
        }

        return $this->cache['userData'];
    }

    /**
     * @param bool $redirectUri
     * @return bool
     */
    public function getAuth($redirectUri = false)
    {
        if (!$redirectUri) {
            $redirectUri = Zend_Registry::get('view')->serverUrl() . Zend_Registry::get('view')->url();
        }
        $hhSettings = Zend_Registry::get('config')->vacancy->hh;

        if ($this->getAuthToken()) {
            $result = $this->sendGetRequest('/me');
            if ($result && $result->getStatus() == 200) {
                return true;
            }
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($authCode = $request->getParam('code')) {
            $baseUrl = $this->getUri();
            $this->setUri('https://m.hh.ru');

            $path  = '/oauth/token';
            $query = array(
                'grant_type'     => 'authorization_code',
                'client_id'      => $hhSettings->api->client_id,
                'client_secret'  => $hhSettings->api->client_secret,
                'code'           => $authCode,
                'redirect_uri'   => $redirectUri
            );

            $token     = $this->sendPostRequest($path, $query);
            $tokenData = parent::decode($token->getBody());
            if ($tokenData->error == 'invalid_grant') {
                Header('Location: ' . $redirectUri);
                exit();

            }

            $this->setUri($baseUrl);
            if ($tokenData && $tokenData->access_token) {
                $this->setAuthToken($tokenData);
                $userData = $this->getUserData();
                if ($userData->is_employer) {
                    return true;
                } else {
                    $redirectUrl = 'https://m.hh.ru/oauth/authorize?response_type=code&force_login=true&client_id='
                        . $hhSettings->api->client_id . '&redirect_uri='.$redirectUri;

                    Header('Location: ' . $redirectUrl);
                    exit();
                }
            }

            //Exception!!!
            exit();

        } else {
            $redirectUrl = 'https://m.hh.ru/oauth/authorize?response_type=code&client_id='
                . $hhSettings->api->client_id . '&redirect_uri='.$redirectUri;

            Header('Location: ' . $redirectUrl);
            exit();
        }
    }

    public function getEmployerId()
    {
        $userData = $this->getUserData();
        return $userData->employer->id;
    }

    public function getUserId()
    {
        $userData = $this->getUserData();
        return $userData->id;
    }

    public function archiveVacancy($vacancyId) {
        $result = $this->sendPutRequest('/employers/'.$this->getEmployerId().'/vacancies/archived/'.$vacancyId);
        if ($result->getStatus() != 403) {
            return true;
        }
        return false;
    }

    public function createVacancy(array $postData)
    {
        $postArray = array(
            "description"               => $postData['description'],
            "name"                      => $postData['name'],
            "area"                      => array('id' => $postData['area'][0]),
            "type"                      => array('id' => $postData['type']),
            "employer"                  => array('id' => $this->getEmployerId()),
            "salary"                    => array(
                'from'     => $postData['compensationFrom'] ? (int) $postData['compensationFrom'] : null,
                'to'       => $postData['compensationTo']   ? (int) $postData['compensationTo']   : null,
                'currency' => $postData['currency'],
            ),
            "response_letter_required"  => $postData['response_letter_required'] ? true : false,
            "accept_handicapped"        => $postData['accept_handicapped'] ? true : false,
            "notify"                    => $postData['notify'] ? true : false,
            "billing_type"              => array('id' => $postData['billing_type']),
            "site"                      => array('id' => 'hh'),
            "manager"                   => array('id' => $postData['manager']),
            "response_notifications"    => false
        );


        if ($postData['experience']) {
            $postArray["experience"] = array('id' => $postData['experience']);
        }
        if ($postData['schedule']) {
            $postArray["schedule"] = array('id' => $postData['schedule']);
        }
        if ($postData['employment']) {
            $postArray["employment"] = array('id' => $postData['employment']);
        }


        if ($postData['test']) {
            $postArray["test"] = array(
            'id' =>  $postData['test'],
            'required' => $postData['testSolutionRequired'] ? true : false);

        }
        if ($postData['specializations']) {
            $postArray['specializations'] = array();
            foreach ($postData['specializations'] as $specId) {
                if (strpos($specId, '.')) {
                    $postArray['specializations'][] = array('id' => $specId);
                }
            }
        }

        $baseUri = $this->getUri();
        $this->setUri('https://api.hh.ru');
        $result = $this->sendPostRequest('/vacancies', json_encode($postArray));
        $this->setUri($baseUri);

        if ($result->getStatus() == 201) {
            $vacancyId = preg_replace('/\/vacancies\/(\d+)/i', '${1}', $result->getHeader('Location'));
            return $vacancyId;
        }

        $body = parent::decode($result->getBody());

        $error_fields = array();
        //TODO обработка ошибок
        if ($body->errors) {
            foreach($body->errors as $error) {
                if ($error->type == 'vacancies') {
                    $msg = $error->value;
                } elseif (!empty($postArray[$error->value])) {
                    $error_fields[] = $error->value;
                }
            }
        }
        return array('error_field' => $error_fields, 'message' => $msg);
    }

    public function findDependentVacancy(\HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        
    }

    public function getCandidatesByVacancy(\HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        pr($this->getUserData());
        if (!$vacancyModel->hh_vacancy_id) {
            return false;
        }

        //HH: В данный момент функциональность откликов/приглашений реализована только для соискателей.
        //Для работодателей данный сервис запланирован, но еще не выпущен.
        //$responses = $this->sendGetRequest('/negotiations/', array('vacancy_id' => $vacancyModel->hh_vacancy_id));

        $hhVacancy = $this->sendGetRequest('/vacancies/'.$vacancyModel->hh_vacancy_id);
        $hhVacancy = parent::decode($hhVacancy->getBody());

        $filters = array();

        if ($hhVacancy->salary->from) {
            $filters['currency']    = $hhVacancy->salary->currency;
            $filters['salary_from'] = $hhVacancy->salary->from;
        }
        if ($hhVacancy->salary->to) {
            $filters['currency']  = $hhVacancy->salary->currency;
            $filters['salary_to'] = $hhVacancy->salary->to;
        }
        if ($hhVacancy->area && $hhVacancy->area->id) {
            $filters['area']  = $hhVacancy->area->id;
        }

        $hhResumes = $this->sendGetRequest('/resumes/', $filters);
        pr(parent::decode($hhResumes->getBody()));

        die();
    }

    public function removeVacancy() {
        
    }

    public function getCandidateResume(array $params = null) {
        
        $resume = $this->sendGetRequest('/resumes/'.$params['externalResumeId']);
        
        if(!($params['json'] == 1)){
            $resume = parent::decode($resume->getBody());
        } else {
            $resume = $resume->getBody();
        }
        return $resume;
    }

    public function getNegotiation(array $params = null)
    {
        $result = false;
        
        if($params['negotiationId']){

            Zend_Registry::get('log_system')->debug('HH STARTED');

            $negotiation = $this->sendGetRequest('/negotiations/'.$params['negotiationId']);
            $body = $negotiation->getBody();

//            $body = file_get_contents(APPLICATION_PATH . "/../data/mockups/hh/negotiations/0.txt");

            $result = parent::decode($body);
        }

        ob_start(); print_r($result); $log = ob_get_contents(); ob_end_clean();
        Zend_Registry::get('log_system')->debug('HH RESULT: ' . $log);

        return $result;
    }
        
    public function getVacancyResponse(\HM_Recruit_Vacancy_VacancyModel $vacancyModel)
    {
        if (!$vacancyModel->hh_vacancy_id) {
            return false;
        }

        Zend_Registry::get('log_system')->debug('HH STARTED');

        $page = 0;
        $items = $result = array();
        do {
            $responses = $this->sendGetRequest('/negotiations/response', array('vacancy_id' => $vacancyModel->hh_vacancy_id, 'page' => $page));
            $body = $responses->getBody();

//            $body = file_get_contents(APPLICATION_PATH . "/../data/mockups/hh/negotiations-response/{$page}.txt");

            $result = parent::decode($body);

            Zend_Registry::get('log_system')->debug(sprintf('HH PAGINATION %s OF %s', $result->page, $result->pages));

            if (!empty($result->items)) {
                $items = $result->items = array_merge($items, $result->items);
            }

        } while((++$page < 20) && !empty($result) && isset($result->page) && isset($result->pages) && ($result->page < $result->pages - 1));

        ob_start(); print_r($result); $log = ob_get_contents(); ob_end_clean();
        Zend_Registry::get('log_system')->debug('HH RESULT: ' . $log);

        return $result;
    }
    
    public function getVacancyList() {
        $userData = $this->getUserData();

        $responses = $this->sendGetRequest('/employers/' . $userData->employer->id . '/vacancies/active');
        return parent::decode($responses->getBody());
    }

    public function discardVacancyResponse(array $params = null){
        $result = false;
        if($params['negotiationId']){
            $result = $this->sendPutRequest('/negotiations/discard_by_employer/'.$params['negotiationId']);
            $result = parent::decode($result->getBody());
        }
        return $result;
    }

    public function getPhoto($resume)
    {
        $photo = false;
        if ($resume->photo && $resume->photo->medium) {
            $photo = parent::getRemoteFile($resume->photo->medium);
        }
        return $photo;
    }
}
