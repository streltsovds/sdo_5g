<?php
class HM_EstaffSpot_EstaffSpotService extends HM_Service_Abstract
{
    public function insert($data, $unsetNull = true)
    {
        try {
            $item = parent::insert($data, $unsetNull);
        } catch (Exception $e) {
            // могут быть нехорошие символы в резюме
            // не прерывать импорт из-за этого
        }
        return $item;
    }

    static public function getStateName($state_id) {
        switch ($state_id) {
            case 'blacklist':
                return 'в чёрном списке';
                break;
            case 'hire':
                return 'нанят';
                break;
            case 'interview:scheduled':
                return 'назначено интервью';
                break;
            case 'job_offer:failed':
                return 'отказ от предложенной работы';
                break;
            case 'job_offer:succeeded':
                return 'согласие на предложенную работу';
                break;
            case 'new':
                return 'новый';
                break;
            case 'phone_interview':
                return 'телефонное интервью';
                break;
            case 'phone_interview:failed':
                return 'телефонное интервью: неуспешно';
                break;
            case 'phone_interview:scheduled':
                return 'телефонное интервью: запланировано';
                break;
            case 'reject':
                return 'отказ';
                break;
            case 'reserve':
                return 'резерв';
                break;
            case 'rr_interview':
                return 'интервью';
                break;
            case 'rr_interview:cancelled':
                return 'интервью: отменено';
                break;
            case 'rr_interview:scheduled':
                return 'интервью: запланировано';
                break;
            case 'rr_reject':
                return 'отказ';
                break;
            case 'rr_resume_review':
                return 'анализ резюме';
                break;
            case 'rr_resume_review:failed':
                return 'анализ резюме: неуспешно';
                break;
            case 'rr_resume_review:succeeded':
                return 'анализ резюме: успешно';
                break;
            case 'security_check':
                return 'проверка службой безопасности';
                break;
            case 'security_check:failed':
                return 'проверка службой безопасности: неуспешно';
                break;
            case 'security_check:succeeded':
                return 'проверка службой безопасности: успешно';
                break;
            case 'self_reject':
                return 'самоотвод';
                break;
            case 'vacancy_close':
                return 'вакансия закрыта';
                break;
            case 'vacancy_response':
                return 'ответ на вакансию';
                break;
        }
    }
}
