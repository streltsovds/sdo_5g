<?php
/**
 *
 */

trait HM_Grid_ColumnCallback_Trait_Common
{
    protected $_deanSubjectIds;
    protected $courseCache;
    protected $departmentCache = array();

    // callbacks
    /*
     * Эти два коллбэка встречаются в HM_Controller_Action_Trait_Grid, который часто вызывается вместе с этим трейтом на странице,
     * что приводит к конфликту методов
     *
     * Пока что закомментировал, надо как-то в одном месте это собирать или переименовывать
     *
     * public function updateDate($date){
        if (!$date || ($date == "") || (!strtotime($date))){
            return _('Нет');
        }else{
            $date = new Zend_Date($date);

            if($date instanceof Zend_Date){
                return $date->toString(HM_Locale_Format::getDateFormat());
            }else{
                return _('Нет');
            }

        }
    }

     public function updateFio($fio, $userId)
    {
        $fio = trim($fio);
        if (!strlen($fio)) {
            $fio = sprintf(_('Пользователь #%d'), $userId);
        }
        return $fio;
    }
    */

    public function updateDateBegin($date, $periodRestrictionType, $state)
    {
        if (!$date) return '';
        $date = new Zend_Date($date, 'YYYY-MM-DD HH:mm:ss');
        $date = iconv('UTF-8', Zend_Registry::get('config')->charset, $date->toString(HM_Locale_Format::getDateFormat()));

        return $date;
    }

    public function getDateForGrid($date,$onlyDate = false)
    {

    }

    public function updateTimeEndedPlanned($date, $CID, $newcomerId = false)
    {
        if (!isset($this->_cache['subject-period'])) {
            $this->_cache['subject-period'] = $this->serviceContainer->getService('Subject')->fetchAll()->getList('subid', 'period');
        }
        return isset($this->_cache['subject-period'][$CID]) && ($this->_cache['subject-period'][$CID] == HM_Subject_SubjectModel::PERIOD_FREE) && !$newcomerId  ? _('Нет') : $date;
    }



