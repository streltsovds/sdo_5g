<?php
/**
 * Created by PhpStorm.
 * User: k1p
 * Date: 2/12/19
 * Time: 4:38 PM
 */
class HM_Proctoring_ProctoringService extends HM_Service_Abstract
{
    const ELS_ROLE_TEACHER = 0;
    const ELS_ROLE_STUDENT = 1;

    const MSG_PROCTORING_ALLOW = 'Внимание! При нажатии "Начать", запустится окно режима прокторинга (аутентификации). Убедитесь, что у вас работает камера. Если вы в первый раз выбрали не ту камеру, изменить ее можно в Настройки->Конфиденциальность и безопасность->Настройка контента->Камера';
    const MSG_PROCTORING_DENY = 'Внимание! Вы не можете приступить к занятию, не получив допуска от преподавателя';

    static function getMessages()
    {
        return array(
            0 => HM_Proctoring_ProctoringService::MSG_PROCTORING_DENY,
            1 => HM_Proctoring_ProctoringService::MSG_PROCTORING_ALLOW
        );
    }

    public function pushEvents($lessonId)//, $studentId = null)
    {
        if(empty($lessonId)) return;

        $lessonsAssignsSelect = $this->getSelect()
            ->from(
                array('sid' => 'scheduleID'),
                array(
                    'ssid' => 'sid.SSID',
                    'name' => 'lesson.title',
                    'broadcaster_id' => 'broadcaster.mid',
                    'broadcaster_name' => new Zend_Db_Expr("CONCAT(broadcaster.LastName, CONCAT(CONCAT(' ', broadcaster.FirstName), CONCAT(' ', broadcaster.Patronymic)))"),
                    'listener_id' => 'listener.mid',
                    'listener_name' => new Zend_Db_Expr("CONCAT(listener.LastName, CONCAT(CONCAT(' ', listener.FirstName), CONCAT(' ', listener.Patronymic)))"),
                )
            )
            ->joinInner(array('lesson' => 'schedule'), 'lesson.SHEID=sid.SHEID', array())
            ->joinInner(array('broadcaster' => 'People'), 'broadcaster.MID=lesson.teacher', array())
            ->joinInner(array('listener' => 'People'), 'listener.MID=sid.MID', array())
            ->where('sid.SHEID in (?)', $lessonId);
            //->where('sid.remote_event_id is null or sid.remote_event_id = 0');
/*
        if(!empty($studentId)) {
            $lessonsAssignsSelect->where('sid.MID = ?', $studentId);
        }
*/
        $lessonsAssigns = $lessonsAssignsSelect->query()->fetchAll();

        foreach ($lessonsAssigns as $exportItem) {
            $eventInfo = array(
                'id' => $lessonId,
                'name' => $exportItem['name'],
                'broadcaster' => array(
                    'id' => $exportItem['broadcaster_id'],
                    'name' => trim($exportItem['broadcaster_name'])
                ),
                'manager' => array(
                    'id' => $exportItem['broadcaster_id'],
                    'name' => trim($exportItem['broadcaster_name'])
                ),
                'users' => array(),
            );
            break;
        }

        $lesson = $this->getService('Lesson')->fetchRow(array('SHEID = ?' => $lessonId));
        if($lesson) {
            $subjectTeachers = $this->getService('Teacher')->fetchAllDependence('User', 'CID='.$lesson->CID);
            $duplicateUsersList = array();
            foreach ($lessonsAssigns as $exportItem) {
                $eventInfo['users'][] = array(
                    'id' => $exportItem['listener_id'],
                    'name' => trim($exportItem['listener_name']),
                    'can_broadcast' => true,
                    'is_watcher' => false, //in_array($exportItem['listener_id'], $subjectTeachers->getList('MID')),

                );
                $duplicateUsersList[] = $exportItem['listener_id'];
            }
// функционал тьюторов-наблюдателей отключен
//            foreach ($subjectTeachers as $teacher) {
//                if(in_array($teacher->MID, $duplicateUsersList)) continue;
//                $teacherName = $teacher->teachers ? $teacher->teachers->current()->getName() : '';
//                $eventInfo['users'][] = array(
//                    'id' => $teacher->MID,
//                    'name' => $teacherName,
//                    'is_watcher' => true,
//                );
//            }
        }
        $config = Zend_Registry::get('config');

        $proctoringServer = $config->proctoring->servers->{$lesson->proctoring_server};
        $apiHost = $proctoringServer->apiHost;
        $accessToken = $proctoringServer->accessToken;

//        $apiHost = $config->proctoring->apiHost;
//        $accessToken = $config->proctoring->accessToken;

        $url = $apiHost . "/api/event/save-many/?access-token={$accessToken}";
        $response = $this->_sendRequest($url, $eventInfo);

        if($response['id']) {
            foreach ($lessonsAssigns as $exportItem) {
                $r=$this->getService('LessonAssign')->update(array(
                    'SSID' => $exportItem['ssid'],
                    'remote_event_id' => $response['id'],
                ));
            }
        }
    }

