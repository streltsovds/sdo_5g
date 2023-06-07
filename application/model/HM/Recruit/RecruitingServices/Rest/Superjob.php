<?php
class HM_Recruit_RecruitingServices_Rest_Superjob extends HM_Recruit_RecruitingServices_Rest_Abstract {

    protected $version = '2.0';
    protected $superjobSettings = null;
    protected $authToken = null;
    protected $references = null;
    
    
    public function __construct($uri = 'https://api.superjob.ru', $version = '2.0') {
        parent::__construct($uri);
        $this->setVersion($version);
    }
    
    public function init() {
        parent::init();
        $this->superjobSettings = Zend_Registry::get('config')->vacancy->superjob;
    }
    
    public function getVersion() {
        return $this->version;
    }
    
    public function setVersion($version) {
        $this->version = $version;
    }
    
    public function archiveVacancy($vacancyId) {
        
    }

    public function createVacancy(array $postData) {
        $postArray = array(
            'profession'    => $postData['profession'],
            'town'          => (int)$postData['town'],
            'published'     => (int)$postData['published'],
            'catalogues'    => $postData['catalogues'],
            'firm_name'     => $postData['firm_name'],
            'firm_activity' => $postData['firm_activity'],
            'work'          => $postData['work'],
            'compensation'  => $postData['compensation'],
            'candidat'      => $postData['candidat'],
        );
        
        $result = $this->sendPostRequest('/vacancies', $postArray);
        
        if ($result->getStatus() == 201) {
            $body = json_decode($result->getBody());
            return $body->id;
        }
        
        $body = json_decode($result->getBody());

        $error_fields = array();
        //TODO обработка ошибок
        if ($body->errors) {//на 27.02.2018 не актуально, потому см. ниже
            foreach($body->errors as $error) {
                if (!empty($postArray[$error->value])) {
                    $error_fields[] = $error->value;
                }
            }
        }

        if ($body->error && $body->error->message) {
            $errors = (array)$body->error->message;
            foreach($errors as $error) {
                    $error_fields[] = is_array($error) ? implode("\n ", $error) : $error;
            }
        }
        return array('error_field' => $error_fields);
    }

    public function findDependentVacancy(\HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        
    }

    public function getCandidateResume(array $params = null){
        $result = $this->sendGetRequest('/resumes/'.$params['externalResumeId']);
        
        return json_decode($result->getBody());
    }
    
    
    public function getCandidatesByVacancy(\HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        
    }

    public function removeVacancy() {
        
    }
    
    protected function addVersionToPath($path) {
        return $path = '/' . $this->version . $path;
    }
    
    public function sendGetRequest($path, array $query = null, $noAuth = false) {
        $path = $this->addVersionToPath($path);
        
        if (!$noAuth) {
            if (!$this->getAuthToken()) {
                $this->getAuth();
            }
            $this->getHttpClient()->setHeaders('X-Api-App-Id', $this->getAppId());
            $this->getHttpClient()->setHeaders('Authorization', 'Bearer ' . $this->getAuthToken()->access_token);
        }
        
        return parent::sendGetRequest($path, $query);
    }
    
    public function sendPostRequest($path, $query = null) {
        $path = $this->addVersionToPath($path);
        $this->getHttpClient()->setHeaders('X-Api-App-Id', $this->getAppId());
        $this->getHttpClient()->setHeaders('Authorization', 'Bearer ' . $this->getAuthToken()->access_token);
        
        return parent::sendPostRequest($path, $query);
    }
    
    public function setAuthToken($authToken) {
        $authToken->expire = time() + $authToken->expires_in;
        $default = Zend_Registry::get('session_namespace_default');
        $default->superjob->authToken = $this->authToken = $authToken;

    }
    
    public function getAuth($redirectUri = false) {
        if (!$redirectUri) {
            $redirectUri = Zend_Registry::get('view')->serverUrl() . Zend_Registry::get('view')->url();
        }
        
        $superjobSettings = Zend_Registry::get('config')->vacancy->superjob;

        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($authCode = $request->getParam('code')) {
            $request->clearParams();
            
            $baseUrl = $this->getUri();

            $path  = '/oauth2/access_token';
            $query = array(
                'client_id'      => $superjobSettings->api->client_id,
                'client_secret'  => $superjobSettings->api->client_secret,
                'code'           => $authCode,
                'redirect_uri'   => $redirectUri
            );

            $token     = $this->sendPostRequest($path, $query);
            $tokenData = json_decode($token->getBody());
            if ($tokenData->error == 'invalid_grant') {
                Header('Location: ' . $redirectUri);
                exit();

            }
            
            if ($tokenData && $tokenData->access_token) {
                $this->setAuthToken($tokenData);
//                $redirectUrl = 'https://www.superjob.ru/authorize/?client_id='
//                    . $superjobSettings->api->client_id . '&redirect_uri='.$redirectUri;

                Header('Location: ' . $redirectUri);
            exit();
            }

            //Exception!!!
            exit();

        } else {
            $redirectUrl = 'https://www.superjob.ru/authorize/?client_id='
                . $superjobSettings->api->client_id . '&redirect_uri='.$redirectUri;

            Header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    public function getAuthToken() {
        $superjobSettings = Zend_Registry::get('config')->vacancy->superjob;
        
        if (!$this->authToken) {
            $default = Zend_Registry::get('session_namespace_default');
            $this->authToken = $default->superjob->authToken;

            if ($default->superjob->authToken && $default->superjob->authToken->expire - time() < 10) {
                //refresh token
                $baseUrl = $this->getUri();

                $path  = '/oauth2/refresh_token';
                $query = array(
                    'client_id'     => $superjobSettings->api->client_id,
                    'client_secret' => $superjobSettings->api->client_secret,
                    'refresh_token' => $default->superjob->authToken->refresh_token
                );

                $token = $this->sendPostRequest($path, $query);
                $this->setAuthToken($token);
                $this->authToken = $token;
                $this->setUri($baseUrl);
            }
        }

        return $this->authToken;
    }
    
    public function getAppId() {
        //@TODO exeption if not found in settings
        if(!$this->superjobSettings){
            $this->superjobSettings = Zend_Registry::get('config')->vacancy->superjob;
        }
        return $this->superjobSettings->api->client_secret;
    }    

    public function getTowns(){
        $result = $this->sendGetRequest("/towns/", array('all' => 1), true);
        return json_decode($result->getBody());
    }
    
    public function getCatalogues(){
        $result = $this->sendGetRequest("/catalogues/", array(), true);
        return json_decode($result->getBody());
    }
    
    public function getReferences(){
        if(!$this->references){
            $result = $this->sendGetRequest("/references/", array(), true);
            $this->references = json_decode($result->getBody());
        }
        return $this->references;
    }
    
    public function getVacancyResponse(\HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        if (!$vacancyModel->superjob_vacancy_id) {
            return false;
        }
        
        $responses = $this->sendGetRequest('/resumes/received/'.$vacancyModel->superjob_vacancy_id);

        return json_decode($responses->getBody());
    }
    
    public function discardVacancyResponse($resumeId){
        if (!$resumeId) {
            return false;
        }
        
        $result = $this->sendPostRequest('/hr/resumes/'.$resumeId.'/reject/');

        return json_decode($result->getBody());
    }


    public function getPhoto($resume)
    {
        $photo = false;
        if ($resume->photo_sizes && $resume->photo_sizes->medium) {
            $photo = parent::getRemoteFile($resume->photo_sizes->medium);
        }
        return $photo;
    }
}