<?php

/**
 * Description of Hh
 *
 * @author tutrinov
 */
class HM_Recruit_RecruitingServices_Curl_Hh extends HM_Recruit_RecruitingServices_Curl_Abstract {

    const CACHE_NAME_API = "head_hunter_data_api";

    protected $login = null;
    protected $password = null;
    protected $employerManagerId = null;
    protected $employerId = null;
    protected $vacancyType = null;
    protected $region = null;
    protected $apiCache = array();

    public function init() 
    {
        parent::init();
        
        $hhSettings = Zend_Registry::get('config');
        $hhVacancySettings = $hhSettings->vacancy->hh;
        
        if ($recruiter = Zend_Registry::get('serviceContainer')->getService('Recruiter')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Recruiter')->fetchAll(array('user_id = ?' => Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId()))
        )) {
            $recruiterSettings = $recruiter->hh_auth_data ? HM_HeadHunter::decript($recruiter->hh_auth_data) : false;
        }
        
        if (!isset($hhSettings->vacancy->hh->curl)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('CURL conenction settings for HeadHunter is not defined!');
        }
        $config = $hhSettings->vacancy->hh->curl;
        foreach ($config as $configName => $configValue) {
            curl_setopt($this->getCurl(), constant('CURLOPT_' . strtoupper($configName)), $configValue);
        }
        $hhVacancySettingsLogin = $recruiterSettings ? $recruiterSettings['hh_email'] : $hhVacancySettings->login;
        if (empty($hhVacancySettingsLogin)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site login must be set on vacancy config!');
        }
        $this->setLogin($hhVacancySettingsLogin);
        
        $hhVacancySettingsPassword = $recruiterSettings ? $recruiterSettings['hh_password'] : $hhVacancySettings->password;
        if (empty($hhVacancySettingsPassword)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site password must be set on vacancy config!');
        }
        $this->setPassword($hhVacancySettingsPassword);
        
        $hhVacancySettingsManagerId = $recruiterSettings ? $recruiterSettings['hh_managerId'] : $hhVacancySettings->employerManagerId;
        if (empty($hhVacancySettingsManagerId)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site employer manager ID must be set on vacancy config!');
        }
        $this->setEmployerManagerId($hhVacancySettingsManagerId);
        
        $hhVacancySettingsRegion = $recruiterSettings ? $recruiterSettings['hh_region'] : $hhVacancySettings->region;
        if (empty($hhVacancySettingsRegion)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site region must be set on vacancy config!');
        }
        $this->setRegion($hhVacancySettingsRegion);
        
        if (!isset($hhVacancySettings->employerId)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site login must be set on vacancy config!');
        }
        $this->setEmployerId($hhVacancySettings->employerId);

        if (!isset($hhVacancySettings->vacancyType)) {
            throw new HM_Recruit_RecruitingServices_Exception_InvalidConfig('HeadHunter site vacancy type must be set on vacancy config!');
        }
        $this->setVacancyType($hhVacancySettings->vacancyType);