    public function hasFailExport($lessonId)
    {
        $lessonService = $this->getService('Lesson');
        $failImports = (int) $lessonService->getSelect()
            ->from(
                array('scid' => 'scheduleID'),
                array('failCount' => new Zend_Db_Expr('count(*)'))
            )
            ->joinInner(array('sc' => 'schedule'), 'sc.SHEID = scid.SHEID', array())
            ->joinInner(array('p' => 'People'), 'p.MID = scid.MID', array())
            ->where('scid.remote_event_id is null or scid.remote_event_id = 0')
            ->where('sc.has_proctoring = 1')
            ->where('scid.SHEID = ?', $lessonId)
            ->query()->fetchColumn(0);

        return $failImports > 0;
    }

    public function isBroadcasting($MID, $lessonId)//$eventId)
    {
        $config = Zend_Registry::get('config');

        $lesson = $this->getService('Lesson')->getLesson($lessonId);
        $proctoringServer = $config->proctoring->servers->{$lesson->proctoring_server};
        $apiHost = $proctoringServer->apiHost;
        $accessToken = $proctoringServer->accessToken;

//        $apiHost = $config->proctoring->apiHost;
//        $accessToken = $config->proctoring->accessToken;
        $url = $apiHost . "/api/event/is-broadcasting/{$lessonId}/?user_id={$MID}&access-token={$accessToken}";
        return $this->_sendRequest($url);
    }

    public function getUserIdsByBroadcast($lessonId, $isBroadcast)
    {
        $config = Zend_Registry::get('config');

        $lesson = $this->getService('Lesson')->getLesson($lessonId);
        $proctoringServer = $config->proctoring->servers->{$lesson->proctoring_server};
        $apiHost = $proctoringServer->apiHost;
        $accessToken = $proctoringServer->accessToken;

//        $apiHost = $config->proctoring->apiHost;
//        $accessToken = $config->proctoring->accessToken;
        $url = $apiHost . "/api/event/get-user-external-ids-by-broadcast/{$lessonId}/?access-token={$accessToken}&is_broadcast={$isBroadcast}";
        return $this->_sendRequest($url);
    }

    public function deleteEvents($lessonId, $studentIds = null)
    {
        if(empty($lessonId)) return;

        $config = Zend_Registry::get('config');
        $apiHost = $config->proctoring->apiHost;
        $accessToken = $config->proctoring->accessToken;
        $url = $apiHost . "/api/event/delete-many/?access-token={$accessToken}";

        $lessonAssignsSelect = $this->getSelect()
            ->from(array('sid' => 'scheduleID'), array('sid.remote_event_id'))
            ->where('sid.SHEID in (?)', $lessonId)
            ->where('sid.remote_event_id is not null');

        $studentIds = array_filter((array) $studentIds);

        if(count($studentIds)) {
            $lessonAssignsSelect->where('sid.MID IN(?)', $studentIds);
        }

        $lessonAssigns = $lessonAssignsSelect->query()->fetchAll();
        $eventsIds = array();

        foreach ($lessonAssigns as $lessonAssign) {
            $eventsIds[] = $lessonAssign['remote_event_id'];
        }

        $this->_sendRequest($url, $eventsIds);
    }

