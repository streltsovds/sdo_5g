<?php


class HM_View_Infoblock_ActivityBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'activity';
    protected $session;

    const TYPE_TIMES 	= 'times';
    const TYPE_SESSIONS = 'sessions';

    const USER_ALL			= -2;
    const GROUP_ALL			= -2;
    const GROUP_TEACHERS	= -1;

    public $periodSet = array(
    	HM_Date::PERIOD_2WEEKS_RELATIVE,
    	HM_Date::PERIOD_4WEEKS_RELATIVE,
    );

    public function activityBlock($param = null)
    {
		$this->session = new Zend_Session_Namespace('infoblock_activity');
		$this->_setDefaults();

		// получаем курсы
        $courses = [];
        foreach($this->_getGroups() as $key => $course) {
            $courses[] = [ 'value' => $key, 'text' => $course ];
        }

        $this->view->courses = $courses;



		$this->view->users = [[ 'value' => self::USER_ALL,'text' => _('все')]];
		$this->view->types = [
		    [ 'value' => 'times', 'text' => _('Время в системе') ],
		    [ 'value' => 'sessions', 'text' => _('Количество сессий')]
        ];

        $periods = [];
        $periodsSets = HM_Date::pluralFormsPeriods($this->periodSet);
        foreach($periodsSets as $key => $period) {
            $periods[] = [ 'value' => $key, 'text' => $period ];
        }
        $this->view->periods = $periods;

		$this->view->period = $this->session->period;
		$this->view->type	= $this->session->type;
		$this->view->export_url = $this->view->url(array(
            'module' => 'infoblock',
            'controller' => 'activity',
            'action' => 'get-data',
            'format' => 'csv',
        ));

//		не будем хранить в сессии выбранную группу и юзера
//		$this->view->group	= $this->session->group;
//		$this->view->user	= $this->session->user;

    	$content = $this->view->render('activityBlock.tpl');
        
        return $this->render($content);
    }

    private function _setDefaults()
    {
		if (!isset($this->session->period)) {
			$this->session->period = HM_Date::PERIOD_2WEEKS_RELATIVE;
		}
		if (!isset($this->session->type)) {
			$this->session->type = 'times';
		}
		if (!isset($this->session->group)) {
			$this->session->group = self::GROUP_ALL;
		}
		if (!isset($this->session->user)) {
			$this->session->user = self::USER_ALL;
		}
    }

    private function _getGroups()
    {
    	//$isTeacher = (HM_Role_Abstract_RoleModel::ROLE_TEACHER == Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole());
    	$isTeacher = (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
    	    Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_TEACHER)
        );

    	$groups = array(
        	self::GROUP_ALL => _('все'),
    	);

// непонятный функционал
//    	if (!$isTeacher) {
//	    	$groups[self::GROUP_TEACHERS] = _('преподаватели');
//    	}

        $serviceSubject = Zend_Registry::get('serviceContainer')->getService('Subject');
        $serviceUser = Zend_Registry::get('serviceContainer')->getService('User');
        $serviceDean = Zend_Registry::get('serviceContainer')->getService('Dean');

    	$select = $serviceSubject->getSelect();
        $select->from(array('s' => 'subjects'),
            array(
                'subid' => 's.subid',
                'name' => 's.name',
                )
            )->distinct()
                //->joinInner(array('st' => 'Students'), 'st.CID = s.subid', array())
                //->joinInner(array('ss' => 'sessions'), 'ss.MID = st.MID', array())
                ->order('s.name');

//#17238
        $now = date('Y-m-d H:i:s');
        $where = $serviceSubject->quoteInto(
            array(
                        '((s.period = ?) OR ',
                        '(s.period = ?', ' AND s.period_restriction_type = ?) OR ',
                        '(s.begin < ?',' AND s.end > ?',' AND s.period = ?',' AND s.period_restriction_type = ?) OR ',
                        '(s.state = ?',' AND s.period = ?',' AND s.period_restriction_type = ?))',
                        'AND s.is_labor_safety = ?',
            ),
            array(
                        HM_Subject_SubjectModel::PERIOD_FREE,
                        HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_DECENT,
                        $now, $now, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_STRICT,
                        HM_Subject_SubjectModel::STATE_ACTUAL, HM_Subject_SubjectModel::PERIOD_DATES, HM_Subject_SubjectModel::PERIOD_RESTRICTION_MANUAL,
                        0,
            )
        );
        $select->where($where);
//
        if ($isTeacher) {
        	$select->joinInner(array('t' => 'Teachers'), 't.CID = s.subid AND t.MID=' . $serviceUser->getCurrentUserId(), array());
        }

        // Область ответственности
        $options = $serviceDean->getResponsibilityOptions($serviceUser->getCurrentUserId());
        if($options['unlimited_subjects'] != 1
            && Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)
           //&& $serviceUser->getCurrentUserRole() == HM_Role_Abstract_RoleModel::ROLE_DEAN
        ){
            $select->joinInner(array('r' => 'responsibilities'), 'r.item_id = s.subid AND r.item_type = ' . HM_Responsibility_ResponsibilityModel::TYPE_SUBJECT, array())
                ->where('r.user_id = ?', $serviceUser->getCurrentUserId());
        }

		if ($rowset = $select->query()->fetchAll()) {
			foreach ($rowset as $row) {
				$groups[$row['subid']] = $row['name'];
			}
        }

        return $groups;
    }
}