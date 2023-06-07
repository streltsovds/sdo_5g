<?php
abstract class HM_State_Action
{
    const DECORATE_NONE = 'none';
    const DECORATE_DEFAULT = 'default';
    const DECORATE_NEXT = 'next';
    const DECORATE_SUCCESS = 'success';
    const DECORATE_FAIL = 'fail';
    const DECORATE_INLINE = 'inline';
    const DECORATE_FAIL_INLINE = 'fail_inline';

    /*protected*/ public $_restriction = array();
    protected $_params = array();
    protected $_state  = null;
    protected $_decorate  = null;

    public function __construct($params, $restriction, $state, $decorate = self::DECORATE_DEFAULT)
    {
        $this->_restriction = $restriction;
        $this->_params      = $params;
        $this->_state       = $state;
        $this->_decorate    = $decorate;
    }

    /**
     * Must implemented in child classes. Return rendered action
     * @abstract
     * @param $params
     */
    abstract public function _render($params, $readOnly);

    /**
     *  return rendered string or false if condition wasn't checked...
     *
     * @return bool|void
     */
    public function render()
    {
        if($this->checkRestriction()){
            return $this->_render($this->_params, false);
        }else{
            return $this->_render($this->_params, true);
        }
    }

    public function getState()
    {
        return $this->_state;
    }

    /**
     * Check all restrictions
     *
     * @return bool
     */
    protected function checkRestriction()
    {
        $return = true;
        foreach($this->_restriction as $class => $params){
            $stateClass = get_class($this->getState());

            $explode = explode('_', $stateClass);
            $explode[count($explode)-1] = 'Validator';
            $explode[] = ucfirst($class);

            $classValidator =  implode('_', $explode);

            if(!class_exists($classValidator)){
                $explode = explode('_', 'HM_State_Action');
                $explode[count($explode)] = 'Validator';
                $explode[] = ucfirst($class);
                $classValidator =  implode('_', $explode);
            }

            $validator = new $classValidator($this->getState());

            if($validator->validate($params) == false){
                $return = false;
            }
        }
        return $return;
    }

    /**
     * Функция определяет нужно ли заключать элемент в декораторы.
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @return bool
     */
    public function isDecorated()
    {
        if (isset($this->_params['decorating']) && $this->_params['decorating'] == self::DECORATE_NONE) {
            return false;
        }
        return true;
    }
    
    public function getDecorate()
    {
        return $this->_decorate ? $this->_decorate : self::DECORATE_DEFAULT;
    }

    /**
     * @return HM_View_Extended
     */
    public function getView()
    {
        $view = clone Zend_Registry::get('view');
        $view->addScriptPath(APPLICATION_PATH.'/../library/HM/State/Action');

        return $view;
    }

    public function getFormattedParams()
    {
        $params = $this->_params;

        if ($this->getDecorate() && !array_key_exists("class", $params)) {
            $params['class'] = $this->getDecorate();
        }

        return $params;
    }

}
