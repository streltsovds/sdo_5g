<?php
class HM_Tc_Provider_Room_RoomService extends HM_Service_Abstract
{
    public function getListSource($providerId)
    {
        $select = $this->getSelect();
        $select->from(array('tcpr' => 'tc_provider_rooms'), array(
            'tcpr.room_id',
//            'tcpr.provider_id',
            'room_name'     => 'tcpr.name',
//            'provider_name' => 'tcp.name',
            'tcpr.type',
            'tcpr.places'
        ));
        $select->where('tcpr.provider_id=?', $providerId);

        return $select;

    }

    public function insert($data, $unsetNull = true)
    {
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');

        $data['created']    = HM_Date::now()->toString(HM_Date::SQL);
        $data['created_by'] = $userService->getCurrentUserId();

        return parent::insert($data, $unsetNull);

    }

    public function pluralFormCount($count)
    {
        return !$count ? _('Нет') : sprintf(_n('аудитория plural', '%s аудитория', $count), $count);
    }
}