        $this->loadApiCache();
    }

    public function getRegion() {
        return $this->region;
    }

    public function setRegion($region) {
        $this->region = $region;
    }

    public function getLogin() {
        return $this->login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getEmployerManagerId() {
        return $this->employerManagerId;
    }

    public function getEmployerId() {
        return $this->employerId;
    }

    public function getVacancyType() {
        return $this->vacancyType;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function setEmployerManagerId($employerManagerId) {
        $this->employerManagerId = $employerManagerId;
    }

    public function setEmployerId($employerId) {
        $this->employerId = $employerId;
    }

    public function setVacancyType($vacancyType) {
        $this->vacancyType = $vacancyType;
    }

    public function cache($key, $value = null) {
        if (null === $value) {
            if (is_array($this->apiCache) && array_key_exists($key, $this->apiCache)) {
                return $this->apiCache[$key];
            }
            return null;
        }
        $this->apiCache[$key] = $value;
        return;
    }

    public function getVacancySpecializationFields() {
        if (null === $this->cache('vacancySpecializationFields')) {
            $recruitingServicefactory = Zend_Registry::get('serviceContainer')->getService('RecruitServiceFactory');
            /* @var $restHHService HM_Recruit_RecruitingServices_Rest_Hh */
            $restHHService = $this->getHhRestService();
            $specializationFields = $restHHService->getSpecializationFields();
            $fields = json_decode($specializationFields, true);
            $index = array(0 => '');

            foreach ($fields as $field) {
                if ($field['id']) {
                    $index[$field['id']] = $field['name'];
                }
            }
            
            asort($index);

            $this->cache('vacancySpecializationFields', $index);
            $this->saveApiCache();
        }
        $cachedResult = $this->cache('vacancySpecializationFields');
        return $cachedResult;
    }

    public function getVacancySpecializationList() {
        if (null === $this->cache('vacancySpecializationList')) {
            $restHHService = $this->getHhRestService();
            $specializationList = $restHHService->getSpecializationList();
            $specializationList = str_replace("\\", "/", $specializationList);
            $fields = json_decode($specializationList, true);
            $index = array();
            foreach ($fields as $field) {
                $index[$field['id']] = $field;
            }
            
            asort($index);

            $this->cache('vacancySpecializationList', $index);
            $this->saveApiCache();
        }
        return $this->cache('vacancySpecializationList');
    }

    public function getCurrency($isSimple = true) {
        if (null === $this->cache('hhCurrency')) {
            $restHHService = $this->getHhRestService();
            $currency = $restHHService->getCurrency();
            $data = json_decode($currency, true);
            $indexSimple = array();
            $index = array();

            foreach ($data as $item) {
                $indexSimple[$item['code']] = $item['name'];
                $index[$item['code']] = array(
                    'name' => $item['name'],
                    'rate' => $item['rate']
                );
            }

            $this->cache('hhCurrency', array(
                'full' => $index,
                'simple' => $indexSimple
            ));
            $this->saveApiCache();
        }
        $currency = $this->cache('hhCurrency');
        return $currency[$isSimple ? 'simple' : 'full'];
    }

    public function getWorkExperience() {
        if (null === $this->cache('hhWorkExperience')) {
            $restHHService = $this->getHhRestService();
            $we = $restHHService->getWorkExperience();
            $data = json_decode($we, true);
            $index = array();

            foreach ($data as $item) {
                $index[$item['id']] = $item['name'];
            }

            $this->cache('hhWorkExperience', $index);
            $this->saveApiCache();
        }
        return $this->cache('hhWorkExperience');
    }

    public function getSchedule() {
        if (null === $this->cache('hhSchedule')) {
            $restHHService = $this->getHhRestService();
            $shedule = $restHHService->getSchedule();
            $data = json_decode($shedule, true);
            $indexSimple = array();

            foreach ($data as $item) {
                $indexSimple[$item['id']] = $item['name'];
            }

            $this->cache('hhSchedule', $indexSimple);
            $this->saveApiCache();
        }
        return $this->cache('hhSchedule');
    }

    public function getEmployment() {
        if (null === $this->cache('hhEmployment')) {
            $restHHService = $this->getHhRestService();
            $empl = $restHHService->getEmployment();
            $data = json_decode($empl, true);
            $indexSimple = array();

            foreach ($data as $item) {
                $indexSimple[$item['id']] = $item['name'];
            }

            $this->cache('hhEmployment', $indexSimple);
            $this->saveApiCache();
        }
        return $this->cache('hhEmployment');
    }

    public function archiveVacancy($vacancyId) {
        
    }

    public function createVacancy(array $postData) 
    {
        $this->sendChangeRegionRequest(); // используем всегда hh.ru

        if (!$this->getInfo('regionSuccessChanged')) {
            throw new HM_Recruit_RecruitingServices_Exception_Runtime('Не удалось установить требуемый регион!');
        }

        $this->sendAuthRequest();

        if (!$this->getInfo('logged')) {
            throw new HM_Recruit_RecruitingServices_Exception_Runtime('Не удалось авторизоваться!');
        }

        $postData = $this->_appendStaticHHPostValues($postData);
        $postData = $this->_appendDynamicHHPostValues($postData);

        $specializationIds = array_slice($postData['specializationIds'], 0, 6); // hh принимает не больше 6
        unset($postData['specializationIds']);
        $postStr = http_build_query($postData);
        foreach ($specializationIds as $id) {
            $postStr .= "&specializationIds={$id}";
        }

        $ch = $this->getCurl();
        curl_setopt($ch, CURLOPT_URL, $this->getVacancyCreateUrl());
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postStr);

        $resultHtml = $this->execRequest();

        curl_setopt($ch, CURLOPT_POST, FALSE);

        $info = $this->getInfo();

        $resultUrl = $info['url'];

        //http://hh.ru/vacancy/7533251?message=add_change
        $pattern = '/https?:\/\/hh\.ru\/vacancy\/(\d+)(\?.*)/';
        if (!preg_match($pattern, $resultUrl, $matches)) {
            if (preg_match("/b-attention m-attention_bad\">([^<]*)</", $this->getLastResult(), $matches)) {
                throw new Exception($matches[1]);
            }
            throw new Exception(_('Произошла ошибка при создании вакансии'));
        }

        return (int) $matches[1];
    }
    
    protected function _appendStaticHHPostValues($values)
    {
        return $values + array(
            'addressId' => '', // Показывать кандидатам адрес офиса при просмотре вакансии
            'city' => 0, // используется при contact=1
            'comment' => '', // ???
            'companyName' => '', // ???
            'companyUrl' => '', // ???
            'contact' => 'false', // Контактная информация - не указывать
            'country' => 7, // используется при contact=1
            'email' => '', // используется при contact=1
            'employerId' => $this->getEmployerId(),
            'employerManagerId' => $this->getEmployerManagerId(),
            'fio' => '', // используется при contact=1
            'ignoreDuplicates' => '', // ???
            'jobSite' => 'HH', 
            'metallic' => 2, // ???
            'multi' => 'false', // в одном городе
            'newPreview' => 'true', // ???
            'number' => 'true', // используется при contact=1
            'pageTemplate' => '', // обычное оформление
            'publishVacancyAction.x' => 'Разместить вакансию', 
            'region' => '', 
            'structureName' => '', // возможно, используется при других значениях pageTemplate
            'substituteEmployerManager' => $this->getEmployerManagerId(), // ???
            'tempMetallic' => $this->getVacancyType(), 
            'templateName' => '', // ??? 
            'userTestId' => '', // ??? 
            'vacancyCode' => '', // ??? 
        );
    }

    public function getVacancyCreateUrl()
    {
        return "http://hh.ru/employer/vacancy/create";
    }

    public function getVacancyCreatePageDOM()
    {
        $ch = $this->getCurl();

        curl_setopt($ch, CURLOPT_URL, $this->getVacancyCreateUrl());

        $this->execRequest();

        $dom = new Zend_Dom_Query();
        $dom->setDocumentHtml($this->lastResult);

        return $dom;

    }

    protected function _appendDynamicHHPostValues($values)
    {
        $createPageDom = $this->getVacancyCreatePageDOM();
        $xsrfField = $createPageDom->query('form input[name="_xsrf"]')->current();

        if (!$xsrfField) {
            return;
        }

        $values['_xsrf'] = $xsrfField->getAttribute('value');

        return $values;

    }
    
    public function getDepartments() 
    {
        $this->sendChangeRegionRequest();
        $this->sendAuthRequest();
        $this->executeWithParams(array(
            CURLOPT_POST => false,
            CURLOPT_HEADER => 1,
            CURLOPT_URL => $this->getVacancyCreateUrl()
        ));
        $htmlRaw = $this->getLastResult();
        $dom = new Zend_Dom_Query();
        $dom->setDocumentHtml($htmlRaw);
        $departmentsSelectNodes = $dom->query("select[name=\"departmentCode\"]");
        $departmentCollection = new HM_Recruit_RecruitingServices_Entity_Hh_DepartmentList();
        if ($departmentsSelectNodes->count() > 0) {
//             $departmentValidator = new HM_Recruit_RecruitingServices_Entity_Validator_Hh_Department();
//             $departmentCollection->getEventDispatcher()->connect(
//                     HM_Recruit_RecruitingServices_Entity_Hh_DepartmentList::EVENT_DEPARMENT_ADD_PRE,
//                     $departmentValidator->hhVacancyDepartmentClassChecker()
//             );
            /* @var $departmentsSelectItem DOMNode */
            $departmentSelectItem = $departmentsSelectNodes->current();
            $departmentValueNodes = $departmentSelectItem->childNodes;
            foreach ($departmentValueNodes as $departmentNode) {
                $departmentInstance = new HM_Recruit_RecruitingServices_Entity_Hh_Department;
                $departmentInstance->setName($departmentNode->nodeValue);
                $departmentInstance->setValue($departmentNode->getAttribute('value'));
                $departmentCollection->add($departmentInstance);
            }
            return $departmentCollection;
        }
        return $departmentCollection;
    }

    public function findDependentVacancy(HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        
    }

    public function getCandidatesByVacancy(HM_Recruit_Vacancy_VacancyModel $vacancyModel) {
        $logged = false;
        $this->sendChangeRegionRequest($this->getRegion());
        while (!$logged) {
            curl_setopt($this->getCurl(), CURLOPT_HEADER, true);
            curl_setopt($this->getCurl(), CURLOPT_URL, 'http://hh.ru/employer/vacancyresponses.mvc?vacancyId=' . ($vacancyModel->hh_vacancy_id) . '&showAll=true&page=0');
            $this->execRequest();
            curl_setopt($this->getCurl(), CURLOPT_HEADER, false);
            if (!$this->getInfo('logged')) {
                $this->sendAuthRequest();
                continue;
            }
            $logged = true;
        }

        // выдираем таблицу со списком заявок
        // этот шаблон постоянно меняется 
        
        // $pattern = '/<table.*?class=".*?HH-Stickies-Dependant[^>]*>(.*)<\/table>/su';
        $pattern = "table.vacancy-responses__list tr";
        
        $dom = new Zend_Dom_Query;
        $dom->setDocumentHtml($str = $this->getLastResult());
//         exit($str);
        $tableRows = $dom->query($pattern);
        $result = array();
        if ($tableRows->count() > 0) {
            /* @var $row DOMElement */
            foreach ($tableRows as $row) {

                $childTds = $row->getElementsByTagName("td");
                $imageColumn = $childTds->item(0);
                $desciptionColumn = $childTds->item(1);
                $resumeHref = $desciptionColumn->childNodes->item(1)->getElementsBytagName("a")->item(0)->getAttribute('href');
                preg_match("/^.*resumeId=([\d]+)&.*$/ui", $resumeHref, $matches);

                $resumeHash = $row->getAttribute("data-hh-resume-hash");
                $resumeId = intval($matches[1]);

                $imageFirstLevelDiv = $imageColumn->childNodes->item(0);
                $resultDescriptionDom = new DOMDocument;
                $photoNode = $resultDescriptionDom->createElement("div");

                /**
                 * photo node handling
                 */
                if ($imageFirstLevelDiv->getAttribute('class') == 'output__photo-after') {
                    $photoNode->setAttribute('class', 'hm-hh-candidate-photo hm-hh-candidate-photo-blank');
                } else {
                    $photoNode->setAttribute('class', 'hm-hh-candidate-photo');

                    $photoANodeHref = $imageFirstLevelDiv->getElementsByTagName("a")->item(0)->getAttribute('href');
                    $photoANodeClass = $imageFirstLevelDiv->getElementsByTagName("a")->item(0)->getAttribute('class');
                    $photoANodeTitle = $imageFirstLevelDiv->getElementsByTagName("a")->item(0)->getAttribute('title');

                    $photoImgNodeSrc = $imageFirstLevelDiv->getElementsByTagName("img")->item(0)->getAttribute('src');
                    $photoImgNodeClass = $imageFirstLevelDiv->getElementsByTagName("img")->item(0)->getAttribute('class');

                    $photoImageANode = $resultDescriptionDom->createElement("a");
                    $photoImageANode->setAttribute('href', 'http://hh.ru' . $photoANodeHref);
                    $photoImageANode->setAttribute('class', $photoANodeClass);
                    $photoImageANode->setAttribute('title', $photoANodeTitle);

                    $photoImageImgNode = $resultDescriptionDom->createElement('img');
                    $photoImageImgNode->setAttribute("src", 'http://hh.ru' . $photoImgNodeSrc);
                    $photoImageImgNode->setAttribute("class", $photoImgNodeClass);

                    $photoImageANode->appendChild($photoImageImgNode);
                    $photoNode->appendChild($photoImageANode);
                }

                /**
                 * description node handling
                 */
                $outInfoNode = $desciptionColumn->childNodes->item(0);
                $mainInfoNode = $desciptionColumn->childNodes->item(1);
                $descriptionNode = $resultDescriptionDom->createElement("div");
                $descriptionNode->setAttribute('class', 'hm-hh-candidate-description');
                $descriptionNode->appendChild($resultDescriptionDom->importNode($outInfoNode, true));
                $descriptionNode->appendChild($resultDescriptionDom->importNode($mainInfoNode, true));

                // hide native checkboxes
                $checkboxes = $descriptionNode->getElementsByTagName("input");
                foreach($checkboxes as $checkbox) {
                    $checkbox->setAttribute('class', 'hm-hh-candidate-description-checkbox');
                }
                
                // override relative urls
                $hrefs = $descriptionNode->getElementsByTagName("a");
                foreach($hrefs as $href) {
                    $url = $href->getAttribute('href');
                    $href->setAttribute('href', 'http://hh.ru' . $url);
                    $href->setAttribute('target', '_blank');
                }

                $resultDescriptionDom->appendChild($photoNode);
                $resultDescriptionDom->appendChild($descriptionNode);
                $resultDescriptionOutput = $resultDescriptionDom->saveHTML();

                $checkboxes = $mainInfoNode->getElementsByTagName("input");
                $fullName = trim($mainInfoNode->getElementsByTagName('span')->item(0)->childNodes->item(0)->nodeValue);

                $ageNodeValue = $outInfoNode->childNodes->item(1)->nodeValue;
                preg_match("/^[^\d]*([\d]+)[^\d]*$/ui", $ageNodeValue, $matches);

                $result[$resumeId] = array(
                    'resumeId' => $resumeId,
                    'fullName' => $fullName,
                    'age' => intval($matches[1]),
                    'resumeHash' => $resumeHash,
                    'description' => $resultDescriptionOutput,
                    'response' => ($checkboxes->length > 0) ? $checkboxes->item(0)->getAttribute('value') : null
                );
            }
        }
        return $result;
    }

    public function removeVacancy() {
        
    }

    public function execRequest() 
    {
        parent::execRequest();
        $info = $this->getInfo();
        $info['logged'] = false;
        $info['user_id'] = null;
        $pattern = '/<li class="usermenu-item m-clientid">\([^\d\)]*(\d+)\)<\/li>/';
        if (preg_match($pattern, $this->getLastResult(), $matches)) {
            $info['user_id'] = $matches[1];
            $info['logged'] = true;
        }
        if (!$info['logged']) {
            /* check header */
            $pattern = '/hhrole\=employer/';
            if (preg_match($pattern, $this->getLastResult())) {
                $info['logged'] = true;
            }
        }
        $this->setInfo($info);
        return $this;
    }

    protected function saveApiCache() {
        Zend_Registry::get('cache')->save($this->apiCache, self::CACHE_NAME_API);
    }

    protected function loadApiCache() {
        $this->apiCache = Zend_Registry::get('cache')->load(self::CACHE_NAME_API);
    }

    protected function sendAuthRequest() {
        if (!preg_match("/Set-Cookie:\shhrole=employer/ui", $this->getLastResult())) {
            curl_setopt($this->getCurl(), CURLOPT_URL, "http://hh.ru/login");
            $this->execRequest();
            curl_setopt($this->getCurl(), CURLOPT_HEADER, true);
            curl_setopt($this->getCurl(), CURLOPT_URL, "http://" . $this->getRegion() . "/logon.do");
            curl_setopt($this->getCurl(), CURLOPT_POST, true);
            curl_setopt($this->getCurl(), CURLOPT_POSTFIELDS, array(
                'username' => $this->getLogin(),
                'password' => $this->getPassword(),
                'action' => 'Войти'
            ));
            $this->execRequest();
        }
        curl_setopt($this->getCurl(), CURLOPT_HEADER, false);
    }

    protected function sendChangeRegionRequest($region = "hh.ru") {
        $url = "http://" . $region . "/?customDomain=1";
        curl_setopt($this->getCurl(), CURLOPT_URL, $url);
        $this->execRequest();
        $info = $this->getInfo();
        $info['regionSuccessChanged'] = false;
        if ($this->getInfo('url') == $url) {
            $info['regionSuccessChanged'] = true;
        }
        $this->setInfo($info);
        return;
    }

    public function getCandidateResume(array $params = null) {
        $logged = false;
//        $url = 'http://hh.ru/resume/print.do?id=' . $params["resumeHash"];
        $printUrl = 'http://hh.ru/resume/' . $params["resumeHash"].'?print=true';
        while (!$logged) {
            curl_setopt($this->getCurl(), CURLOPT_HEADER, true);
            curl_setopt($this->getCurl(), CURLOPT_URL, $printUrl);
            $this->execRequest();
            curl_setopt($this->getCurl(), CURLOPT_HEADER, false);
            if (!$this->getInfo('logged')) {
                $this->sendAuthRequest();
                continue;
            }
            $logged = true;
        }
        $resumeFull = $this->getLastResult();
        $resultEntity = null;

        $dom = new Zend_Dom_Query;
        $dom->setDocumentHtml($resumeFull);
        $fullNameNode = $dom->query("div.resume__personal__name");

        if ($fullNameNode->count() > 0) {
            /*@var $resultEntity HM_Recruit_RecruitingService_Entity_AbstractCandidate */
            $resultEntity = Zend_Registry::get('serviceContainer')->getService('RecruitServiceFactory')->newExternalCandidate();
            $resultEntity->setUrl($printUrl);
            $fullName = trim($fullNameNode->current()->nodeValue);

            $resultEntity->setFullName($fullName);
            $fioParts = preg_split('/\s+/', $fullName);
            switch (count($fioParts)) {
                case 3:
                    $resultEntity->setLastName($fioParts[0]);
                    $resultEntity->setFirstName($fioParts[1]);
                    $resultEntity->setPatronymic($fioParts[2]);
                    break;
                case 2:
                    $resultEntity->setLastName($fioParts[0]);
                    $resultEntity->setFirstName($fioParts[1]);
                    break;
                case 1:
                    $resultEntity->setLastName($fioParts[0]);
                    break;
            }

            $emailNode = $dom->query("div.resume__inlinelist a");
            if ($emailNode->count() > 0) {
                $resultEntity->setEmail($emailNode->current()->nodeValue);
            }

            $phoneNode = $dom->query("span.resume__contacts__phone__number");
            if ($phoneNode->count() > 0) {
                $resultEntity->setPhone($phoneNode->current()->nodeValue);
            }
            $resultEntity->setHtmlRaw($resumeFull);
        }
        return $resultEntity;
    }

    /**
     * 
     * @return HM_Recruit_RecruitingServices_Rest_Hh
     */
    protected function getHhRestService() {
        $recruitingServicefactory = Zend_Registry::get('serviceContainer')->getService('RecruitServiceFactory');
        $restHHService = $recruitingServicefactory->getRecruitingService(
                HM_Recruit_RecruitingServices_PlacementBehavior::SERVICE_HH, HM_Recruit_RecruitingServices_PlacementBehavior::API_REST);
        return $restHHService;
    }

}
