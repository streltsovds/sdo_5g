<?php


use HM_Role_Abstract_RoleModel as Roles;

class HM_View_Infoblock_ScreencastBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'screencast';

    protected $_screencasts = array();

    public function screencastBlock($param = null)
    {
        $this->_screencasts = array(
            '' => array(
                'title' =>'',
                Roles::ROLE_ADMIN        => 1,
                Roles::ROLE_SIMPLE_ADMIN => 1,
                Roles::ROLE_GUEST        => 1,
                Roles::ROLE_ENDUSER      => 1,
                Roles::ROLE_SUPERVISOR   => 1,
                Roles::ROLE_TEACHER      => 1,
                Roles::ROLE_DEAN         => 1,
                Roles::ROLE_DEVELOPER    => 1,
                Roles::ROLE_MANAGER      => 1,
            ),
            '01_login' => array(
                'title' =>_('Вход в систему'),
                Roles::ROLE_ADMIN        => 1,
                Roles::ROLE_SIMPLE_ADMIN => 1,
            ),
            '02_create_subject' => array(
                'title' =>_('Cоздание учебного курса'),
                Roles::ROLE_ADMIN => 1,
            ),
            '03_assign_teachers_students' => array(
                'title' =>_('Назначение преподавателей и слушателей'),
                Roles::ROLE_ADMIN => 1,
            ),
            '04_import_module' => array(
                'title' =>_('Импорт готового учебного модуля'),
                Roles::ROLE_ADMIN => 1,
            ),
            '05_create_schedule' => array(
                'title' =>_('Cоздание плана занятий'),
                Roles::ROLE_ADMIN => 1,
            ),
            '06_create_quiz' => array(
                'title' =>_('Cоздание тестов'),
                Roles::ROLE_ADMIN => 1,
            ),
            '07_create_assignment' => array(
                'title' =>_('Cоздание заданий'),
                Roles::ROLE_ADMIN => 1,
            ),
            '08_service_addition_in_courses' => array(
                'title' =>_('Подготовка и проведение вебинаров'),
                Roles::ROLE_ADMIN => 1,
            ),
            '09_include_webinar' => array(
                'title' =>_('Включение вебинара'),
                Roles::ROLE_ADMIN => 1,
            ),
            '10_results and grading' => array(
                'title' =>_('Анализ результатов обучения и выставление оценок'),
                Roles::ROLE_ADMIN => 1,
            ),
        );

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/screencasts/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/screencasts/script.js');

        $currentRole = $this->getService('User')->getCurrentUserRole(true);

        $this->view->screencasts = $this->_getAvailableScreencasts($currentRole);
        $content = $this->view->render('screencastBlock.tpl');
        
        return $this->render($content);
    }

    protected function _getAvailableScreencasts($role)
    {
        $availableScreencasts = array();
        foreach ($this->_screencasts as $key => $params) {
            if(empty($params[$role])) {
                continue;
            }
            $availableScreencasts[$key] = $params['title'];
        }
        return $availableScreencasts;
    }
}