<?php


/**
 * Class HM_View_Helper_HmVue
 */
class HM_View_Helper_HmVue extends HM_View_Helper_Abstract {

    /** @var string[] массив компонентов Vuejs  */
    protected $_components = array();

    /** @var string[] массив данных главного инстанса Vuejs  */
    protected $_data_items = array();

    protected $_isMainLayoutShown;

    public $isIe = false;

    /**
     * @var string Режим разработки / продакшн
     *
     * @todo добавить смену dev / prod
     */
    protected $_mode = '';



    public function hmVue()
    {
        $this->_mode = APPLICATION_ENV;
        return $this;
    }
    public function showMainLayout() {
        return isset($this->_isMainLayoutShown) ? $this->_isMainLayoutShown : true;
    }

    public function setMainLayoutVisibility($is_visible = true) {
        $this->_isMainLayoutShown = $is_visible;
    }

    public function isIE() {

        return $this->isIe;
    }

    public function init() {
        $this->addVueHeadScript();

        // регистрируем поле флага открытия главной навигации
        $this->registerDataItem('isDrawerShown', new HM_JsExpression('false'));


        $this->registerDataItem('global_scope', new HM_JsExpression('window'));

    }



    private function addVueHeadScript() {

        // @todo: проверять актуальность версии
        if ($this->_mode === 'development' && (Zend_Registry::get('config')->webpack && Zend_Registry::get('config')->webpack->devserver)) {
            $address = Zend_Registry::get('config')->webpack->devserver->address;
            $this->view->VueScript()->appendFile($address.'runtime.js');
            $this->view->VueScript()->appendFile($address.'app.js');
        } else {
            $this->view->VueScript()->mix('runtime.js');
            $this->view->VueScript()->mix('chunk-vendors.js');
            $this->view->VueScript()->mix('app.js');
            $this->view->VueScript()->mix('chunk-vendors.css');
            $this->view->VueScript()->mix('app.css');
        }

        if ($this->_mode === 'production') {
            $this->view->VueScript()->mix('chunk-vendors.css');
            $this->view->VueScript()->mix('app.css');
        }

        // инициализация полифиллов для IE11
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('~MSIE|Internet Explorer~i', $user_agent) || (strpos($user_agent, 'Trident/7.0; rv:11.0') !== false)) {
            $this->view->VueScript()->prependFile('/js/polyfills/polyfill.min.js', 'text/javascript');
            $this->view->VueScript()->prependFile('/js/polyfills/es6-promise.auto.min.js', 'text/javascript');
            $this->isIe = true;
        }
    }

    /**
     * Зарегистрировать vue компонент
     *
     * Добавляет имя компонента в массив компонентов
     *
     * @param string $component_name название компонента
     * @return void
     */
    public function registerComponent($component_name)
    {
        $this->_components[$component_name] = $component_name;
        $this->addComponentScript($component_name);
        $this->addComponentCss($component_name);

    }

    /**
     * Получить массив компонентов vue
     *
     * @return string[] массив имен компонентов
     */
    public function getComponents() {
        return $this->_components;
    }

    /**
     * Получить массив компонентов vue в виде JS объекта
     *
     * @return string массив компонентов как JS объект
     */
    public function getComponentsAsJSObject() {
        $as_json = json_encode($this->_components);
        return preg_filter('/"/', '', $as_json);
    }

    /**
     * Добавляет скрипты для компонентов в head
     *
     * @param string $component_name название компонента
     *
     * @todo по хорошему перенести бы всё это вниз перед закрывающим тэгом
     */
    private function addComponentScript($component_name) {
        // @todo добавить dev/prod
        if ($this->_mode === 'development') {
            if ($component_name === 'hmVueApp') {
                $this->view->VueScript()->prependFile('/frontend/components/'.$component_name.'/dist/'.$component_name.'.umd.js');
            } else {
                $this->view->VueScript()->appendFile('/frontend/components/'.$component_name.'/dist/'.$component_name.'.umd.js');
            }
        } else {
            if ($component_name === 'hmVueApp') {
                $this->view->VueScript()->prependFile('/frontend/components/'.$component_name.'/dist/'.$component_name.'.umd.min.js');
            } else {
                $this->view->VueScript()->appendFile('/frontend/components/'.$component_name.'/dist/'.$component_name.'.umd.min.js');
            }
        }
    }

    /**
     * Добавляет стили для компонентов в head
     *
     * @param string $component_name название компонента
     */
    private function addComponentCss($component_name) {

            $this->view->headLink()->appendStylesheet('/frontend/components/'.$component_name.'/dist/'.$component_name.'.css');

    }

    /**
     * Зарегистрировать поле в data главного инстанса Vue
     *
     *
     * @param string $name ключ поля
     * @param string $value значение поля
     * @return void
     */
    public function registerDataItem($name, $value = null)
    {
        if (is_null($value)) {
            $value = $name;
        }

        $this->_data_items[] = array(
            'name' => $name,
            'value' => $value
        );
    }

    /**
     * Получить массив данных главного инстанса vue
     *
     * @return string[] массив имен компонентов
     */
    public function getDataItems() {
        return $this->_data_items;
    }


    /**
     * Получить массив данных главного инстанса vue в виде JS объекта
     *
     * @return string js объект данных
     */
    public function getDataItemsAsJSObject() {
        $js = $nojs = array();

        // some test cases
        //$this->_data_items[] = array('name' => 'number', 'value' => 2345);
        //$this->_data_items[] = array('name' => 'string', 'value' => '2345');
        //$this->_data_items[] = array('name' => 'jsExpr', 'value' => new HM_JsExpression('2345'));

        foreach ($this->_data_items as $item) {
            if (!$item['value'] instanceof HM_JsExpression) {
                $nojs[$item['name']] = $item['value'];
            } else {
                $js[$item['name']] = $item['value'];
            }
        }
        $ret = count($nojs) ? json_encode($nojs) : '{}';
        $ret = count($nojs) ? rtrim($ret,"}").',' : rtrim($ret,"}");

        foreach ($js as $key => $item) {
            $ret.='"'.$key.'":'.$item->string.',';
        }

        $ret.='}';

        // убираем запятую в конце объекта, чтобы не падало в IE
        $ret = preg_filter('/,}/', '}', $ret);

        return $ret;
    }
}