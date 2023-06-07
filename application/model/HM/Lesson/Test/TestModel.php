<?php
/*
 * Тест
 */
class HM_Lesson_Test_TestModel extends HM_Lesson_LessonModel
{

//    const TEST_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TEST;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/5g/test.svg";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
        $path = isset($redirectUrl['path']) ? $redirectUrl['path'] : '';
        $query = isset($redirectUrl['query']) ? $redirectUrl['query'] : '';
        $redirectUrl = $path.'?'.$query;

        $params = [
            'module'     => 'quest',
            'controller' => 'lesson',
            'action'     => 'info',
            'subject_id' => $this->CID,
            'quest_id' => $this->getModuleId(),
            'lesson_id' => $this->SHEID,
            'redirect_url' => urlencode($redirectUrl),
        ];
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));
    }

    public function getQuestContext()
    {
        return array(
            'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_ELEARNING, 
            'context_event_id' => $this->SHEID
        );
    }
    
    public function getResultsUrl($options = array())
    {
        $params = array(
                          'module'     => 'lesson',
                          'controller' => 'result', 
                          'action'     => 'index',
                          'lesson_id'  => $this->SHEID,
                          'baseUrl'    => ''
                        );
        
        if ($this->CID > 0) {
            $params['subject_id'] = $this->CID;
        }
                        
        $params = (count($options))? array_merge($params,$options) : $params;
                        
        return Zend_Registry::get('view')->url($params,null,true);

    }
    
    
    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'lesson', 
                	'controller' => 'execute',  
                	'action' => 'index', 
                	'lesson_id' => $this->SHEID
                );
    }
    
    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'test', 
                	'controller' => 'abstract', 
                	'action' => 'index'
                );
    }



    public function isResultInTable()
    {
        return true;
    }
    
    public function isFreeModeEnabled()
    {
        return false;
    }

    public function isExecutable($fromQuest = false)
    {
        if (!parent::isExecutable()) {
            return false;
        }
        
        if (!$quest = Zend_Registry::get('serviceContainer')->getService('Quest')->getOne(
            Zend_Registry::get('serviceContainer')->getService('Quest')->findDependence(array('Settings', 'SubjectAssign'), $this->getModuleId())
        )) {
            throw new HM_Exception(_('Тест не найден'));
            return false;
        }
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_LESSON, $this->SHEID);
        

        //todo: Проверка на попытки!
        /** @var HM_Quest_Attempt_AttemptService $qaService */
        $qaService = Zend_Registry::get('serviceContainer')->getService('QuestAttempt');

        $attempts = $qaService->fetchAll($qaService->quoteInto(
            array('user_id=?', ' AND quest_id=?', ' AND type=?', ' AND context_event_id=?'),
            array(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
                $this->getModuleId(),
                HM_Quest_QuestModel::TYPE_TEST,
                $this->SHEID,
            )
        ),
        'attempt_id DESC'
        );


        $attemptCount = count($attempts);
        $attempts = $attempts->getList('attempt_id', 'date_begin');
        $attemptsDates = array_values($attempts);
        $attempts = array_keys($attempts);

        if ($attemptCount) {
            $lastAttemptDate = new HM_Date($attemptsDates[0]);
        }

        // Внимание! Сюда мы попадаем как до теста (не даем перейти к тесту) и во время теста (защита от хакеров), индиктор режима - $fromQuest
        // Если проверка осуществляется из запущенного теста, значит текущая попытка уже записана в базу и ее учитывать не надо
        if ($fromQuest == true && $attemptCount > 0 && $quest->limit_attempts != 0) {
            $attemptCount--;
            if (isset($attempts[1])) {// Берем предпоследнюю т.к. текущую не учитываем
                $lastAttemptDate = new HM_Date($attemptsDates[1]);
            }
        }         

        if ($quest->limit_attempts != 0 && $attemptCount >= $quest->limit_attempts) {

            $now = new HM_Date(date('Y-m-d H:i:s'));
            $diffSeconds = $now->sub($lastAttemptDate)->toValue();
            $limitSeconds = $quest->limit_clean*3600*24;

            //Делим все попытки на серии по limit_attempts штук. Если авторазблокировка есть, то ограничиваем только на последней попытке в серии 
            //и то при том, что время после последней попытки еще не прошло достаточно много (limit_clean)
            $bCanGo = $quest->limit_clean && (($attemptCount % $quest->limit_attempts)!=0 || ($diffSeconds>$limitSeconds));
            if (!$bCanGo /*&& !$fromQuest*/) {

                if ($fromQuest) { //В тест как-то попали, нечестно, а прав на это нету - удаляем текущую попытку, чтобы не нарушать алгоритм авторазблокировки
                    $qaService->delete($attempts[0]);
                }
                throw new HM_Exception(
                    _('Закончились попытки на прохождение теста').
                    ($quest->limit_clean ? 
                        (_('. Дальнейшее прохождение возможно через ').HM_Date::getDurationString($limitSeconds-$diffSeconds)) : 
                        ''
                    )
                );
            }

            return $bCanGo;
        }
        return true;
    }

    public function getBeginDateRelative($____1= false, $____2= null)
    {
        $assign = $this->getParticipantAssign(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        $date   = $assign->beginPeprsonal;
        return    $this->date($date);
    }

    public function getEndDateRelative($____1= false, $____2= null)
    {
        $assign = $this->getParticipantAssign(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId());
        $date   = $assign->end_personal;
        return    $this->date($date);
    }

    public function getParticipantAssign($participantId)
    {
        if ($participantId) {
            if ($assigns = $this->getAssigns()) {
                foreach($assigns as $assign) {
                    if ($assign->MID == $participantId) {
                        return $assign;
                    }
                }
            }
        }
        return false;
    }
}