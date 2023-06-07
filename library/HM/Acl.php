<?php
use HM_Role_Abstract_RoleModel as Roles;

class HM_Acl extends Zend_Acl
{
    const DIRECT_PASSWORD = 'SDmBOtVP'; // нужна возможность отключить ACL - для авто-тестирования
    const RESOURCE_USER_CONTROL_PANEL = 'user_control_panel';

    const PRIVILEGE_VIEW = 'view';
    const PRIVILEGE_EDIT = 'edit';

    private $_modules = [];

    public function __construct()
    {
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_ENDUSER));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_GUEST));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_STUDENT), [Roles::ROLE_ENDUSER]);
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_PARTICIPANT), [Roles::ROLE_ENDUSER]);
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_TEACHER));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_MODERATOR));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_DEVELOPER));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_MANAGER));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_ATMANAGER));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_ATMANAGER_LOCAL));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_ADMIN));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_SIMPLE_ADMIN), [Roles::ROLE_ADMIN]);
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_CURATOR));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_DEAN));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_DEAN_LOCAL));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_HR_LOCAL));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_HR));
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_LABOR_SAFETY), [Roles::ROLE_DEAN]);
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_LABOR_SAFETY_LOCAL), [Roles::ROLE_DEAN]);
		$this->addRole(new Zend_Acl_Role(Roles::ROLE_USER), [Roles::ROLE_ENDUSER]);
        $this->addRole(new Zend_Acl_Role(Roles::ROLE_SUPERVISOR)); // supervisor != enduser!
        //$this->_setDefaults();
    }

    /**
     * Добавляет к ACL ресурсы модуля по его имени
     * @param string $moduleName - имя модуля
     * @param string $applicationModule - имя модуля приложения (edo, els)
     * @return HM_Acl
     */
    public function addModuleResources($moduleName, $applicationModule = 'els')
    {
        if (!$moduleName) return null;
        $applicationModule = $applicationModule ? strtolower($applicationModule) : 'els';
        $moduleName = strtolower(trim($moduleName));
        $services = Zend_Registry::get('serviceContainer');
        $moduleDirectory = APPLICATION_PATH . '/modules/' . $applicationModule . '/' . $moduleName . '/acls';
        if (is_dir($moduleDirectory) && is_readable($moduleDirectory) && !$this->hasModuleResources($moduleName)) {
            $handle = opendir($moduleDirectory);
            if ($handle) {
                while(false !== ($file = readdir($handle))) {
                    if (in_array($file, ['.', '..'])) continue;
                    if (substr($file, -4) == '.php') {
                        $class = 'HM_Acl_'.substr($file, 0, -4);
                        if (!class_exists($class, false)) {
                            Zend_Loader::loadFile($file, $moduleDirectory);
                            $acl = new $class($services->getService('Acl'));
                            unset($acl);
                        }
                    }
                }
                $this->storeModuleName($moduleName)
                     ->_checkNewModules();
            }
        }
        return $this;
    }

    /**
     * Добавляет имя модуля в список модулей ресурсы которых имеются в ACL
     * @param $moduleName
     * @return HM_Acl
     */
    public function storeModuleName($moduleName)
    {
        if (!$moduleName) return $this;
        $moduleName = strtolower($moduleName);
        if (!$this->hasModuleResources($moduleName)) $this->_modules[] = $moduleName;
        return $this;
    }

    /**
     * Проверяет инициализированы ли в ACL ресурсы заданного модуля
     * @param $moduleName
     * @return bool
     */
    public function hasModuleResources($moduleName)
    {
        if (!$moduleName) return false;
        $moduleName = strtolower($moduleName);
        return in_array($moduleName,$this->_modules);
    }

    /**
     * Проверяет имеются ли в списке ресурсов ресурсы модулей, которые не добавлены,
     * и добавляет ресурсы этих модулей в ACL
     */
    private function _checkNewModules()
    {
        $resources = $this->getResources();
        if (!$resources) return;
        foreach ($resources as $resource) {
            list( ,$moduleName) = explode(':', $resource);
            if ($moduleName && !$this->hasModuleResources($moduleName)) {
                $this->addModuleResources($moduleName);
            }
        }
    }

    public function isCurrentAllowed($resource, $privilege = null)
    {
        if ($this->has($resource)) {
            $currentRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
            if (!$this->isAllowed($currentRole, $resource, $privilege)) {
                return false;
            }
        }
        return true;
    }

    public function addResource($resource, $parent = null)
    {
        if (!$this->has($resource)) {
            return parent::addResource($resource, $parent);
        }
    }

    /**
     * Быстрый способ проверить роль текущего пользователя без постоянного вызова getCurrentUserRole()
     *
     * @param $inherit
     * @param bool $onlyParents
     * @param array $testedAcc
     * @throws Zend_Exception
     */
    public function checkRoles($inherit, $onlyParents = false, $testedAcc = []){
        $currentRole = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole();
        return $this->inheritsRole($currentRole, $inherit, $onlyParents, $testedAcc);
    }

    public function inheritsRole($role, $inherit, $onlyParents = false, $testedAcc = [])
    {
        if ($role == $inherit) return true;

        $unionRoles = [
            Roles::ROLE_PARTICIPANT,
            Roles::ROLE_STUDENT,
            Roles::ROLE_CHIEF,
            Roles::ROLE_USER,
        ];

        if (count(array_intersect((array) $inherit, $unionRoles))) {
            $inherit = array_unique(array_merge((array) $inherit, $unionRoles));
        }

        if(is_array($inherit)) {
            $inherit = array_diff($inherit, $testedAcc);
        }

        if (is_array($inherit)) {
            foreach($inherit as $item) {
                $testedAcc[] = $item;
                if ($this->inheritsRole($role, $item, $onlyParents, $testedAcc)) {
                    return true;
                }
            }
        } else {
            return parent::inheritsRole($role, $inherit, $onlyParents);
        }
        return false;
    }

    /**
     * Одни и те же модуль-контроллер-действие настраиваются по-разному внутри уч.курса и вне него
     */
    public function isSubjectContext()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $subject = $request->getParam('subject');
        $subjectId = $request->getParam('subject_id');

        return $subject ? 'subject' == $subject : $subjectId;
    }

    protected function isProjectContext()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $subject = $request->getParam('subject');
        $projectId = $request->getParam('project_id');

        return $subject ? 'project' == $subject : $projectId;
    }

    // не просто разрешить всем студентам, а убедиться что он именно студент на этом курсе
    public function allowForSubject($acl, $role, $resource)
    {
        $subjectId = $this->isSubjectContext();
        $sc = Zend_Registry::get('serviceContainer');
        $userId = $sc->getService('User')->getCurrentUserId();
        $subjects = $sc->getService('Student')->getSubjects($userId)->getList('subid');
        return $this->allowForContext($acl, $role, $resource, $subjectId, $subjects);
    }

    public function allowForProject($acl, $role, $resource)
    {
        $projectId = $this->isProjectContext();
        $sc = Zend_Registry::get('serviceContainer');
        $userId = $sc->getService('User')->getCurrentUserId();
        $projects = $sc->getService('Participant')->getProjects($userId)->getList('projid');
        return $this->allowForContext($acl, $role, $resource, $projectId, $projects);
    }

    private function allowForContext($acl, $role, $resource, $contextId, $contextValues)
    {
        if ($contextId) {
            if (Roles::ROLE_ENDUSER === $role and in_array($contextId, $contextValues)) {
                $acl->allow($role, $resource);
            }
        } else {
            $acl->allow($role, $resource); // в глобальном контексте
        }
        return true;
    }

    protected function isManager()
    {
        return Zend_Registry::get('serviceContainer')->getService('User')->isManager();
    }

    public function allowForVacancy($acl, $role, $resource)
    {
        return $this->allowForOrgStructure($acl, $role, $resource, 'vacancy_id', 'RecruitNewcomer');
    }

    public function allowForNewcomer($acl, $role, $resource)
    {
        return $this->allowForOrgStructure($acl, $role, $resource, 'newcomer_id', 'RecruitNewcomer');
    }

    public function allowForReserve($acl, $role, $resource)
    {
        return $this->allowForOrgStructure($acl, $role, $resource, 'reserve_id', 'HrReserve');
    }

    private function allowForOrgStructure($acl, $role, $resource, $paramName, $serviceNameForSource)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $id = $request->getParam($paramName);
        $sc = Zend_Registry::get('serviceContainer');

        if ($entity = $sc->getService($serviceNameForSource)->findOne($id)) {
            if ($manager = $sc->getService('Orgstructure')->getManager($entity->position_id)) {
                if ($manager->mid == $sc->getService('User')->getCurrentUserId()) {
                    $acl->allow($role, $resource);
                }
            }
        }
        return false;
    }
}