<?php

class HM_Hr_Rotation_RotationProcess extends HM_Process_Type_Static
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
        return HM_Process_ProcessModel::PROCESS_PROGRAMM_ROTATION;
    }

    public function onProcessComplete()
    {
        $rotationService = Zend_Registry::get('serviceContainer')->getService('HrRotation');
        $feedbackUserService = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers');

        $rotation = $this->getModel();
// goToFail тоже приводит сюда
//        $rotationService->update(array('rotation_id' => $rotation->rotation_id, 'result' => HM_Hr_Rotation_RotationModel::RESULT_SUCCESS));

        $feedbackUserService->assignUser(
            $rotation->user_id,
            HM_Feedback_FeedbackModel::ROTATION_FEEDBACK
        );
    }
}