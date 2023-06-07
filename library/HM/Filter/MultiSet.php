<?php
require_once 'Zend/Filter/Interface.php';
require_once 'HTMLPurifier/HTMLPurifier.auto.php';

class HM_Filter_MultiSet implements Zend_Filter_Interface
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value
     *
     * @param  string $value
     * @return string
     */
    
    var $_name;
    var $_dependences;
    
    public function __construct($name, $dependences = array())
    {
        $this->_name = $name;
        $this->_dependences = $dependences;
    }
    
    // фильтр для преобразования плоского POST'а в красивый массив
    public function filter($value)
    {
        $return = array(); 
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        if ($request->isPost()) {
            $data = $request->getParams();
            foreach (array_keys($data) as $key) {
                if (strpos($key, $this->_name . '_') !== false) {
                    list(, $itemName, $itemId) = explode('__', $key);

                    // TODO: как-то автоматически определять нужные поля
                    // и наличие валидаторов вещественных чисел у поля
                    if ($itemName === HM_Form_Element_MultiSet::ITEM_WEIGHT) {
                        $filter = new HM_Filter_FloatPoint();

                        if (is_array($data[$key])) {
                            foreach ($data[$key] as $i => &$v) {
                                $v = $filter->filter($v);
                            }
                            unset($v);
                        } else {
                            $data[$key] = $filter->filter($data[$key]);
                        }

                        // TODO: нужно или нет?
                        // $request->setParam($key, $data[$key]);
                    }

                    if ($itemId != HM_Form_Element_MultiSet::ITEMS_NEW) {
                        $return[$itemId][$itemName] = $data[$key];
                    } else {
                        $return[HM_Form_Element_MultiSet::ITEMS_NEW][$itemName] = $data[$key];
                    }
                }
            }
        }
        return count($return) ? $return : $value;
    }
}
