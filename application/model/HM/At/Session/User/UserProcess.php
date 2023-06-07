<?php

class HM_At_Session_User_UserProcess extends HM_Process_Type_Programm
{
    public function onProcessStart(){}
    
    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_ASSESSMENT;
    }

    static public function getStatuses()
    {
    }
    
    public function onProcessComplete() 
    {
        /** @var HM_At_Session_User_UserModel $sessionUser */
        $sessionUser = $this->getModel();
        
        // сомнительное требование газнефть
        // если его убирать, то надо ещё изменить текст confirm у группового действия "принудительно завершить"
//         Zend_Registry::get('serviceContainer')->getService('AtSessionEvent')->deleteBy(array(
//             'session_user_id = ?' => $sessionUser->session_user_id,
//             'status != ?' => HM_At_Session_Event_EventModel::STATUS_COMPLETED,
//         ));        
        
        Zend_Registry::get('serviceContainer')->getService('AtSessionUserCriterionValue')->setCriteriaValues($sessionUser->session_user_id);

        /** @var HM_At_Session_User_UserService $sesionUserService */
        $sesionUserService = Zend_Registry::get('serviceContainer')->getService('AtSessionUser');

        $sessionUser->total_kpi = $sesionUserService->getKpiTotal($sessionUser); // ранг результативности
        $sessionUser->total_kpi = round(100 * $sessionUser->total_kpi); // хотим в процентах
        $sessionUser->total_competence = $sesionUserService->getResultsAvg($sessionUser); // среднее по компетенциям

//        $sessionUser->total_competence = $sesionUserService->getResultsVsProfile($sessionUser); // категория для матрицы
//        $sessionUser->result_category = $sesionUserService->getMatrixBlock($sessionUser->total_kpi, $sessionUser->total_competence); // решение по итогам оц.сессии
        $sessionUser->status = HM_At_Session_User_UserModel::STATUS_COMPLETED;

        $sesionUserService->update($sessionUser->getValues());

        // Обновляем last_at_session_id для данного работника
        Zend_Registry::get('serviceContainer')->getService('Orgstructure')->update(array(
            'soid' => $sessionUser->position_id,
            'last_at_session_id' => $sessionUser->session_id
        ));

        $sesionUserService->generatePdfs($sessionUser->session_user_id);
        
        Zend_Registry::get('serviceContainer')->getService('Log')->log(
            Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(),
            'User process complete',
            'Success',
            Zend_Log::NOTICE,
            'HM_At_Session_User_UserModel',
            $sessionUser->user_id
        );
        
    }

    public function getStateDatesMode()
    {
        return HM_Process_Abstract::MODE_STATE_DATES_HIDDEN;
    }
}