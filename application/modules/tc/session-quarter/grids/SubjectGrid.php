<?php
use HM_Role_Abstract_RoleModel as Roles;

class HM_SessionQuarter_Grid_SubjectGrid extends HM_Grid {

    protected static $_defaultOptions = array(
        'sessionQuarterId' => 0,
        'defaultOrder' => 'session_quarter_id_ASC',
    );

    protected function _initColumns()
    {
        $view = $this->getView();

        $this->_columns = array(
            'subject_id' => array('hidden'=> true),
            'subject' => array(
                'title'=> _('Название курса'),
                'decorator' => // как правильно задать ширину?
                    '<div style="display: inline-block; width: 600px;">' .
                    $view->cardLink($view->url(array('module' => 'subject', 'controller' => 'list', 'action' => 'card', 'baseUrl' => '/', 'subject_id' => '')) . '{{subject_id}}',_('Карточка')) .
                    ' <a href="' . $view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'baseUrl' => '/', 'subject_id' => '{{subject_id}}', 'no_context' => 1, 'detailed' => 1), null, true, false) . '">{{subject}}</a>' .
                    '</div>'
            ),
            'courses' => array(
                'title' => _('Учебные сессии'),
                'callback' => array(
                    'function' => array($this->getController(), 'coursesCache'),
                    'params' => array('{{courses}}', $this->_sourceOriginal)
                )
            ),
            'count_total' => array(
                'title' => _('Всего участников'),
                'decorator' => '<a href="'.$view->url(array('module' => 'session-quarter', 'controller' => 'student', 'action' => 'index', 'gridmod' => null, 'session_quarter_id' => $this->getOption('sessionQuarterId'), 'subject_namegrid' => '')) . '{{subject}}'.'">'.'{{count_total}}</a>',
            ),
            'count_students' => array(
                'title' => _('Из них назначено на сессии'),
//                'decorator' => '<a href="'.$view->url(array('module' => '?', 'controller' => '?', 'action' => '?', 'gridmod' => null, '?' => '?')) . '{{subject_id}}'.'">'.'{{count_students}}</a>',
            ),
            'count_graduated' => array(
                'title' => _('Из них прошли обучение'),
//                'decorator' => '<a href="'.$view->url(array('module' => '?', 'controller' => '?', 'action' => '?', 'gridmod' => null, '?' => '?')) . '{{subject_id}}'.'">'.'{{count_graduated}}</a>',
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'subject' => null,
            'count_total' => null,
            'count_students' => null,
            'count_graduated' => null,
        ));
    }




    public function getGridId()
    {
        return parent::getGridId();
    }
}