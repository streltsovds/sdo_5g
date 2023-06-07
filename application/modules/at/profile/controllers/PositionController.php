<?php
class Profile_PositionController extends HM_Controller_Action_Profile
{
    use HM_Controller_Action_Trait_Grid;

    public function listAction()
    {
        $this->gridId = $gridId = ($this->_profile->profile_id) ? "grid{$this->_profile->profile_id}" : 'grid';
        $switcher = $this->getSwitcherSetOrder(null, 'fio_ASC');
        
        $default = new Zend_Session_Namespace('default');
    	if (!isset($default->grid['profile-position-list'][$gridId])) {
    		$default->grid['profile-position-list'][$gridId]['filters']['profile'] = $this->_profile->profile_id;
    	}
    	
        $order = $this->_request->getParam("order{$gridId}");
        
        if ($order == ''){
            $this->_request->setParam("order{$gridId}", 'fio_ASC');
        }

        $select = $this->getService('Orgstructure')->getSelect();
        $select->from(array('so' => 'structure_of_organ'), array(
            'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)"),
            'mid' => 'so.mid',
            'is_in_profile' => 'ap.name', // profile и profile_id выдают дополнительное текстовое условие
            'so.soid',
            'so.name',
            'org_id' => 'so.owner_soid',
            'parent_name' => 'sop.name',
        ));

        $select
            ->joinLeft(array('p' => 'People'), 'so.mid = p.MID', array())
            ->joinLeft(array('ap' => 'at_profiles'), 'so.profile_id = ap.profile_id AND ap.profile_id = ' . $this->_profile->profile_id, array())
            ->joinLeft(array('sop' => 'structure_of_organ'), 'so.owner_soid = sop.soid', array())
            ->where('so.type IN (?)', array(HM_Orgstructure_OrgstructureModel::TYPE_POSITION, HM_Orgstructure_OrgstructureModel::TYPE_VACANCY))
            ->group(array('so.soid', 'so.type', 'so.name', 'so.mid', 'so.owner_soid', 'p.LastName', 'p.FirstName', 'p.Patronymic', 'ap.name', 'sop.name'))
            ->order('so.type');

//         if ($notAll){
//             $select->joinInner(array('ap' => 'at_profiles'), 'so.profile_id = ap.profile_id', array());
//         }

        if ($switcher == self::FILTER_STRICT) {
             $select->where('so.profile_id = ?', $this->_profile->profile_id);
        }

//        echo $select->__toString(); exit();

        $grid = $this->getGrid($select, array(
            'soid' => array('hidden' => true),
            'mid' => array('hidden' => true),
            'fio' => array(
                'title' => _('ФИО пользователя'),
                'decorator' => '<a href="'.$this->view->url(array('module' => 'user', 'controller' => 'report', 'action' => 'index', 'gridmod' => null,'user_id' => '', 'baseUrl' => ''), null, true) . '{{mid}}'.'">'. '{{fio}}</a>'
            ),
//            'profile' => array('hidden' => true),
            'org_id' => array('hidden' => true),
            'parent_name' => array(
                'title' => _('Подразделение'),
            ),
            'name' => array(
                'title' => _('Должность'),
            ),
        ),
            array(
                'org_id' =>
                    array(
                        'callback' => array(
                            'function'=>array($this, 'orgFilter'),
                            'params'=>array()
                        )
                    ),
                'name' => null,
                'parent_name' => null,
                'fio' => null,
            ),
            $gridId
        );
//        if(!$this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_DEAN)) {
            $grid->addMassAction(
                array(
                    'module' => 'profile',
                    'controller' => 'position',
                    'action' => 'assign',
                ),
                _('Назначить профиль должности'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );

            $grid->addMassAction(
                array(
                    'module' => 'profile',
                    'controller' => 'position',
                    'action' => 'unassign',
                ),
                _('Отменить назначение профиля должности'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
 //       }
        $grid->setGridSwitcher([
            'label' => _('Показать всех'),
            'title' => _('Показать всех'),
            'param' => self::SWITCHER_PARAM_DEFAULT,
            'modes' => [self::FILTER_STRICT, self::FILTER_ALL],
        ]);

        if ($switcher == self::FILTER_ALL) {
            $grid->setClassRowCondition("'{{is_in_profile}}'", 'success', '');
        }

        $this->view->grid = $grid;
    }

    public function assignAction()
    {
        $gridId = ($this->_profile->profile_id) ? "grid{$this->_profile->profile_id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
        if (strlen($postMassIds)) {

            $ids = explode(',', $postMassIds);
            if (count($ids)) {

                $this->getService('AtProfile')->assign($this->_profile->profile_id, $ids);
                $this->_flashMessenger->addMessage(_('Профиль успешно назначен'));
            }
        }
        $this->_redirectToIndex();
    }

    public function unassignAction()
    {
        $gridId = ($this->_profile->profile_id) ? "grid{$this->_profile->profile_id}" : 'grid';
        $postMassIds = $this->_getParam("postMassIds_{$gridId}", '');
    	if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $this->getService('AtProfile')->unassign($ids);
                $this->_flashMessenger->addMessage(_('Назначение успешно отменено'));
            }
        }
        $this->_redirectToIndex();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('list', 'position', 'profile', array('profile_id' => $this->_profile->profile_id));
    }
    
    public function updateStatus($status)
    {
        return ($status != '') ?  _('Да') : _('Нет');
    }

    public function orgFilter($data)
    {
        $field = $data['field'];
        $value = $data['value'];
        $select = $data['select'];

        if(strlen($value) > 1){
            $fetch = $this->getService('Orgstructure')->fetchAll(array('name LIKE LOWER(?)' => "%" . $value . "%"));

            $data = $fetch->getList('soid', 'name');
            $select->where('so.owner_soid IN (?)', array_keys($data));
        }
    }
}
