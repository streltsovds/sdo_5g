<?php

class HM_State_Data_StateDataService extends HM_Service_Abstract
{
    // имеется в виду "отметить (check)" этап зеленой галочкой/красным крестом
    public function check($stateOfProcessId, $state, $stateStatus, $auto = false)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $stateOfProcessId = (int) $stateOfProcessId;
        $stateStatus           = (int) $stateStatus;
        $auto             = (int) (bool) $auto;

        $now = new HM_Date();
        $now = $now->toString(HM_Date::SQL);

        $currentUserId = $userService->getCurrentUserId();

        // все этапы добавляются при старте процесса;
        // здесь только фиксируем фактическую дату смены этапа и юзера для наступившего этапа
        $data = ($stateStatus == HM_State_Abstract::STATE_STATUS_CONTINUING) ? array(
            'begin_date'               => $now,
            'begin_by_user_id'         => $currentUserId,
            'begin_auto'               => $auto,
        ) : array(
            'end_date'               => $now,
            'end_by_user_id'         => $currentUserId,
            'end_auto'               => $auto,
        );
        $data['status'] = $stateStatus;

        $this->updateWhere($data, array(
            'state_of_process_id = ?'   => $stateOfProcessId,
            'state = ?'                 => $state,
        ));
    }

    public function rollback($stateOfProcessId, $state, $status)
    {
        $nextState = $state->getNextState();
        //делетим инфу о шаге с которого сделан роллбек, чтобы потом при goToNextState норм сработал _updateState
        $result = $this->deleteBy($this->quoteInto(
            array('state_of_process_id = ? ', ' AND state = ?'),
            array($stateOfProcessId, $nextState)
        ));
        //апдейтим инфу по шагу на который сделан роллбек
        $result1 = $this->updateWhere(array(
                'status'   => $status,
                'end_date' => null
            ),
            array(
                'state_of_process_id = ?' => $stateOfProcessId,
                'state = ?'               => get_class($state),
            )
        );
    }
}