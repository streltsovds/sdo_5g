<?php
class HM_View_Helper_HeadSwitcher extends HM_View_Helper_Abstract
{
    protected $_params = array();
    /**
    * @param $params = array('module', 'controller', 'action', 'switcher')
    * @param $switcher_id = null
    * @return string
    */
    public function headSwitcher($params, $switcher_id = null, $disabledMods = array())
    {
        $this->_params = $params;
        if(!$switcher_id) $switcher_id = $params['module'];
        $mods = array();
        foreach ($this->$switcher_id() as $mod) {
            if (!in_array($mod['switcher'], $disabledMods)) {
                $mods[] = $mod;
            }
        };

        $role = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();

        $switcher = array();
        foreach ($mods as $mode){

            $allow = false;
            if (isset($mode['role']) && count($mode['role'])) {
                foreach($mode['role'] as $roleId => $isEnabled) {
                    if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole($role, $roleId)) {
                        if ($isEnabled) {
                            $allow = true;
                        }
                    }
                }
            } else {
                $allow = true;
            }

            if (!$allow) continue;
            //if(!$mode['role'][$role]) continue;
            if($this->isCurrent($params, $mode)){
                $switcher[] = '<b title="' . _('Режим') . ': ' . $mode['alt'] . '">' . $mode['title'] . '</b>';
                $current = $mode['switcher'];
            }
            else {
                $url = array('module', 'controller', 'action', 'switcher');
                $url = array_intersect_key($mode, array_flip($url));
                if(isset($mode['params']) && is_array($mode['params'])) $url = $url + $mode['params'];

                $switcher[] = '<a href=' . $this->view->url($url) . ' title="' . _('Режим') . ': ' . $mode['alt'] . '">' . $mode['title'] . '</a>';
            }

        }
        
        if(count($switcher) < 2) return false;
        $switcher = implode('', $switcher);

        $this->view->title = _('Режим');
        $this->view->switcher = $switcher;
        $this->view->switcher_id = $switcher_id;
        $this->view->current = $current;
        // какой некрасивый код.(
        $this->view->clearfix = !(
            ($switcher_id == "lesson" && $current == "index") ||
            ($switcher_id == "materialresource") ||
            ($switcher_id == "projectmaterialresource") ||
            ($switcher_id == "subject") ||
            ($switcher_id == "result") ||
            ($switcher_id == "assign") ||
            ($switcher_id == "programm") || 
            ($switcher_id == "vacancyCard") || 
            ($switcher_id == "profile") || 
            ($switcher_id == "profileCard") || 
            ($switcher_id == "quest")
        );

