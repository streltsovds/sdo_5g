<?php

class HM_State_Action_Link extends HM_State_Action
{
    public function _render($params, $readOnly)
    {
            if(is_array($params['url']) == true){
                $url = Zend_Registry::get('view')->url($params['url']);
            }else{
                $url = $params['url'];
            }
            if($readOnly == false){
                $confirm = '';
                if ($params['confirm']) {
                    $confirm = ' onclick="return confirm(\''.$params['confirm'].'\')" ';
                }
                return  '<a href="' . $url . '" '.$confirm.'>' . $params['title'] . '</a>';
            }
    }

    public function getFormattedParams() {
        $params = $this->_params;
        $formattedParams = [];

        if(isset($params['url']) && is_array($params['url'])){
            $formattedParams['url'] = Zend_Registry::get('view')->url($params['url']);
        }else{
            $formattedParams['url'] = $params['url'];
        }

        if (isset($params['title']) && $params['title']) {
            $formattedParams['title'] = $params['title'];
        }

        if ($this->getDecorate()) {
            $formattedParams['class'] = $this->getDecorate();
        }

        if (isset($params['confirm']) && $params['confirm']) {
            $formattedParams['confirm'] = $params['confirm'];
        }

        return $formattedParams;
    }


}
