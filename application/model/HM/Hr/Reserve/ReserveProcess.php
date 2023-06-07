<?php

class HM_Hr_Reserve_ReserveProcess extends HM_Process_Type_Static
{
    public function onProcessStart()
    {


    }

    static public function getStatuses()
    {
        return array(
            self::PROCESS_STATUS_INIT => _('Сессия создана'),
            self::PROCESS_STATUS_CONTINUING => _('Доступ к оценочным мероприятиям открыт'),
            self::PROCESS_STATUS_COMPLETE => _('Сессия завершена'),
            self::PROCESS_STATUS_FAILED => _('Сессия отменена'), // ?
        );
    }

    public function getType()
    {
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_RESERVE;
    }

    public function onProcessComplete()
    {
        $reserveService = Zend_Registry::get('serviceContainer')->getService('HrReserve');

        $reserve = $this->getModel();
        $reserveService->update(array(
            'reserve_id' => $reserve->reserve_id,
            'status' => HM_Hr_Reserve_ReserveModel::STATE_CLOSED,
            'result' => HM_Hr_Reserve_ReserveModel::RESULT_SUCCESS
        ));

        if (count($reserve->sessionUser)) {
            $sessionUser = $reserve->sessionUser->current();
        } else {
            $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->getOne(
                Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAllDependence(array('Session'), array('reserve_id = ?' => $reserve->reserve_id))
            );
        }

        // остановить сессию оценки, прекратить дальнейшее заполнение форм
        Zend_Registry::get('serviceContainer')->getService('AtSession')->stopSession($reserve->session_id);

        if ($sessionUser) {
            Zend_Registry::get('serviceContainer')->getService('Process')->goToComplete($sessionUser);
        } // в этот момент генерится pdf


    }
}