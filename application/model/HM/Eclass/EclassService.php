<?php
class HM_Eclass_EclassService extends HM_Service_Abstract
{
    
    public function webinarPush($webinarData)
    {
        $lesson   = $webinarData['lesson'];
        $students = $webinarData['students'];
        
        $config = Zend_Registry::get('config');
        
        $webinarHost = $config->eclass->webinarApiHost;
        $accessToken = $config->eclass->accessToken;
        
        $data = array(
            'name' => $lesson->title,
            'date' => $lesson->begin,
        );
        
        $userService = $this->getService('User');
        
        if ($lesson->teacher) {
            $teacher = $userService->find($lesson->teacher)->current();

        } else {
            $teacher = $this->getService('User')->getCurrentUser();
        }

        if ($teacher && $teacher->MID) {
            $data['manager'] = $this->_getFormattedArray($teacher);
            $data['broadcaster'] = $this->_getFormattedArray($teacher);
        }

        if (is_array($students) && count($students)) {
            $users = $userService->fetchAll(array('MID IN (?)' => $students));
            foreach($users as $user){
                $data['users'][] = $this->_getFormattedArray($user);
            }
        }

        $id = isset($lesson->webinar_event_id) ? $lesson->webinar_event_id : '0'; //$lesson->SHEID;
        $url = $webinarHost . "/api/event/save/{$id}/?access-token={$accessToken}";

        if ($webinar_event_id = $this->_curl($url, $data, 'id')) {
            $this->setSyncStatus($lesson->SHEID, true);
        } else {
            $this->setSyncStatus($lesson->SHEID, false);
        }

        $lessonValues = $this->getService('Lesson')->find($lesson->SHEID)->current()->getValues();

        if ($webinar_event_id) {
            $lessonValues['webinar_event_id'] = $webinar_event_id;
            $lessonValues = $this->getService('Lesson')->update($lessonValues);
        } 

        if (!$webinar_event_id && !$lessonValues['webinar_event_id']) { //����� ������� � �������� � ��������� �������� - �������
            $this->getService('Lesson')->delete($lesson->SHEID);
        } 

    }

    public function webinarDelete($lessonId)
    {
        $config = Zend_Registry::get('config');
        $webinarHost = $config->eclass->webinarApiHost;
        $accessToken = $config->eclass->accessToken;
        $url = $webinarHost . "/api/events/delete/{$lessonId}?access-token={$accessToken}";

        $this->_curl($url, array());
    }

    public function getWebinarVideo($lessonId)
    {
        $lesson   = $this->getService('Lesson')->find($lessonId)->current();
        $config = Zend_Registry::get('config');

        $webinarHost = $config->eclass->webinarApiHost;
        $accessToken = $config->eclass->accessToken;

        $id = isset($lesson->webinar_event_id) ? $lesson->webinar_event_id : '0';
        $url = $webinarHost . "/api/events/video/{$id}?access-token={$accessToken}";

        $result = $this->_curl($url, array());

        return $result;
    }

    public function lessonWebinarReassign($lessonId)
    {
        $lessons = $this->getService('Lesson')->fetchAllManyToMany('User', 'Assign',
        $this->getService('Lesson')->quoteInto(
            array(
                'schedule.SHEID = ?',
                ' AND schedule.all = ?',
                ' AND schedule.typeID = ?'
            ),
            array(
                $lessonId,
                '1',
                HM_Event_EventModel::TYPE_ECLASS
            )
        ));

        foreach ($lessons as $lesson) {
            $lessonUsers = !empty($lesson->users) ? $lesson->users->getList('MID') : [];

            $this->webinarPush([
                'lesson'   => $lesson,
                'students' => $lessonUsers,
            ]);
        }
    }

    public function subjectWebinarsReassign($subjectId) {

        $lessons = $this->getService('Lesson')->fetchAllManyToMany('User', 'Assign',
        $this->getService('Lesson')->quoteInto(
            array(
                'schedule.CID = ?',
                ' AND schedule.all = ?',
                ' AND schedule.typeID = ?'
            ),
            array(
                $subjectId,
                '1',
                HM_Event_EventModel::TYPE_ECLASS
            )
        ));

        foreach ($lessons as $lesson) {
            $lessonUsers = !empty($lesson->users) ? $lesson->users->getList('MID') : [];

            $this->webinarPush([
                'lesson'   => $lesson,
                'students' => $lessonUsers,
            ]);
        }
    }


