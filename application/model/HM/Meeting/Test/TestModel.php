<?php
/*
 * Тест
 */
class HM_Meeting_Test_TestModel extends HM_Meeting_MeetingModel
{

//    const TEST_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TEST;
    }

    public function getIcon($size = HM_Meeting_MeetingModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}test.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
//        return Zend_Registry::get('config')->url->base."/quest/subject/start/meeting_id/".$this->SHEID."/quest_id/".$this->getModuleId();
        $redirectUrl = parse_url($_SERVER['HTTP_REFERER']);
        $redirectUrl = $redirectUrl['path'].'?'.$redirectUrl['query'];
        
        $params = array(
            'module'     => 'quest',
            'controller' => 'meeting',
            'action'     => 'start',
            'quest_id' => $this->getModuleId(),
            'meeting_id' => $this->meeting_id,
            'redirect_url' => urlencode($redirectUrl),
//          'subject_id' => $this->CID
                        );
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));

    }

    public function getQuestContext()
    {
        return array(
            'context_type' => HM_Quest_Attempt_AttemptModel::CONTEXT_TYPE_PROJECT,
            'context_event_id' => $this->meeting_id
        );
    }
    
    public function getResultsUrl($options = array())
    {
        $params = array(
                          'module'     => 'meeting',
                          'controller' => 'result', 
                          'action'     => 'index',
                          'meeting_id'  => $this->meeting_id,
                          'baseUrl'    => ''
                        );
        
        if ($this->project_id > 0) {
            $params['project_id'] = $this->project_id;
        }
                        
        $params = (count($options))? array_merge($params,$options) : $params;
                        
        return Zend_Registry::get('view')->url($params,null,true);

    }
    
    
    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'meeting', 
                	'controller' => 'execute',  
                	'action' => 'index', 
                	'meeting_id' => $this->meeting_id
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
            Zend_Registry::get('serviceContainer')->getService('Quest')->findDependence(array('Settings', 'ProjectAssign'), $this->getModuleId())
        )) {
            throw new HM_Exception(_('Тест не найден'));
            return false;
        }
        $quest->setScope(HM_Quest_QuestModel::SETTINGS_SCOPE_MEETING, $this->meeting_id);
        

        //todo: Проверка на попытки!
        /** @var HM_Quest_Attempt_AttemptService $qaService */
        $qaService = Zend_Registry::get('serviceContainer')->getService('QuestAttempt');

        $attempts = $qaService->fetchAll($qaService->quoteInto(
            array('user_id=?', ' AND quest_id=?', ' AND type=?', ' AND context_event_id=?'),
            array(
                Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
                $this->getModuleId(),
                HM_Quest_QuestModel::TYPE_TEST,
                $this->meeting_id,
            )
        ),
        'attempt_id DESC'
        );


        $attempCount = count($attempts);
        $attempts = $attempts->getList('attempt_id', 'date_begin');
        $attemptsDates = array_values($attempts);
        $attempts = array_keys($attempts);

        if($attempCount) {
            $lastAttemptDate = new HM_Date($attemptsDates[0]);
        }

        // Внимание! Сюда мы попадаем как до теста (не даем перейти к тесту) и во время теста (защита от хакеров), индиктор режима - $fromQuest
        // Если проверка осуществляется из запущенного теста, значит текущая попытка уже записана в базу и ее учитывать не надо
        if($fromQuest == true && $attempCount > 0 && $quest->limit_attempts != 0){
            $attempCount--;
            if(isset($attempts[1])) {// Берем предпоследнюю т.к. текущую не учитываем
                $lastAttemptDate = new HM_Date($attemptsDates[1]);
            }
        }         

        if ($quest->limit_attempts != 0 && $attempCount >= $quest->limit_attempts) {

            $now = new HM_Date(date('Y-m-d H:i:s'));
            $diffSeconds = $now->sub($lastAttemptDate)->toValue();
            $limitSeconds = $quest->limit_clean*3600*24;

            //Делим все попытки на серии по limit_attempts штук. Если авторазблокировка есть, то ограничиваем только на последней попытке в серии 
            //и то при том, что время после последней попытки еще не прошло достаточно много (limit_clean)
            $bCanGo = $quest->limit_clean && (($attempCount % $quest->limit_attempts)!=0 || ($diffSeconds>$limitSeconds));
            if(!$bCanGo /*&& !$fromQuest*/) {

                if($fromQuest) { //В тест как-то попали, нечестно, а прав на это нету - удаляем текущую попытку, чтобы не нарушать алгоритм авторазблокировки
                    $this->getService('QuestAttempt')->delete($attempts[0]);
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

}