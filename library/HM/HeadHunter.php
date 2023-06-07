<?php

/**
 * Реализует работу с сайтом hh.ru
 * 
 * @author Sevastyanov Cyril
 * @since  2013-03-07
 */
class HM_HeadHunter {

    const REGION_RUSSIA = 'hh.ru';
    const REGION_SPB = 'spb.hh.ru';

    private $employerId = '63290'; //'1196144'; // ID клиента
    private $vacancyType = 'FREE'; // FREE - бесплатная, STANDART - платная

    /**
     * Данные учётной записи, под которой должен входить пользователь на hh
     */
    private $user = 'job@learnware.ru';
    private $password = 'HyperMethod';
    private $employerManagerId = '1088747';
    private $cookieFile;
    private $curl;
    private $lastResult = null;
    private $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.97 Safari/537.22';
    private static $BLOWFISH_KEY = 'yEgSn$3*l4#2bHdswQdsA';

    public static function encript($data) {
        return base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, self::$BLOWFISH_KEY, serialize($data), MCRYPT_MODE_ECB));
    }

    public static function decript($data) {
        return unserialize(mcrypt_decrypt(MCRYPT_BLOWFISH, self::$BLOWFISH_KEY, base64_decode($data), MCRYPT_MODE_ECB));
    }

    /**
     * Содержит информацию о статусе сессии на hh
     * @var array
     */
    public $connectionInfo = array(
        'region' => 'hh.ru',
        'user_id' => NULL
    );
    private $timeStart;
    private $timeName;
    public $time = 0;
    public $times = array();

    private function timeStart($name) {
        $this->timeStart = microtime(true);
        $this->timeName = $name;
    }

    private function timeEnd() {
        $delta = microtime(true) - $this->timeStart;
        $this->time += $delta;
        $this->times[] = array(
            'value' => $delta,
            'name' => $this->timeName
        );
    }

    const CACHE_NAME = 'head_hunter_data';
    const CACHE_NAME_API = 'head_hunter_data_api';

    protected $CACHE_NAME_USER;
    protected $cache = array();
    protected $cacheApi = array();

    public function __construct($employerId, $vacancyType) {
        $this->employerId = $employerId;
        $this->vacancyType = $vacancyType;

        $services = Zend_Registry::get('serviceContainer');

        $MID = $services->getService('User')->getCurrentUserId();

//        $userSelect = $services->getService('User')->getSelect();
//        $userSelect->from('recruiters', array('hh_auth_data'));
//        $userSelect->where('user_id = ?', $MID);
//        
//        $item = $userSelect->query()->fetch();
//        $hhUserData = self::decript($item['hh_auth_data']);
//        
//        // получаем учётную запись пользователя
//        $this->employerManagerId = $hhUserData['hh_managerId'];
//        $this->user              = $hhUserData['hh_email'];
//        $this->password          = $hhUserData['hh_password'];

        $this->CACHE_NAME_USER = self::CACHE_NAME . '_' . $MID;

        // файл с печенюшками =)
        $this->cookieFile = APPLICATION_PATH . '/../data/temp/hh_cookies_' . $MID . '.txt';

        // файл кэша данных, относящихся только к текущему пользователю
        if ($cache = Zend_Registry::get('cache')->load($this->CACHE_NAME_USER)) {
            $this->cache = $cache;
        }
        // файл кэша данных Api hh
        if ($cacheApi = Zend_Registry::get('cache')->load(self::CACHE_NAME_API)) {
            $this->cacheApi = $cacheApi;
        }

        $this->initCurl();
    }

    public function getLastResult() {
        return $this->lastResult;
    }

    public function setLastResult($lastResult) {
        $this->lastResult = $lastResult;
    }

    protected function saveCache() {
        Zend_Registry::get('cache')->save($this->cache, $this->CACHE_NAME_USER);
    }

    protected function saveCacheApi() {
        Zend_Registry::get('cache')->save($this->cacheApi, self::CACHE_NAME_API);
    }

    /**
     * Инициализация curl, проверка статуса авторизации и региона, при необходимости - авторизация
     * и смена региона на Россию.
     */
    protected function initCurl() {
        if ($this->curl) {
            curl_close($this->curl);
        }

        $this->log('Инициализация');

        $this->curl = $ch = curl_init();

        // настраиваем curl
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // получаем информацию о сессии
        $info = $this->getInfo();
        // если нужно, меняем регион на Россию
        if ($info['region'] !== self::REGION_RUSSIA) {
            $this->changeRegion(self::REGION_RUSSIA);

            $info = $this->getInfo();
            if ($info['region'] !== self::REGION_RUSSIA) {
                throw new Exception('Не удалось сменить регион на "Россия"');
            }
        }

        // авторизуемся, если надо
        if (!$info['logged']) {

            $this->auth();
            $info = $this->getInfo();
            if (!$info['logged']) {
                throw new Exception('Не удалось авторизоваться на сайте hh.ru');
            }
        }

        if ($info['user_id'] != $this->employerId) {
            throw new Exception('Логин/пароль не от учётной записи ID' . $this->employerId);
        }
    }

    private function throwExceptionOfLoadVacancyForm() {
        throw new Exception('Не удалось распарсить форму публикации вакансии');
    }

    /**
     * Получение информации о текущем состоянии сессии - статус авторизации, регион и т.д.
     *
     * @return array
     */
    protected function getInfo() {
        $this->log('Запрос статуса соединения');
        $return = array();

        curl_setopt($this->curl, CURLOPT_URL, 'http://hh.ru');

        $this->timeStart('Запрос статуса соединения');
        $result = curl_exec($this->curl);
        $this->setLastResult($result);
        $this->timeEnd();

        // определяем регион

        $url = curl_getinfo($this->curl, CURLINFO_EFFECTIVE_URL);
        $url = trim($url, "/");

        $region = false;

        switch ($url) {
            case 'http://hh.ru':
                $region = 'hh.ru';
                break;
            case 'http://spb.hh.ru':
                $region = 'spb.hh.ru';
                break;
        }
        $return['region'] = $region;
        $return['logged'] = false;
        if (preg_match("/Set-Cookie:\shhrole=employer/ui", $this->getLastResult())) {
            $pattern = '/<li class="usermenu-item m-clientid">\([^\d\)]*(\d+)\)<\/li>/';
            preg_match($pattern, $this->getLastResult(), $matches);
            $return['user_id'] = $matches[1];
            $return['logged'] = true;
        }


        $this->connectionInfo = $return;

        return $return;
    }

    public $log = array();

    /**
     * Временно вместо assert =)
     *
     * @param $msg
     */
    protected function log($msg) {
        $this->log[] = $msg;
    }

    protected function showLog() {
        header('Content-Type:text/plain; charset=UTF-8');
        var_dump($this->log);
        die;
    }

    /**
     * Быстрое отклонение резюме
     * 
     * /employer/negotiations/quick_discard?r=18039829&vacancyId=7280070
     */
    public function quickDiscardResume($resumeId, $vacancyId) {
        
    }

    /**
     * Множественное приглашение на собеседование
     * 
     * http://hh.ru/employer/vacancyresponses.mvc?level=INCOMING
     * 
     * vacancyId:7280070
     * page:0
     * itemsOnPage:50
     * showAll:true
     * asc:false
     * fromArchivePage:false
     * invite:Пригласить
     * response:255587276
     * response:254002495
     */
    public function multiInviteResume($vacancyId, $responses) {
        
    }

    /**
     * Множественное отклонение резюме
     * 
     * http://hh.ru/employer/vacancyresponses.mvc?level=INCOMING
     * 
     * vacancyId:7280070
     * page:0
     * itemsOnPage:50
     * showAll:true
     * asc:false
     * fromArchivePage:false
     * reject:Отказать
     * response:255587276
     * response:254002495
     */
    public function multiRejectResume($vacancyId, $responses) {
        
    }

    protected static function clearData($data) {
        return trim(preg_replace('/<[^>]*>/', '', $data));
    }

    /**
     * http://hh.ru/resume/print.do?id=52755e8300011344150000f73a6f3343475263
     * 
     * @param $resumeHash
     */
    public function getResume($resumeHash) {
        $this->log('Загрузка резюме');

        curl_setopt($this->curl, CURLOPT_URL, 'http://spb.hh.ru/resume/'.$resumeHash.'?print=true');

        $this->timeStart('Загрузка резюме');
        $resumeFull = curl_exec($this->curl);
        $this->timeEnd();

        $return = array();

        $bodyPattern = '/<body[^>]*>[^<]*<div style="margin: 20px">(.*)<\/div>[^<]*<\/body>/s';

        if (preg_match($bodyPattern, $resumeFull, $matches)) {
            $body = trim($matches[1]);

            $parts = preg_split('/<h4>(.*)<\/h4>/sU', $body, -1, PREG_SPLIT_DELIM_CAPTURE);

            $return['name'] = self::clearData($parts[0]);

            $fioParts = preg_split('/\s+/', $parts[1]);

            switch (count($fioParts)) {
                case 3:
                    $return['LastName'] = $fioParts[0];
                    $return['FirstName'] = $fioParts[1];
                    $return['Patronymic'] = $fioParts[2];
                    break;
                case 2:
                    $return['LastName'] = $fioParts[0];
                    $return['FirstName'] = $fioParts[1];
                    break;
                case 1:
                    $return['LastName'] = $fioParts[0];
                    break;
            }

            unset(
                    $parts[0], $parts[1]
            );

            // смотрим свойства резюме

            /*
              <p>
              <small>Date of birth</small>&nbsp;&nbsp;
              16.10.1979
              </p>
             */
            $pattern = '/<p>[^<]*<small>(.*):?<\/small>&nbsp;&nbsp;(.*)<\/p>/sU';

            $matches = array();

            if (preg_match_all($pattern, $parts[2], $matches)) {

                $resumeProperties = array();

                foreach ($matches[1] as $key => $name) {
                    $resumeProperties[$name] = trim($matches[2][$key]);
                }

                if (isset($resumeProperties['Date of birth']) || isset($resumeProperties['Дата рождения'])) {

                    $birthDate = isset($resumeProperties['Date of birth']) ? $resumeProperties['Date of birth'] : $resumeProperties['Дата рождения'];

                    $birthDate = DateTime::createFromFormat('d.m.Y', $birthDate);
                    $return['BirthDate'] = $birthDate ? $birthDate->format('Y-m-d') : NULL;
                } else {
                    $return['BirthDate'] = NULL;
                }

                if (isset($resumeProperties['E-mail'])) {
                    $return['EMail'] = $resumeProperties['E-mail'];
                } else {
                    $return['EMail'] = '';
                }

                if (isset($resumeProperties['Phone']) || isset($resumeProperties['Тел.'])) {
                    $phone = isset($resumeProperties['Phone']) ? $resumeProperties['Phone'] : $resumeProperties['Тел.'];
                    $return['Phone'] = trim(preg_replace('/\([^\)]+\)$/', '', $phone));
                } else {
                    $return['Phone'] = '';
                }
            }
        }

        $return['full'] = $resumeFull;

        return $return;
    }

    /**
     * Возвращает список кандидатов на вакансию
     * 
     * @param $vacancy_id
     * @return array
     */
    public function getVacancyCandidates($vacancy_id) {
        $this->log('Поиск кандидатов к вакансии');

        curl_setopt($this->curl, CURLOPT_URL, 'http://hh.ru/employer/vacancyresponses.mvc?vacancyId=' . $vacancy_id . '&showAll=true&page=0');

        $this->timeStart('Поиск кандидатов к вакансии');
        $result = curl_exec($this->curl);
        $this->timeEnd();

        // выдираем таблицу со списком заявок
        $pattern = '/<table\s+style="[^"]+"\s+class="HH-Stickies-Dependant\s+output\s+b-v-responses-list-INCOMING\s*">(.*)<\/table>/sU';

        if (preg_match($pattern, $result, $matches)) {

            $pattern = '/<tr[^>]*>(.*)<\/tr>/sU';

            // разбиваем таблицу на строки с заявками
            if (preg_match_all($pattern, $matches[1], $matches)) {

                $peoplesTDs = $matches[1];

                $pattern = '/<td([^>]*)>(.*)<\/td><td[^>]*>(.*)<\/td>/sU';
                $search = array(
                    '/<script[^>]*>.*<\/script>/sU',
                    '/<div\s+[^>]*class="output__addition"[^>]*>.*$/',
                    '/onclick="return \{types:\[\'\{jsxComponents\}\.ToggleBlock\'\], \'\{jsxComponents\}\.ToggleBlock\':\{expandClass:\'g-expand\'\}\}"/',
                    '/<input class="output__checkbox" value="\d+" name="response" type="checkbox" \/>/',
                    '/href="\/resume\?/'
                );
                $replace = array(
                    '',
                    '',
                    '',
                    '',
                    'href="http://hh.ru/resume?'
                );
                $searchPhoto = array(
                    '/<script[^>]*>.*<\/script>/sU',
                    '/href="\/photo/',
                    '/src="\/photo/',
                    '/onclick="return[^"]*"/'
                );
                $replacePhoto = array(
                    '',
                    'href="http://hh.ru/photo',
                    'src="http://hh.ru/photo',
                    ''
                );

                $result = array();

                // чистим строки от мусора и немного переверстываем
                foreach ($peoplesTDs as $peoplesTD) {

                    // сначала вытаскиваем важные данные
                    $resumeParams = array();

                    $searchPattern = '/href="\/resume\?fromResponsesPage=true&vacancyId=' . $vacancy_id . '&resumeId=(\d+)&resumeHash=([0-9a-f]{38})&page=\d+&level=INCOMING"/';

                    if (preg_match($searchPattern, $peoplesTD, $urlMatches)) {
                        $resumeParams['resumeId'] = (int) $urlMatches[1];
                        $resumeParams['resumeHash'] = $urlMatches[2];
                    }

                    $searchPattern = '/<input class="output__checkbox" value="(\d+)" name="response" type="checkbox" \/>/';

                    if (preg_match($searchPattern, $peoplesTD, $urlMatches)) {
                        $resumeParams['response'] = $urlMatches[1];
                    }

                    if (preg_match($pattern, $peoplesTD, $peopleMatches)) {

                        if ($peopleMatches[1] === ' class="output__photo-blank"') {
                            $photo = '<div class="hm-hh-candidate-photo hm-hh-candidate-photo-blank"></div>';
                        } else {
                            $photo = '<div class="hm-hh-candidate-photo">' .
                                    preg_replace($searchPhoto, $replacePhoto, $peopleMatches[2]) .
                                    '</div>';
                        }

                        $description = preg_replace($search, $replace, $peopleMatches[3]);
                        $resumeParams['description'] = $photo .
                                '<div class="hm-hh-candidate-description">' .
                                $description .
                                '</div>';
                    }

                    $result[$resumeParams['resumeId']] = $resumeParams;
                }

                return $result;
            }
        }

        return array();
    }

    /**
     * Изменяет регион
     *
     * @param string $region
     */
    protected function changeRegion($region = 'hh.ru') {
        $this->log('Смена региона');

        curl_setopt($this->curl, CURLOPT_URL, 'http://' . $region . '/?customDomain=1');

        $this->timeStart('Смена региона');
        $result = curl_exec($this->curl);
        $this->timeEnd();
    }

    /**
     * Публикует вакансию на hh.ru
     * 
     * @param $formValues
     * @return int
     * @throws Exception
     */
    public function vacancyDo($formValues) {
        // Алексей Авраменко сказал идентификатор адреса и теста не заполнять, чтобы не парсить страницу
        $formValues['addressId'] = '0';
        $formValues['userTestId'] = null;

        // дополняем постоянными полями
        $formValues['employerId'] = $this->employerId;
        $formValues['vacancyDouble'] = 'false';
        $formValues['jobSite'] = 'HH';
        $formValues['metallic'] = $this->vacancyType;
        $formValues['vacancy.vacancyId'] = '0';
        $formValues['vacancyId'] = '0';
        $formValues['multi'] = 'false';
        $formValues['employerManagerId'] = $this->employerManagerId;
        $formValues['vacancy.insiderId'] = '0';
        $formValues['templateName'] = '';
        $formValues['shared'] = 'false';
        $formValues['publishVacancyAction.x'] = 'Разместить вакансию';
        $formValues['action'] = 'заказать';
        

        $this->log('Публикация вакансии');

        $ch = $this->curl;
        curl_setopt($ch, CURLOPT_URL, "http://hh.ru/employer/vacancy.do");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);


        $this->timeStart('Публикация вакансии');
        $resultHtml = curl_exec($ch);
        $this->setLastResult($resultHtml);
        $this->timeEnd();

        curl_setopt($ch, CURLOPT_POST, FALSE);

        $info = curl_getinfo($ch);

        $resultUrl = $info['url'];

        //http://hh.ru/vacancy/7533251?message=add_change
        $pattern = '/https?:\/\/hh\.ru\/vacancy\/(\d+)(\?.*)/';
        if (!preg_match($pattern, $resultUrl, $matches)) {
            if (preg_match("/b-attention m-attention_bad\">([^<]*)</", $this->getLastResult(), $matches)) {
                throw new Exception(_($matches[1]));
            }
            throw new Exception(_('Неожиданная ситуация, возможно вакансия не была создана!'));
        }

        return (int) $matches[1];
    }

    /**
     * Отправка вакансии в архив.
     */
    public function vacancyArchive($vacancyId) {
        $formValues = array(
            'selectedVacancyes' => $vacancyId,
            'archiveSelected.x' => 'Поместить в архив'
        );

        $this->log('Отправка вакансии в архив');

        $ch = $this->curl;

        curl_setopt($ch, CURLOPT_URL, "http://hh.ru/employer/vacancies");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formValues);

        $this->timeStart('Отправка вакансии в архив');
        $resultHtml = curl_exec($ch);
        $this->timeEnd();

        curl_setopt($ch, CURLOPT_POST, FALSE);

        if (!preg_match('/Вакансия .* успешно отправлена в архив/', $resultHtml)) {
            throw new Exception(_('Неожиданная ситуация, возможно вакансия не была отправлена в архив!'));
        }
    }

    /**
     * Выполняем авторизацию на hh
     */
    protected function auth() {
        $this->log('Авторизация');

        $ch = $this->curl;

        // чтобы авторизовалось, надо обязательно авторизовываться со страницы авторизации
        curl_setopt($ch, CURLOPT_URL, "http://hh.ru/login");
        $this->timeStart('Авторизация');
        $result = curl_exec($ch);
        $this->setLastResult($result);
        $this->timeEnd();

        // выполняем авторизацию
        curl_setopt($ch, CURLOPT_URL, "https://hh.ru/logon.do");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'username' => $this->user,
            'password' => $this->password,
            'action' => 'Войти'
        ));


        $this->timeStart('Авторизация');
        $result = curl_exec($ch);
        $this->setLastResult($result);
        $this->timeEnd();

        curl_setopt($ch, CURLOPT_POST, FALSE);
    }

    protected $hhApiCurlHandler;

    protected function getHhApiCurl() {
        if ($this->hhApiCurlHandler) {
            curl_close($this->hhApiCurlHandler);
        }

        $this->hhApiCurlHandler = $ch = curl_init();

        // настраиваем curl
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        return $ch;
    }

    public function getVacancySpecializationFields() {
        if (empty($this->cacheApi['hhFields'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, "http://api.hh.ru/1/json/field/all/");

            $this->timeStart('api_fields');
            $result = curl_exec($ch);
            $this->timeEnd();

            $fields = json_decode($result, true);
            $index = array();

            foreach ($fields as $field) {
                $index[$field['id']] = $field['name'];
            }

            $this->cacheApi['hhFields'] = $index;
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhFields'];
    }

    public function getVacancySpecializationList() {
        if (empty($this->cacheApi['hhSpecializations'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, "http://api.hh.ru/1/json/specialization/all/");

            $this->timeStart('api_specializations');
            $result = curl_exec($ch);
            $this->timeEnd();

            $fields = json_decode($result, true);
            $index = array();

            foreach ($fields as $field) {
                $index[$field['id']] = array(
                    'name' => $field['name'],
                    'field' => $field['field']
                );
            }

            $this->cacheApi['hhSpecializations'] = $index;
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhSpecializations'];
    }

    public function getWorkExperience() {
        if (empty($this->cacheApi['hhWorkExperience'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, 'http://api.hh.ru/1/json/experience/');

            $this->timeStart('api_work_experience');
            $result = curl_exec($ch);
            $this->timeEnd();

            $data = json_decode($result, true);
            $index = array();

            foreach ($data as $item) {
                $index[$item['id']] = $item['name'];
            }

            $this->cacheApi['hhWorkExperience'] = $index;
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhWorkExperience'];
    }

    public function getCurrency($isSimple = true) {
        if (empty($this->cacheApi['hhCurrency'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, 'http://api.hh.ru/1/json/currency/');

            $this->timeStart('api_currency');
            $result = curl_exec($ch);
            $this->timeEnd();

            $data = json_decode($result, true);
            $indexSimple = array();
            $index = array();

            foreach ($data as $item) {
                $indexSimple[$item['code']] = $item['name'];
                $index[$item['code']] = array(
                    'name' => $item['name'],
                    'rate' => $item['rate']
                );
            }

            $this->cacheApi['hhCurrency'] = array(
                'full' => $index,
                'simple' => $indexSimple
            );
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhCurrency'][$isSimple ? 'simple' : 'full'];
    }

    public function getSchedule() {
        if (empty($this->cacheApi['hhSchedule'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, 'http://api.hh.ru/1/json/schedule/');

            $this->timeStart('api_schedule');
            $result = curl_exec($ch);
            $this->timeEnd();

            $data = json_decode($result, true);
            $indexSimple = array();

            foreach ($data as $item) {
                $indexSimple[$item['id']] = $item['name'];
            }

            $this->cacheApi['hhSchedule'] = $indexSimple;
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhSchedule'];
    }

    public function getEmployment() {
        if (empty($this->cacheApi['hhEmployment'])) {

            $ch = $this->getHhApiCurl();

            curl_setopt($ch, CURLOPT_URL, 'http://api.hh.ru/1/json/employment/');

            $this->timeStart('api_schedule');
            $result = curl_exec($ch);
            $this->timeEnd();

            $data = json_decode($result, true);
            $indexSimple = array();

            foreach ($data as $item) {
                $indexSimple[$item['id']] = $item['name'];
            }

            $this->cacheApi['hhEmployment'] = $indexSimple;
            $this->saveCacheApi();
        }

        return $this->cacheApi['hhEmployment'];
    }

}
