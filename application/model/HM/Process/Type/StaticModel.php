<?php

/**
 * @property array states
 */
class HM_Process_Type_StaticModel extends HM_Process_ProcessModel
{
    public function isStatic()
    {
        return true;
    }
    
    public function isStrict()
    {
        return true;
    }
    
    public function getStateClass($state)
    {
        return get_class($state);
    }
    
    public function getChain($all = false)
    {
        $states = $chain = array();
        foreach ($this->states['state'] as $state) {
            $state['visible'] = isset($state['visible']) ? $state['visible'] : null;
            if ($all == true || $state['visible'] != "false" || $state['visible'] == "true") {
                $states[] = $state['class'];
            }
        }
        for ($i = 0; $i < count($states); $i++) {
            if (isset($states[$i + 1]) && $states[$i + 1] != '') {
                $chain[$states[$i]] = $states[$i + 1];
            }
        }        
        return $chain;
    }    
    
    public function getStateTitle($state)
    {
        $class = get_class($state);
        foreach ($this->states['state'] as $state) {
            if ($state['class'] == $class) return $state['name'];
        }        
        return false;
    } 

    // не применимо к статичным процессам
    public function _getStateParams()
    {
        return array();
    }
        
    public function addStateParams()
    {
        return true;
    }
}