    private function _getFormattedArray($user)
    {
        return array(
            'id'         => $user->MID,
            'name'       => $user->getName(),
            'avatar_url' => $user->getPhotoWithoutDefault()?Zend_Registry::get('view')->serverUrl(
                '/'. $user->getPhotoWithoutDefault()
            ):null
        );
    }
    
    
    public function setSyncStatus($lessonId, $status) {
        $webinar = $this->fetchAll(array('lesson_id = ?' => $lessonId))->current();
        $data = array(
            'lesson_id' => $lessonId,
            'synced' => $status,
        );
        if($status){
            $data['sync_date'] = date("Y-m-d H:i:s");
        }
        if($webinar->id){
            $data['id'] = $webinar->id;
            $this->update($data);
        } else {
            $this->insert($data);
        }
    }
    
    public function getSyncStatus($lessonId) {
        $webinar = $this->fetchAll(array('lesson_id = ?' => $lessonId))->current();
        
        $result = false;
        if($webinar->id && $webinar->status){
            $result = true;
        }
        return $result;
    }
    
    private function _curl($url, $data, $returnField=false)
    {
        $data_string = json_encode($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
        );
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        
        $output = curl_exec($curl);
        $httpCode = curl_getinfo(CURLINFO_HTTP_CODE);
        curl_close($curl);
 
//        if($httpCode != '200') return false;
        $output = json_decode($output);
        if($returnField) {
            return $output->$returnField;
        }

        return $output;
    }
    
    
    /**
     * Generate sign for webinar url
     * @param array $data
     * $data = array(
     *      'user_id'  => {current user id}, 
     *      'event_id' => {lesson id},
     *      'app_key'  => {app key},
     * );
     * @return string
     */
    public function generateSign(array $data)
    {
        $config = Zend_Registry::get('config');
        $secretKey = $config->eclass->secretKey;
        $hashAlgo  = $config->eclass->hashAlgo;
        
        ksort($data);
        $hashString = implode('', $data) . $secretKey;
        $sign = hash($hashAlgo, $hashString);
        
        return $sign;
    }


    public function createDefault($title, $subjectId)
    {
        return $this->insert([
            'subject_id' => $subjectId,
            'title' => $title,
        ]);
    }

    public function createLesson($subjectId, $eclassId)
    {
        // создаём новое занятие, даже если ранее уже было создано
        $eclass = $this->findOne($eclassId);
        if ($eclass) {
            $values = array(
                'title' => $eclass->title,
                'begin' => date('Y-m-d 00:00:00'),
                'end' => date('Y-m-d 23:59:00'),
                'createID' => $this->getService('User')->getCurrentUserId(),
                'createDate' => date('Y-m-d H:i:s'),
                'typeID' => HM_Event_EventModel::TYPE_ECLASS,
                'vedomost' => 0,
                'CID' => $subjectId,
                'startday' => 0,
                'stopday' => 0,
                'timetype' => 2,
                'isgroup' => 0,
                'teacher' => $this->getService('User')->getCurrentUserId(),
                'params' => 'module_id='.(int) $eclass->id.';',
                // 5G
                // продублируем в отдельное человеческое поле,
                // чтобы в будущем отказаться от "params"
                'material_id' => $eclass->id,
                'webinar_event_id' => $eclass->id,
                'all' => 1,
                'cond_sheid' => '',
                'cond_mark' => '',
                'cond_progress' => 0,
                'cond_avgbal' => 0,
                'cond_sumbal' => 0,
                'cond_operation' => 0,
                'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
            );

            $lesson = $this->getService('Lesson')->insert($values);
            $students = $lesson->getService()->getAvailableStudents($subjectId);
            if (is_array($students) && count($students)) {
                $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
            }

//[ES!!!] //array('lesson' => $lesson))
        }

        return $lesson;
    }
}