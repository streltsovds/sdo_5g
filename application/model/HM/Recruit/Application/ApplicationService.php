<?php
class HM_Recruit_Application_ApplicationService extends HM_Service_Abstract
{

    public function getGridSelect($my = false)
    {
        $select = parent::getSelect();

        $select->from('recruit_application', array(
            'recruit_application_id',
            'user_id',
            'created_by',
            'vacancy_name',
            'vacancy_id',
            'department_path',
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(user.LastName, ' ') , user.FirstName), ' '), user.Patronymic)"),
            'rv_name' => 'rv.name',
            'rv_id' => 'rv.vacancy_id',
            'programm_name',
            'recruiter_user_id' => 'user2.MID',
//            'recruiter_fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(user2.LastName, ' ') , user2.FirstName), ' '), user2.Patronymic)"),
            'status'
        ));

        $select->joinLeft(
            array('user' => 'People'),
            'recruit_application.user_id = user.MID',
            array()
        );

        $select->joinLeft(
            array('user2' => 'People'),
            'recruit_application.recruiter_user_id = user2.MID',
            array()
        );

        $select->joinLeft(
            array('soid1' => 'structure_of_organ'),
            'recruit_application.soid = soid1.soid',
            array()
        );
        $select->joinLeft(
            array('rv' => 'recruit_vacancies'),
            'recruit_application.vacancy_id = rv.vacancy_id',
            array()
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(
            HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR,
            HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL,
        ))) {
            $select
                ->joinLeft(array('rsp' => 'responsibilities'), sprintf("rsp.user_id = %s and rsp.item_type = %s", $this->getService('User')->getCurrentUserId(), HM_Responsibility_ResponsibilityModel::TYPE_STRUCTURE), array())
                ->joinLeft(array('soid2' => 'structure_of_organ'), "rsp.item_id = soid2.soid", array())
                ->where("(soid2.lft <= soid1.lft")->where("soid1.rgt <= soid2.rgt)");
        }


        if ($my) {
            if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
                $select->where('recruit_application.created_by = ?', $this->getService('User')->getCurrentUserId());
            } else {
                $select->where('recruit_application.recruiter_user_id = ?', $this->getService('User')->getCurrentUserId());
            }
        }

	$select->group(array(
            'recruit_application.recruit_application_id',
            'recruit_application.user_id',
            'recruit_application.created_by',
            'recruit_application.vacancy_name',
            'recruit_application.vacancy_id',
            'recruit_application.department_path',
            'user.LastName',
            'user.FirstName',
            'user.Patronymic',
            'rv.name',
            'rv.vacancy_id',
            'recruit_application.programm_name',
            'user2.MID',
            'recruit_application.status',
        ));

        return $select;
    }

    public function insert($data)
    {
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        $data['created'] = $this->getDateTime();

        return parent::insert($data);
    }

    public function takeToWork($recruitApplicationId)
    {
        $data = array();
        $data['recruit_application_id'] = $recruitApplicationId;
        $data['status'] = HM_Recruit_Application_ApplicationModel::STATUS_INWORK;
        $data['recruiter_user_id'] = $this->getService('User')->getCurrentUserId();

        return parent::update($data);
    }



    public function getOrgstructureList($recruitApplicationId)
    {
        $list = array();

        $recruitApplication = $this->getOne($this->findDependence('Department', $recruitApplicationId));
        if ($recruitApplication && count($recruitApplication->department)) {
            $department = $recruitApplication->department->current();
            if (count($collection = $this->getService('Orgstructure')->fetchAllDependence('Profile', array(
                'type IN (?)' => array(
                    HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    HM_Orgstructure_OrgstructureModel::TYPE_VACANCY,
                ),
                'lft > ?' => $department->lft,
                'rgt < ?' => $department->rgt,
                ), 'name'))) {

                foreach ($collection as $item) {
                    if (count($item->profile)) {
                        $profile = $item->profile->current();
                        $list[$item->soid] = sprintf("%s | %s%s",
                            $item->name ? : '-',
                            $profile->name,
                            $item->mid ? '' : '*'
                        );
                    }
                }
            }
        }
        return $list;
    }

}