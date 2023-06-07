<?php
class Programm_IndexController extends HM_Controller_Action
{
    private $_programmId = 0;
    private $_programm = null;

    protected $_profile;
    
    protected $service     = 'AtProfile';
    protected $idParamName = 'profile_id';
    protected $idFieldName = 'profile_id';
    protected $id          = 0;

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id' , 0);
        if(!$this->_programmId){
            $this->_programmId = (int) $this->_getParam('programm_recruit',0);
            if(!$this->_programmId){
                $this->_programmId = (int) $this->_getParam('programm_assessment',0);
                if(!$this->_programmId){
                    $this->_programmId = (int) $this->_getParam('programm_elearning',0);
                    if(!$this->_programmId){
                        $this->_programmId = (int) $this->_getParam('programm_reserve',0);
                    }
                }
            }
        }
        $this->_programm = $this->getOne($this->getService('Programm')->find($this->_programmId));
        $this->view->setSubHeader($this->_programm->name);

        $profileId = $this->_getParam('profile_id', 0);
        if ($profileId && $profiles = $this->getService('AtProfile')->find($profileId)) {
            $this->_profile = $profiles->current();

            if (!$this->isAjaxRequest()) {

                $this->view->setExtended(
                    array(
                        'subjectName' => $this->service,
                        'subjectId' => $this->_profile->profile_id,
                        'subjectIdParamName' => $this->idParamName,
                        'subjectIdFieldName' => $this->idFieldName,
                        'subject' => $this->_profile
                    )
                );
            }
        }

        parent::init();
    }

    public function indexAction()
    {
        $profileId = $this->_getParam('profile_id', null);
        if ($this->_programm) {
            switch ($this->_programm->programm_type) {
                case HM_Programm_ProgrammModel::TYPE_RECRUIT:
                case HM_Programm_ProgrammModel::TYPE_ADAPTING:
                case HM_Programm_ProgrammModel::TYPE_ASSESSMENT:
                case HM_Programm_ProgrammModel::TYPE_RESERVE:
                    $this->_redirector->gotoSimple('index', 'evaluation', 'programm', array('programm_id' => $this->_programmId, 'profile_id' => $profileId));
                    break;
                case HM_Programm_ProgrammModel::TYPE_ELEARNING:
                    $this->_redirector->gotoSimple('index', 'subject', 'programm', array('programm_id' => $this->_programmId, 'profile_id' => $profileId));
                    break;
            }

        } else {
            $this->_flashMessenger->addMessage(array(
                'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                'message' => _('Программа не найдена')
            ));
            $this->_redirector->gotoSimple('index', 'list', 'programm', array('programm_id' => null));
        }
    }


    public function assignAction()
    {
        if ($this->isAjaxRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();

            $ids = $this->_getParam('course_id', array());
            $isElectives = $this->_getParam('idElective', array());


            $oldSubjects = $this->getService('Programm')->getSubjects($this->_programmId);
            $oldIds = array ('Elektive' => array(), 'noElektive' => array());
            $newIds = array ('Elektive' => array(), 'noElektive' => array());
            if ($oldSubjects) {
                foreach ($oldSubjects as $oldSubject) {
                    if ($oldSubject->isElective) {
                        $oldIds['Elektive'][] =  $oldSubject->item_id;
                    } else {
                        $oldIds['noElektive'][] =  $oldSubject->item_id;
                    }
                }
            }

            if (count($ids)) {
                foreach($ids as $key=>$id) {

                    if ($isElectives[$key]) {
                        $newIds['Elektive'][] =  $id;
                    } else {
                        $newIds['noElektive'][] =  $id;
                    }

                    $this->getService('Programm')->assignSubject($this->_programmId, $id, $isElectives[$key]);
                }
            }

            $addIds = array_diff($newIds['noElektive'], $oldIds['noElektive']);
            $removeIds = array_diff($oldIds['noElektive'], $newIds['noElektive']);

            $this->getService('Lesson')->beginProctoringTransaction();

            /* обновляем список курсов для пользователей программы, возвращаем МИДы слушателей для которых обновили курсы*/
            $usersIds = $this->getService('Programm')->updateCoursesForUsers($this->_programmId, $addIds, $removeIds);

            /* обновляем список курсов на группах */
            $this->getService('Programm')->updateCoursesForGroups($this->_programmId, $newIds, $oldIds);

            $this->getService('Lesson')->commitProctoringTransaction();

            /* Удаляем связь программа курс */
            if (count($ids)) {
                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id NOT IN (?)'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $ids)
                    )
                );
            } else {
                /* Удаляем все курсы */
                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
                    )
                );
            }

        } else {
            $this->_redirector->gotoSimple('index');
        }
    }

    public function unassignAction()
    {
        $events = explode(',', $this->_getParam('postMassIds_grid', ''));

        if (count($events)) {
            foreach($events as $eventId) {
                $this->getService('ProgrammEvent')->delete($eventId);
            }
        }

        $this->_redirector->gotoSimple('index', 'index', 'programm', array('programm_id' => $this->_programmId));
    }
}