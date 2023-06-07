<?php
class HM_Crontask_Task_LessonNotification extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface {

	
    protected $_importService = null;

    public function getTaskId() {
        return 'lessonNotification';
    }

    public function run() 
    {
        $select = $this->getServiceLayer('LessonAssign')->getSelect();
        $select->from(array('si' => 'scheduleID'), array('SHEID', 'MID'))
            ->joinInner(array('s' => 'schedule'), 's.SHEID = si.SHEID', array('notify_before', 'timetype', 'end', 'CID', 'lesson'=>'s.title'))
            ->joinInner(array('st' => 'students'), 'st.MID=si.MID AND st.CID=s.CID', array())
            ->joinInner(array('c' => 'subjects'), 'c.subid=s.CID', array('course'=>'c.name'))
            ->joinInner(array('p' => 'People'), 'p.MID=si.MID', array('fio'=>new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")))
            ->where('si.V_STATUS = -1 AND s.notify_before IS NOT NULL AND s.notify_before > 0 AND s.timetype <> ?', HM_Lesson_LessonModel::TIMETYPE_FREE);

        $incidents = $select->query()->fetchAll();


        $urlCache = array();
    	$messenger = $this->getServiceLayer('Messenger');
        $messenger->setRoom('', 0);
        foreach($incidents as $incident) {
            if($dateEnd = $this->getServiceLayer('LessonAssign')->isInNotificationPeriod($incident, $incident['MID'])) {
            	$messenger->setOptions(HM_Messenger::TEMPLATE_LESSON_NOTIFICATION, array(
                        'LESSON_ID' => $incident['CID'],
                        'COURSE' => $incident['course'],
                        'NAME' => $incident['fio'],
                        'DATE_END' => $dateEnd->get("dd.MM.yyyy"),
                    )
                );
    	        $messenger->send(HM_Messenger::SYSTEM_USER_ID, $incident['MID']);
            }
        }
    }
    
    /*
    *
    * @return HM_Service_Abstract
    */
    protected function  getServiceLayer($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

}
