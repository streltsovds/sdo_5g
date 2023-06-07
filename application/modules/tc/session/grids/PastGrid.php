<?php

class HM_Session_Grid_PastGrid extends HM_Grid
{

    protected static $_defaultOptions = array(
        'sessionId'    => 0,
        'defaultOrder' => 'year_DESC',

    );

    public function init($source = null)
    {
        parent::init($source);

    }

    protected function _initColumns()
    {
        $view       = $this->getView();

        $sessionViewUrl = $view->url(array(
            'module'      => 'session',
            'controller'  => 'list',
            'action'      => 'view',
            'session_id' => ''
        ));
        $sessionViewUrl .= '{{session_id}}';

        $this->_columns = array(
            'session_id' => array('hidden'=> true),
            'year' => array('hidden'=> true),
            'name' => array(
                'title' => _('Сессия планирования'),
                'position' => 1,
            ),
            'course' => array(
                'title' => _('Курс'),
                'position' => 2,
            ),
            'type' => array(
                'title' => _('Тип обучения'),
                'callback' => array(
                    'function' => array($this, 'updateTypes'),
                    'params' => array('{{type}}'),
                ),
                'position' => 3,
            ),
            'price' => array(
                'title' => _('Стоимость'),
                'position' => 4,
            ),
            'end' => array(
                'title' => _('Дата прохождения'),
                'position' => 5,
            ),
            'certificate' => array(
                'title' => _('Ссылка на сертификат'),
                'callback' => array(
                    'function' => array($this, 'getDownloadLink'),
                    'params' => array('{{certificate}}'),
                ),
                'position' => 6,
            ),
        );
    }

    protected function _initFilters(HM_Grid_FiltersList $filters)
    {
        $filters->add(array(
            'name' => null,
            'course' => null,
            'price' => null,
            'type' => array(
                'values' => HM_Tc_Application_ApplicationModel::getApplicationCategories(),
            ),
            'end' => array('render' => 'Date')
        ));
    }

    protected function _initSwitcher(HM_Grid_Switcher $switcher)
    {

    }

    public function _initActions(HM_Grid_ActionsList $actions)
    {

    }

    protected function _initMassActions(HM_Grid_MassActionsList $massActions)
    {

    }

    protected function _initGridMenu(HM_Grid_Menu $menu)
    {

    }

    public function updateTypes($type)
    {
        $types = HM_Tc_Application_ApplicationModel::getApplicationCategories();
        return isset($types[$type]) ? $types[$type] : '';
    }

    public function getDownloadLink($certificate)
    {
        $url = Zend_Registry::get('view')->url(
            array(
                'baseUrl' => false,
                'module' => 'file',
                'controller' => 'get',
                'action' => 'certificate',
                'certificate_id' => $certificate,
                'download' => 1
            ), null, true
        );
        return $certificate ? '<a href="' . $url . '">' . _('Скачать') . '</a>' : _('Нет сертификата');
    }
}