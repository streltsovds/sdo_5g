<?php
class HM_Contact_ContactService extends HM_Activity_ActivityService
{
    public function getContactSelect($subject, $subjectId)
    {
        $currentUser = $this->getService('User')->getCurrentUserId();

        if ($subject == 'project'){
            $select = $this->getService('Activity')->getActivityProjectUsersSelect($subjectId);
        } else {
            $select = $this->getService('Activity')->getActivityUsersSelect(true);
        }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) {
            $select->joinLeft(
                array('rsp' => 'responsibilities'),
                'rsp.user_id = t1.MID AND rsp.item_type = ' . HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE,
                array('is_specialist' => 'rsp.item_id')
            );

            $currentUserPosition = $this->getService('Orgstructure')->fetchAll(array(
                'mid = ?' => $currentUser
            ))->current();
            if ($currentUserPosition->soid) {
                $userUpperDepartments = $this->getService('Orgstructure')->getAllOwnersInTree($currentUserPosition->soid);
                if (count($userUpperDepartments)) $select->where('rsp.item_id IS NULL OR rsp.item_id IN (?)', $userUpperDepartments);
            }
        }

        $select->where('blocked != ?', 1);

        return $select;
    }
}