    private function _sendRequest($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));

        if(!is_null($data)) {
            $data_string = json_encode($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        $output = curl_exec($curl);
        curl_close($curl);

        if (0) {
            $log = Zend_Registry::get('log_security');
            $log->log('PROCTORING URL: ' . $url, Zend_Log::DEBUG);
            $log->log(sprintf('PROCTORING REQUEST: %s', serialize($data)), Zend_Log::DEBUG);
            $log->log(sprintf('PROCTORING RESPONSE: %s', $output), Zend_Log::DEBUG);
        }

        return json_decode($output, true);
    }

    public function generateSign($lessonId, array $data){
        $config = Zend_Registry::get('config');

        $lesson = $this->getService('Lesson')->getLesson($lessonId);
        $proctoringServer = $config->proctoring->servers->{$lesson->proctoring_server};
        $secretKey = $proctoringServer->secretKey;
        $hashAlgo = $proctoringServer->hashAlgo;

//        $secretKey = $config->proctoring->secretKey;
//        $hashAlgo = $config->proctoring->hashAlgo;

        ksort($data);
        $hashString = implode('', $data) . $secretKey;
        $sign = hash($hashAlgo, $hashString);

        return $sign;
    }

    public function getEventUrl(
//        $eventId,
        $lessonId,
        $elsRole,
        $userId = null,
        $privateToElsUserId = null,
        $massWatchUserIds = null,
        $additionalParams = array()
    ) {
        $config = Zend_Registry::get('config');

        $lesson = $this->getService('Lesson')->getLesson($lessonId);
        $proctoringServer = $config->proctoring->servers->{$lesson->proctoring_server};
        $apiHost = $proctoringServer->apiHost;
        $appKey = $proctoringServer->appKey;

//        $apiHost = $config->proctoring->apiHost;
//        $appKey = $config->proctoring->appKey;

        $userId = $userId ? $userId : $this->getService('User')->getCurrentUserId();

        $data = array(
            'user_id' => $userId,
//            'event_id' => $eventId,
            'external_event_id' => $lessonId,
            'app_key' => $appKey,
            'els_role' => $elsRole,
        );

        $sign = $this->generateSign($lessonId, $data);

        $urlParams = array_merge($data, array(
            'sign' => $sign,
        ));

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ($request) {
            $domain = $request->getScheme() . '://' . $request->getHttpHost();
            $urlParams['elsApiLessonGetUrl'] = $domain . '/lesson/index/api-get';

            if ($elsRole === HM_Proctoring_ProctoringService::ELS_ROLE_TEACHER) {
                $urlParams['elsApiLessonSetUrl'] = $domain . '/lesson/index/api-set';
            }

            $urlParams['elsApiProctoringPhotoChangeUrl'] = $domain . '/lesson/index/api-proctoring-photo-change';
            $urlParams['elsApiProctoringFileAddUrl'] = $domain . '/lesson/index/api-proctoring-file-add';
        }

        if ($privateToElsUserId) {
            $urlParams['privateToExternalUserId'] = $privateToElsUserId;
        }
        if ($massWatchUserIds) {
            $urlParams['massWatchIds'] = $massWatchUserIds;
        }

        $urlParams = array_replace($urlParams, $additionalParams);

        $url = $apiHost . '/site/auth/external/?'. http_build_query($urlParams);

        return $url;
    }

    public function isValidBrowser()
    {
        require_once APPLICATION_PATH . '/../library/sinergi/SinergiBrowser.php';
        $allowedBrowsersFromConfig = Zend_Registry::get('config')->proctoring->allowedBrowsers;
        if(!is_object($allowedBrowsersFromConfig)) {
            throw new Exception('Missing proctoring allowed browses in config file');
        }

        $browsers = $allowedBrowsersFromConfig->toArray();
        $browserDetector = new SinergiBrowser();
        $currentBrowserName = $browserDetector->getName();
        $currentBrowserVersion = $browserDetector->getVersion();
        // Выбираем major-версию из строки формата "101.0.4951.67"
        list($currentBrowserVersion) = explode('.', $currentBrowserVersion);

        $isValid = false;

        $browserInfo = $browsers[$currentBrowserName];
        if (!empty($browserInfo) and
            $currentBrowserVersion >= $browserInfo['min']
        ) {
            $isValid = true;
        }

        return $isValid;
    }

    public function getInvalidBrowserMessage()
    {
        require_once APPLICATION_PATH . '/../library/sinergi/SinergiBrowser.php';
        $browsers = Zend_Registry::get('config')->proctoring->allowedBrowsers->toArray();
        $browsersMessageItems = array();

        foreach ($browsers as $browserName => $browserVersions) {
            $minVersion = $browserVersions['min'];
            $maxVersion = $browserVersions['max'];
            $browsersMessageItems[] = "{$browserName} (версий {$minVersion} - {$maxVersion})";
        }

        $browserDetector = new SinergiBrowser();
        $currentBrowserName = $browserDetector->getName();
        $currentBrowserVersion = $browserDetector->getVersion();

        $message = 'Поддерживаемые браузеры: ' . join(', ', $browsersMessageItems) . '. ' .
            "У вас установлен {$currentBrowserName} {$currentBrowserVersion}.
            Вы не сможете пройти занятие в режиме прокторинга (аутентификации). 
            Установите поддерживаемый браузер и запустите занятие в нем.";

        return $message;
    }

}
