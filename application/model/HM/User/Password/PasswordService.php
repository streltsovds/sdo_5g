<?php

class HM_User_Password_PasswordService extends HM_Service_Abstract
{

    public function getChangePasswordLastDate($userId)
    {
        $userId = (int)$userId;

        $res = $this->getOne($this->fetchAll(array('user_id = ?' => $userId), array('change_date DESC'), 1));
        if (!$res) {
            return false;
        }
        return $res->change_date;

    }

    public function getLastPasswords($userId, $amount)
    {
        $userId = (int)$userId;
        $res = $this->fetchAll(array('user_id = ?' => $userId), array('change_date DESC'), $amount);
        return $res;
    }

    /**
     * todo: Добавить проверки по другим параметрам пароля
     *
     * @param $password
     * @return array
     */
    public function checkPassword($password) {
        /** @var HM_Option_OptionService $optionService */
        $optionService = $this->getService('Option');
        $options = $optionService->getOptions(HM_Option_OptionModel::SCOPE_PASSWORDS);

        $errors = [];
        if($options['passwordMinLength'] > 0 && strlen($password) < $options['passwordMinLength']) {
            $errors[] = sprintf(_('Количество символов в пароле должно быть не менее %d'), $options['passwordMinLength']);
        }

        return $errors;
    }
}