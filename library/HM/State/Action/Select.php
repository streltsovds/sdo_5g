<?php
//
class HM_State_Action_Select extends HM_State_Action
{

    public function _render($params, $readOnly)
    {

        $view = Zend_Registry::get('view');

        $view->addScriptPath(dirname(__FILE__));

        //$view->stateId = $this->getState()->getProcess()->getStateId();
        //$view->forState = get_class($this->getState());
        $view->selectName = $params['name'];
        $view->value = $params['value'];
        $view->values = $params['values'];
        $view->params = (isset($params['params'])) ? $params['params'] : array();
        $view->textDesc = $params['text'];
        $view->important = $params['important'];


        if($readOnly == false){
            return $view->render('view/select.tpl');
        }else{
            return $view->render('view/select-read.tpl');
        }

    }


}
