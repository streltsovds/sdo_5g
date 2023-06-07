<?php
class Vacancy_VacancyController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;


    protected $_positionCache = array();

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", 'created_at_DESC');
        }

        $select = $this->getService('RecruitVacancy')->getSelect();
        $select->from(
            array('so' => 'structure_of_organ'),
            array(
                'org_id' => 'so.soid',
                'profile_name' => 'p.name',
                'department' => 'so.owner_soid',
                'created_at' => 'so.created_at',
                'vacancy_id' => 'v.vacancy_id',
                'session_name' => 'v.name',
                'reqruiters' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT r.user_id)")
            )
        )->joinInner(
            array('p' => 'at_profiles'),
            "so.profile_id = p.profile_id",
            array()
        )->joinInner(
            array('sop' => 'structure_of_organ'),
            "sop.soid = so.owner_soid",
            array()
        )->joinLeft(
            array('v' => 'recruit_vacancies'),
            "so.soid = v.position_id",
            array()
        )->joinLeft(
            array('rv' => 'recruit_vacancy_recruiters'),
            "v.vacancy_id = rv.vacancy_id",
            array()
        )->joinLeft(
            array('r' => 'recruiters'),
            "rv.recruiter_id = r.recruiter_id",
            array()
        )->where(
            "so.type = ?", HM_Orgstructure_OrgstructureModel::TYPE_VACANCY
        )->group( array(
            'so.soid',
            'so.created_at',
            'p.name',
            'so.owner_soid',
            'v.vacancy_id',
            'v.name'
        ));

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR)) {
            $currentUser = $this->getService('User')->getCurrentUser();
            $userPosition = $this->getOne($this->getService('Orgstructure')->fetchAll($this->quoteInto('mid = ?', $currentUser->MID)));
            $parentPosition = $this->getOne($this->getService('Orgstructure')->find($userPosition->owner_soid));

            if ($userPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $parentPosition->lft)
                    ->where('rgt < ?', $parentPosition->rgt);
                $select->where("so.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
	    }

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL)) {
            // все по области ответственности, даже не назначенные
            $soid = $this->getService('Responsibility')->get();
            $responsibilityPosition = $this->getOne($this->getService('Orgstructure')->find($soid));
            if ($responsibilityPosition) {
                $subSelect = $this->getService('Orgstructure')->getSelect()
                    ->from('structure_of_organ', array('soid'))
                    ->where('lft > ?', $responsibilityPosition->lft)
                    ->where('rgt < ?', $responsibilityPosition->rgt);
                $select->where("so.soid IN (?)", $subSelect);
            } else {
                $select->where('1 = 0');
            }
        }

        /*

select
	so.soid,
	p.name as profile_name,
	so.owner_soid,
	v.vacancy_id,
	v.name as vacancy_name,
	dbo.GROUP_CONCAT(DISTINCT r.user_id)
from
	structure_of_organ so
		inner join at_profiles p on so.profile_id = p.profile_id
		left join recruit_vacancies v on so.soid = v.position_id
			left join recruit_vacancy_recruiters rv on v.vacancy_id = rv.vacancy_id
				left join recruiters r on rv.recruiter_id = r.recruiter_id

            -- для фильтрации по ФИО рекрутера
			left join recruit_vacancy_recruiters rv1 on v.vacancy_id = rv1.vacancy_id
				left join recruiters r1 on rv1.recruiter_id = r1.recruiter_id
					left join People u on r1.user_id = u.MID
            ---
where
	so.type = -3
	and (u.LastName LIKE '%%' OR u.FirstName LIKE '%%' OR u.Patronymic LIKE '%%')
group by
	so.soid,
	p.name,
	so.owner_soid,
	v.vacancy_id,
	v.name

        */




        $columns = array(
            'org_id' => array('hidden' => true),
            'profile_name' => array('title' => _('Профиль должности')),
            'department' => array(
                'title' => _('Подразделение'),
                'callback' => array(
                    'function' => array($this, 'getPositionName'),
                    'params' => array('{{department}}')
                )
            ),
            'created_at' => array(
                'title' => _('Дата создания'),
                'format' => array('Date', array('date_format' => Zend_Locale_Format::getDateTimeFormat())),
            ),
            'vacancy_id' => array('hidden' => true),
            'session_name' =>  array('title' => _('Сессия подбора')),
            'reqruiters' => array(
                'title' => _('Специалисты по подбору'),
                'callback' => array(
                    'function' => array($this, 'usersCache'),
                    'params' => array('{{reqruiters}}')
                )
            )
        );

        $filters = array(
            'profile_name' => array(),
            'department' => array(
                'render' => 'department',
//                'callback' => array(
//                    'function' => array($this, 'departmentFilter')
//                )

            ),
            'created_at' => array('render' => 'DateSmart'),
            'session_name' => array(),
            'reqruiters' => array(
                'callback' => array(
                    'function' => array($this, 'recruitersFilter')
                )
            )
        );

        $grid = $this->getGrid($select, $columns, $filters);

        $grid->updateColumn('session_name',
            array(
                'callback' => array(
                    'function'=> array($this, 'updateName'),
                    'params'=> array('{{vacancy_id}}', '{{session_name}}', '{{org_id}}')
                )
            )
        );