    public function coursesCache($field, $select) {
        if($this->serviceContainer->getService('Acl')->inheritsRole($this->serviceContainer->getService('User')->getCurrentUserRole(),
            HM_Responsibility_ResponsibilityModel::getResponsibilityRoles())) {
            if ($this->_deanSubjectIds === false) {
                $this->_deanSubjectIds = $this->serviceContainer->getService('Responsibility')->getSubjectIds($this->serviceContainer->getService('User')->getCurrentUserId());
            }

            $subjectIds = explode(',', $field);
            $subjectIds = array_intersect($subjectIds, empty($this->_deanSubjectIds) ? array() : $this->_deanSubjectIds);
            $field      = implode(',', $subjectIds);
        }

        if ($this->courseCache === null){
            $this->courseCache = array();
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['courses'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $tmp = array_filter($tmp);
            if (count($tmp)) {
                $this->courseCache = $this->serviceContainer->getService('Subject')->fetchAll(array('subid IN (?)' => $tmp), 'name');
            }
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        $result =  array();
        if (is_a($this->courseCache, 'HM_Collection')) {
            foreach($fields as $value){
                if ($tempModel = $this->courseCache->exists('subid', $value)) {
                    $marker = '';
                    if ($tempModel->base_id) {
                        $marker = HM_View_Helper_Footnote::marker(1);
                        $this->view->footnote(_('Учебная сессия'), 1);
                    }
                    $result[] = "<p>{$tempModel->name}{$marker}</p>";
                }
            }
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Subject')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('',$result);
        } else {
            return _('Нет');
        }
    }

    public function departmentsCache($field, $select = null, $isPosition = false){

        $key = $isPosition ? 'positions' : 'departments';

        if(!isset($this->departmentCache[$key])) {
            $extraCond = $isPosition ? 'type IN (1,2)' : 'type=0';

            $select = $this->serviceContainer->getService('Orgstructure')->getSelect();
            $select->from('structure_of_organ', array(
                'soid',
                'name',
                'is_manager'
            ));
            $select->where($extraCond);
            $deps = $select->query()->fetchAll();
            $index = array();
            foreach ($deps as $dep) {
                $index[$dep['soid']] = array('name' => $dep['name'], 'is_manager' => $dep['is_manager']);
            }
            $this->departmentCache[$key] = $index;
        }

        $fields = array_filter(array_unique(explode(',', $field)));
        $pluralForm = $isPosition ? 'pluralFormPositionsCount' : 'pluralFormCount';
        $cache = &$this->departmentCache[$key];


        if ($isPosition && is_array($fields) && (count($fields) == 1)) {
            // Если данные представляют собой одну-единственную должность
            $value = $fields[0];
            return $this->updatePositionName($cache[$value]['name'], $value, HM_Orgstructure_OrgstructureModel::TYPE_POSITION, $cache[$value]['is_manager']);

        } else {
            // Во всех остальных случаях (т.е. нет данных или несколько должностей или подразделений)
            // Делаем, как было раньше
            $result = (is_array($fields) && (count($fields) > 1)) ? array('<p class="total">' . Zend_Registry::get('serviceContainer')->getService('Orgstructure')->$pluralForm(count($fields)) . '</p>') : array();

            foreach($fields as $value){
                if (isset($cache[$value])) {
                    $result[] = "<p>{$cache[$value]['name']}</p>";
                }
            }

            if ($result) {
                return implode('', $result);
            } else {
                return _('Нет');
            }
        }
    }

    public function updatePositionName($name, $soid, $type, $isManager)
    {
        if (empty($name)) return '';

        if ($this->serviceContainer->getService('Acl')->inheritsRole($this->serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) return $name;

        if ($type == HM_Orgstructure_OrgstructureModel::TYPE_DEPARTMENT) {
            $name = '<a href="'.$this->view->url(array('module' => 'orgstructure', 'controller' => 'index', 'action' => 'index', 'org_id' => $soid), null, true).'">'.$name.'</a>';
        }

        return $this->view->cardLink(
                $this->view->url(array(
                        'module' => 'orgstructure',
                        'controller' => 'list',
                        'action' => 'card',
                        'org_id' => '',
                        'baseUrl' => '')
                ) . $soid,
                HM_Orgstructure_OrgstructureService::getIconTitle($type, $isManager),
                'icon-custom',
                'pcard',
                'pcard',
                'orgstructure-icon-small ' . HM_Orgstructure_OrgstructureService::getIconClass($type, $isManager)
            ) . $name;
    }

    public function groupsCache($field, $select)
    {
        if(!isset($this->departmentCache['groups'])) {
            $this->departmentCache['groups'] = $this->getService('StudyGroup')->fetchAll()->asArrayOfObjects();
        }

        $fields = array_filter(array_unique(explode(',', $field)));

        foreach ($fields as $value) {
            if (count($this->departmentCache['groups'])) {
                $tempModel = $this->departmentCache['groups'][$value];
                if ($tempModel) {
                    $result[] = '<p><a href="' . $this->view->url(array('module' => 'study-groups', 'controller' => 'users', 'action' => 'index', 'group_id' => ''), null, true) . $tempModel->group_id . '">' . $tempModel->name . '</a></p>';
                }
            }
        }

        if(count($result)) {
            array_unshift($result, '<p class="total">' . Zend_Registry::get('serviceContainer')->getService('StudyGroup')->pluralFormCount(count($result)) . '</p>');
        }

        if ($result)
            return implode('', $result);
        else
            return _('Нет');
    }

    public function displayTags($itemId, $itemType, $forGrid = true)
    {
        if ( $tags = Zend_Registry::get('serviceContainer')->getService('Tag')->getStrTagsByIds($itemId, $itemType, $forGrid) ) {
            return $tags;
        }
        return '';
    }

    public function updateGroupColumn($field, $id)
    {
        if ($field == $id) {
            return _('Да');
        }
        return _('Нет');
    }
}