        return $this->view->render('head-switcher.tpl');
    }

    private function course(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_edit" style="padding-left: 4px;">'._('редактирование').'</div>',
            'module' => 'course',
            'controller' => 'constructor',
            'action' => 'index',
            'switcher' => 'edit',
            'alt' => _('редактирование'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => ($this->_params['subject_id'] && $this->_params['owner'] == $this->_params['subject_id']),
                HM_Role_Abstract_RoleModel::ROLE_DEAN    => ($this->_params['subject_id'] && $this->_params['owner'] == $this->_params['subject_id'])
            )
        );
        $mods[] = array(
            'title'=> '<div class="list_view">'._('просмотр').'</div>',
            'module' => 'subject',
            'controller' => 'course',
            'action' => 'index',
            'switcher' => 'index',
            'params' => array('key' => null),
            'alt' => _('просмотр'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1,
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1
            )
        );

        return $mods;

    }

    private function lesson(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'lesson',
            'controller'=> 'list',
            'action'    => 'index',
            'alt' => _('таблица'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'subject',
            'controller'=> 'lessons',
            'action'    => 'edit',
            'alt' => _('список'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1,
                HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1)
        );

        return $mods;

    }
    private function meeting(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'meeting',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('таблица'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1
            )
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'meeting',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'my',
            'alt' => _('список'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
                HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_date">'._('календарь').'</div>',
            'module'    => 'meeting',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'calendar',
            'alt' => _('календарь'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
//                HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 1
            )
        );
//        $mods[] = array(
//            'title'     => '<div class="list_timeline">'._('таймлайн').'</div>',
//            'module'    => 'meeting',
//            'controller'=> 'list',
//            'action'    => 'index',
//            'switcher'  => 'timeline',
//            'alt' => _('таймлайн'),
//            'role'=> array(
//                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
////                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
//                HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 1)
//        );

        return $mods;

    }

    private function quest(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('Подробный отчёт по ответам на вопросы').'</div>',
            'module'    => 'quest',
            'controller'=> 'report',
            'action'    => 'poll',
            'switcher'  => 'poll-detailed',
            'alt' => _('Подробный отчёт по ответам на вопросы'),
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('Сводный отчёт по вопросам').'</div>',
            'module'    => 'quest',
            'controller'=> 'report',
            'action'    => 'questions',
            'switcher'  => 'questions',
            'alt' => _('Сводный отчёт по вопросам'),
        );
        $mods[] = array(
            'title'     => '<div class="list_chart">'._('Диаграммы распределения ответов').'</div>',
            'module'    => 'quest',
            'controller'=> 'report',
            'action'    => 'diagram',
            'switcher'  => 'diagram',
            'alt' => _('Диаграммы распределения ответов'),
        );

        return $mods;

    }

    private function materialresource(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'resource',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('таблица'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1
            )
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'subject',
            'controller'=> 'materials',
            'action'    => 'index',
            'switcher'  => 'materialresource',
            'alt' => _('список'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1,
                HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1
            )
        );
        
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'subject',
            'controller'=> 'index',
            'action'    => 'courses',
            'switcher'  => 'index_courses',
            'alt' => _('таблица'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1
            )
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'subject',
            'controller'=> 'materials',
            'action'    => 'index',
            'switcher'  => 'materialresource_courses',
            'alt' => _('список'),
            'params' => array('from'=>'indexcourses'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1,
                HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1)
        );

        return $mods;

    }

    private function projectmaterialresource(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'resource',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('таблица'),
            'role'=> array(
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
            )
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'project',
            'controller'=> 'materials',
            'action'    => 'index',
            'switcher'  => 'materialresource',
            'alt' => _('список'),
            'role'=> array(
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
            )
        );
        return $mods;

    }

    private function contacts(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'message',
            'controller'=> 'contact',
            'action'    => 'index',
            'switcher'  => 'grid',
            'alt' => _('таблица')
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'message',
            'controller'=> 'contact',
            'action'    => 'index',
            'switcher'  => 'list',
            'alt' => _('список')
        );

        return $mods;

    }

    private function resource(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_edit" style="padding-left: 4px;">'._('редактирование').'</div>',
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'edit-content',
            'switcher' => 'edit-content',
            'alt' => _('редактирование'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => ($this->_params['subject_id'] && $this->_params['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL),
                HM_Role_Abstract_RoleModel::ROLE_DEAN => ($this->_params['subject_id'] && $this->_params['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL),
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => ($this->_params['subject_id'] && $this->_params['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL),
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => ($this->_params['subject_id'] && $this->_params['location'] == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL),
                HM_Role_Abstract_RoleModel::ROLE_DEVELOPER => !$this->_params['subject_id'],
                HM_Role_Abstract_RoleModel::ROLE_MANAGER => !$this->_params['subject_id']
            )
        );
        $mods[] = array(
            'title' => '<div class="list_view">'._('просмотр').'</div>',
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'index',
            'switcher' => 'index',
            'alt' => _('просмотр'),
            'role'=> array(
                HM_Role_Abstract_RoleModel::ROLE_DEAN => 1,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1,
                HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1,
//                HM_Role_Abstract_RoleModel::ROLE_MODERATOR => 1,
                HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1,
                HM_Role_Abstract_RoleModel::ROLE_DEVELOPER => 1,
                HM_Role_Abstract_RoleModel::ROLE_MANAGER => 1
            )
        );
    
        return $mods;

    }

    private function subject(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'subject',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('таблица'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1, HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 0)
        );
        $mods[] = array(
            'title'     => '<div class="list_date">'._('календарь').'</div>',
            'module'    => 'subject',
            'controller'=> 'list',
            'action'    => 'calendar',
            'base'      => 2,
            'switcher'  => 'calendar',
            'alt'       => _('календарь'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 0, HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 0)
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'subject',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'list',
            'alt' => _('список'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 0, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1, HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_program">'._('с группировкой по программам').'</div>',
            'module'    => 'subject',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'programm',
            'alt' => _('программы'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 0, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 0, HM_Role_Abstract_RoleModel::ROLE_ENDUSER => 1)
        );
        return $mods;
    }

    private function programm(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_text" style="padding-left: 4px;">'._('структура').'</div>',
            'module'    => 'programm',
            'controller'=> $this->_params['controller'],
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('структура'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1, HM_Role_Abstract_RoleModel::ROLE_DEAN => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_date">'._('календарь').'</div>',
            'module'    => 'programm',
            'controller'=> $this->_params['controller'],
            'action'    => 'calendar',
            'switcher'  => 'calendar',
            'alt'       => _('календарь'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1, HM_Role_Abstract_RoleModel::ROLE_DEAN => 1)
        );
        return $mods;
    }

    private function session(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_text" style="padding-left: 4px;">'._('список').'</div>',
            'module'    => 'session',
            'controller'=> 'event',
            'action'    => 'list',
            'switcher'  => 'list',
            'alt' => _('список'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_date">'._('календарь').'</div>',
            'module'    => 'session',
            'controller'=> 'event',
            'action'    => 'calendar',
            'switcher'  => 'calendar',
            'alt'       => _('календарь'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1)
        );
        return $mods;
    }

    private function profile(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'profile',
            'controller'=> 'criterion',
            'action'    => 'corporate',
            'switcher'  => 'grid',
            'alt' => _('таблица'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        $mods[] = array(
            'title' => '<div class="list_text" style="padding-left: 4px;">'._('профиль успешности').'</div>',
            'module'    => 'profile',
            'controller'=> 'competence',
            'action'    => 'index',
            'switcher'  => 'form',
            'alt' => _('профиль успешности'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_chart">'._('просмотр').'</div>',
            'module'    => 'profile',
            'controller'=> 'report',
            'action'    => 'competence',
            'switcher'  => 'report',
            'alt'       => _('просмотр'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        return $mods;
    }

    private function professional(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'profile',
            'controller'=> 'criterion',
            'action'    => 'professional',
            'switcher'  => 'grid',
            'alt' => _('таблица'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        $mods[] = array(
            'title' => '<div class="list_text" style="padding-left: 4px;">'._('профиль успешности').'</div>',
            'module'    => 'profile',
            'controller'=> 'competence',
            'action'    => 'professional',
            'switcher'  => 'form',
            'alt' => _('профиль успешности'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1, HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        return $mods;
    }

    private function project(){

        $mods = array();
        $mods[] = array(
            'title'     => '<div class="list_date">'._('календарь').'</div>',
            'module'    => 'project',
            'controller'=> 'list',
            'action'    => 'calendar',
            'base'      => 2,
            'switcher'  => 'calendar',
            'alt'       => _('календарь'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1, HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT => 0)
        );
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('таблица').'</div>',
            'module'    => 'project',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('таблица'),
            'role'=> array(HM_Role_Abstract_RoleModel::ROLE_CURATOR => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_text">'._('список').'</div>',
            'module'    => 'project',
            'controller'=> 'list',
            'action'    => 'index',
            'switcher'  => 'list',
            'alt' => _('список'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_CURATOR => 0, HM_Role_Abstract_RoleModel::ROLE_PARTICIPANT => 1)
        );
        return $mods;

    }
    
    private function result(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_cal" style="margin-left: 4px;">'._('таблица').'</div>',
            'module'    => 'lesson',
            'controller'=> 'result',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt' => _('журнал попыток'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1, HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_text" style="margin-left: 4px;">'._('список').'</div>',
            'module'    => 'lesson',
            'controller'=> 'result',
            'action'    => 'listlecture',
            'switcher'  => 'listlecture',
            'alt' => _('сводные результаты по учебному модулю'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1, HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1)
        );
        $mods[] = array(
            'title'     => '<div class="list_skillsoft">Skillsoft</div>',
            'module'    => 'lesson',
            'controller'=> 'result',
            'action'    => 'skillsoft',
            'switcher'  => 'skillsoft',
            'alt' => _('подробная статистика Skillsoft'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 1, HM_Role_Abstract_RoleModel::ROLE_STUDENT => 1)
        );

        return $mods;
    }

    private function proctored(){

        $mods = array();
        $mods[] = array(
            'title' => '<div class="list_matrix" style="padding-left: 4px;">'._('Просмотр участников').'</div>',
            'module'    => 'lesson',
            'controller'=> 'list',
            'action'    => 'video',
            'switcher'  => 'video',
            'alt' => _('Просмотр участников'),
        );
        $mods[] = array(
            'title' => '<div class="list_cal" style="padding-left: 4px;">'._('Список пользователей').'</div>',
            'module'    => 'lesson',
            'controller'=> 'list',
            'action'    => 'proctored',
            'switcher'  => 'proctored',
            'alt' => _('Список пользователей'),
        );
        return $mods;

    }

    private function assign()
    {
        $mods = array();

        $mods[] = array(
            'title'     => '<div class="list_cal" style="margin-left: 4px;">'._('таблица').'</div>',
            'module'    => 'assign',
            'controller'=> 'teacher',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt'       => _('таблица'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 0, HM_Role_Abstract_RoleModel::ROLE_STUDENT => 0),
            'params'    => array('MID' => null)
        );
         $mods[] = array(
            'title' => '<div class="list_date" style="margin-left: 4px;">'._('календарь').'</div>',
            'module'    => 'assign',
            'controller'=> 'teacher',
            'action'    => 'calendar',
            'switcher'  => 'calendar',
            'alt' => _('календарь'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_DEAN => 1, HM_Role_Abstract_RoleModel::ROLE_TEACHER => 0, HM_Role_Abstract_RoleModel::ROLE_STUDENT => 0)
        );
        return $mods;
    }

    private function vacancyCard()
    {
        $mods = array();

        $mods[] = array(
            'title'     => '<div class="list_cal" style="margin-left: 4px;">'._('таблица').'</div>',
            'module'    => 'vacancy',
            'controller'=> 'index',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt'       => _('таблица'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_HR => 1),
        );
        $mods[] = array(
            'title' => '<div class="list_date" style="margin-left: 4px;">'._('календарь').'</div>',
            'module'    => 'vacancy',
            'controller'=> 'report',
            'action'    => 'index',
            'switcher'  => 'report',
            'alt' => _('календарь'),
            'role'  => array(HM_Role_Abstract_RoleModel::ROLE_HR => 1)
        );
        return $mods;
    }

    private function profileCard()
    {
        $mods = array();

        $mods[] = array(
            'title'     => '<div class="list_cal" style="margin-left: 4px;">'._('таблица').'</div>',
            'module'    => 'profile',
            'controller'=> 'index',
            'action'    => 'card',
            'switcher'  => 'index',
            'alt'       => _('таблица'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1),
        );
        $mods[] = array(
            'title' => '<div class="list_date" style="margin-left: 4px;">'._('календарь').'</div>',
            'module'    => 'profile',
            'controller'=> 'report',
            'action'    => 'index',
            'switcher'  => 'report',
            'alt' => _('календарь'),
            'role'      => array(HM_Role_Abstract_RoleModel::ROLE_HR => 1, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER => 1),
        );
        return $mods;
    }

    private function candidate()
    {
        $mods = array();

        $mods[] = array(
            'title'     => '<div class="list_text" style="margin-left: 4px;">'._('автопоиск').'</div>',
            'module'    => 'candidate',
            'controller'=> 'search',
            'action'    => 'index',
            'switcher'  => 'index',
            'alt'       => _('автопоиск'),
            'role'      => array(
                HM_Role_Abstract_RoleModel::ROLE_HR => 1,
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1
            ),
        );
        $mods[] = array(
            'title'     => '<div class="list_srch" style="margin-left: 4px;">'._('поиск по критериям').'</div>',
            'module'    => 'candidate',
            'controller'=> 'search',
            'action'    => 'form',
            'switcher'  => 'form',
            'alt'       => _('поиск по критериям'),
            'role'      => array(
                HM_Role_Abstract_RoleModel::ROLE_HR => 1,
                HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL => 1,
            ),
        );
        return $mods;
    }

    private function forum()
    {
        $reflection = new ReflectionClass('HM_Role_Abstract_RoleModel');
        $roles = array();
        foreach($reflection->getConstants() as $constant => $value){
            if(strpos($constant, 'ROLE_') === 0) $roles[$value] = true;
        }

        $params = array(
            'module'     => 'forum',
            'controller' => 'index',
            'action'     => 'index',
            'role'       => $roles
        );

        return array(
            $params + array(
                'title'      => '<div class="list_text">' . _('Список') . '</div>',
                'alt'        => _('Отображение сообщений в виде плоского списка'),
                'switcher'   => 1,
                'params'     => array('mode' => 'list'),

            ),
            $params + array(
                'title'      => '<div class="list_tree">' . _('Дерево') . '</div>',
                'alt'        => _('Отображение сообщений в виде дерева'),
                'switcher'   => 2,
                'params'     => array('mode' => 'tree'),
            )
        );
    }

    private function isCurrent($params, $mode){
        return ($mode['module'] == $params['module'] && $mode['controller'] == $params['controller'] && $mode['action'] == $params['action'] && $mode['switcher'] == $params['switcher']);
    }
}