//        $grid->addAction('[url]',  array(), '[replace_text]');



        $grid->addAction(array(
        	    'module'     => 'application',
	            'controller' => 'list',
	            'action'     => 'new',
	        ),
        	array('org_id'),
		_('Создать заявку на подбор')
        );

        $grid->addAction(array(
        	    'module'     => 'vacancy',
	            'controller' => 'list',
	            'action'     => 'create-from-structure',
	        ),
        	array('org_id'),
		_('Создать сессию подбора')
        );

        $grid->addAction(array(
            'module'     => 'vacancy',
            'controller' => 'report',
            'action'     => 'card',
        ),
            array('vacancy_id'),
            _('Просмотр сессии подбора')
        );


        $grid->setActionsCallback(
            array(
                'function' => array($this, 'updateActions'),
                'params'   => array('{{vacancy_id}}')
            )
        );



        $this->view->grid = $grid;
    }


    public function updateActions($vacancyId, $actions)
    {
        if ($vacancyId) {
		$this->unsetAction($actions, array(        	    
	  	    'module'     => 'vacancy',
	            'controller' => 'list',
	            'action'     => 'create-from-structure',
		));
		$this->unsetAction($actions, array(        	    
        	    'module'     => 'application',
	            'controller' => 'list',
	            'action'     => 'new',
		));
        } else {
		$this->unsetAction($actions, array(        	    
	            'module'     => 'vacancy',
        	    'controller' => 'report',
	            'action'     => 'card',
		));
        }

        return $actions;
    }


    public function updateName($vacancyId, $name, $positionId)
    {
        $return = '';
        if ($vacancyId) {
            $return = '<a href="' . $this->view->url(array('controller' => 'report', 'action' => 'card', 'vacancy_id' => $vacancyId, 'candidate_id' => null)) . '">' . $this->view->escape($name) . '</a>';

        }
        return $return;
    }


    protected function getPosition($soid)
    {
        if (! isset($this->_positionCache[$soid])) {
            $position = $this->getService('Orgstructure')->getOne(
                $this->getService('Orgstructure')->find($soid)
            );
            if (false !== $position)
                $this->_positionCache[$soid] = array('name' => $position->name, 'parent' => $position->owner_soid);
            else
                $this->_positionCache[$soid] = array('name' => _('Нет данных'), 'parent' => 0);
        }
        return $this->_positionCache[$soid];
    }

    public function recruitersFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];


        // Только больше 2 символов чтобы много не лезло в in
        if(strlen($value) > 2){
            $value = '%' . $value . '%';
            $select->joinLeft(
                array('rv1' => 'recruit_vacancy_recruiters'),
                "v.vacancy_id = rv1.vacancy_id",
                array()
            )->joinLeft(
                array('r1' => 'recruiters'),
                "rv1.recruiter_id = r1.recruiter_id",
                array()
            )->joinLeft(
                array('u' => 'People'),
                "r1.user_id = u.MID",
                array()
            )->where(
                "(u.LastName LIKE (?)", $value
            )->orWhere(
                "u.FirstName LIKE (?)", $value
            )->orWhere(
                "u.Patronymic LIKE (?))", $value
            );
        }

    }

    public function departmentFilter($data)
    {
        return;
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];
        if ($value) {
            $select->where("so.owner_soid = ?", $value);
        }
    }

    public function getPositionName($soid, $fullPath = true)
    {
        $position = $this->getPosition($soid);
        $result = $position['name'];

        if ($position['parent'] && $fullPath) $result = $this->getPositionName($position['parent']) . " → " . $result;
        return $result;
    }
}