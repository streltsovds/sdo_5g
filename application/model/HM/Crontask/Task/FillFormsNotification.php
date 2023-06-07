<?php
class HM_Crontask_Task_FillFormsNotification extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface {

	
    protected $_importService = null;

    public function getTaskId() {
        return 'fillFormsNotification';
    }

    public function run() 
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');
        $receivers = array();
        $actualSessions = $serviceContainer->getService('AtSession')->fetchAll(
            array(
                'state = ?' => HM_At_Session_SessionModel::STATE_ACTUAL,
                'programm_type = ?' => HM_Programm_ProgrammModel::TYPE_ASSESSMENT
            )
        )->getList('session_id');

        $respondents = $serviceContainer->getService('AtSessionRespondent')->fetchAll();

        foreach ($respondents as $sessionRespondent) {
            if (in_array($sessionRespondent->session_id, $actualSessions)) {
                $notFilledYetForms = $serviceContainer->getService('AtSessionEvent')->fetchAll(
                    $serviceContainer->getService('AtSessionEvent')->quoteInto(
                        array(
                            ' session_id = ? AND ',
                            ' session_respondent_id = ? AND ',
                            ' status IN (?) '
                        ),
                        array(
                            $sessionRespondent->session_id,
                            $sessionRespondent->session_respondent_id,
                            array(HM_At_Session_Event_EventModel::STATUS_PLANNED, HM_At_Session_Event_EventModel::STATUS_IN_PROGRESS)
                        )
                    )
                );

                if (count($notFilledYetForms)) {
                    $receivers[$sessionRespondent->user_id][] = $sessionRespondent->session_id;
                }
            }
        }

        $messenger = $serviceContainer->getService('Messenger');

        foreach($receivers as $receiver => $sessions) {
            foreach ($sessions as $sessionId) {
                $session = $serviceContainer->getService('AtSession')->find($sessionId)->current();
                $urlSession = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] .
                    '/at/session/report/card/session_id/'.$sessionId;
                $messenger = $serviceContainer->getService('Messenger');
                $messenger->setOptions(
                    HM_Messenger::TEMPLATE_ASSIGN_SESSION_FILL_FORMS,
                    array(
                        'url_session'   => $urlSession,
                        'session_begin' => date('d.m.Y', strtotime($session->begin_date)),
                        'session_end'   => date('d.m.Y', strtotime($session->end_date)),
                        'begin' => date('d.m.Y', strtotime($session->begin_date)),
                        'end'   => date('d.m.Y', strtotime($session->end_date)),
                        'contacts'      => $serviceContainer->getService('AtSession')->getManagerContacts($session->initiator_id),
                    ),
                    'session',
                    $sessionId
                );
                $messenger->send(HM_Messenger::SYSTEM_USER_ID, $receiver);
            }
        }
    }
}
