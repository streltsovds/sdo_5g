<?php
/** @deprecated в пользу @see HM_View_Helper_VueMultiSet */
class HM_View_Helper_MultiSet extends Zend_View_Helper_FormElement
{
    protected $id;

    /**
     * @param $name
     * @param array $values - массив значений
     * @return string
     */
    public function multiSet($name, $value = array(), $options = array())
    {
        $header = $set ='';
        $this->id = $this->view->id('ms');

        $dependences = $options['dependences'];
        foreach ($dependences as $element) {
            if (empty($element) || !is_subclass_of($element, 'Zend_Form_Element')) continue;
            $extraClass = '';
            $type = strtolower(str_replace(array('Zend_Form_Element_', 'HM_Form_Element_'), '', $element->getType()));
            $classes = explode(' ', trim($element->getAttrib('class')));
            foreach ($classes as $class) {
                if (!empty($class) && strpos($class, 'multiset') === false) {
                    $extraClass .= ' multiset-class-' . $class;
                }
            }
            $header .= '<div class="multiset-cell multiset-type-' . $type . $extraClass .'">' . $element->getLabel() . '</div>';
        }

        $params = array(
            'name' => $name,
            'form' => $options['form'],
            'item_id' => null,
            'dependences' => $dependences,
        );

        if (is_array($value) && count($value)) {
            foreach ($value as $params['item_id'] => $params['item']) {
                if($params['item_id'] === 'new'){ // какая-то проблема php при сравнении строки 'new' с нулем
                    $this->_addNewItems($set, $params);
                } else {
                    $this->_addItem($set, $params);
                }
            }
        }

        $params['item_id'] = null;

        $newItem = '';

        $this->_addItem($newItem, $params);

        $isSingle = (bool) (isset($options['isSingle']) ? $options['isSingle'] : false);

        $jsConfig = array(
            'el' => '#'.$this->id,
            'isSingle' => $isSingle,
        );

        if (isset($options['onRowAdd'])) {
            $jsConfig['listeners'] = array(
                'addRow' => $options['onRowAdd']
            );
        }

        $this->view->HM()->create('hm.core.ui.form.helper.MultiSet', $jsConfig);

        return '<div data-emptyTpl="'.$this->view->escape($newItem).'" id="' . $this->id. '" class="multiset"><div class="multiset-header">' . $header . '</div>'.$set.'</div>';
    }

    protected function _addItem(&$set, $params)
    {
        $set .= '<div class="multiset-row">';

        if (is_array($params['dependences'])) {
            foreach ($params['dependences'] as $patternElement) {

                if (empty($patternElement) || !is_subclass_of($patternElement, 'Zend_Form_Element')) continue;

                $element = clone $patternElement;
                $elementName = $element->getName();
                $element->clearDecorators()
                    ->addDecorator('ViewHelper')
                    ->setAttrib('class', $element->getAttrib('class') . ' multiset-element-' . $elementName);

                if ($params['item_id'] === 'new') {// какая-то проблема php при сравнении строки 'new' с нулем
                    $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, $params['item_id']))
                        ->setValue($params['item'][$elementName])
                        ->setIsArray(true);
                } else if ($params['item_id'] !== null) {
                    // populate
                    $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, $params['item_id']))
                        ->setValue($params['item'][$elementName])
                        ->setIsArray(false);
                } else {
                    // новый пустой элемент
                    $element->setName($elementName)
                        ->setValue('')
                        ->setIsArray(true);

                    // общепринятый хак чтоб checkbox возвращал также и пустые значения
                    if (is_a($element, 'Zend_Form_Element_Checkbox')) {
                        $hiddenElement = new Zend_Form_Element_Hidden(sprintf('%s__%s__new', $params['name'], $elementName), array('value' => 1));
                        $set .= $hiddenElement->setIsArray(true)->clearDecorators()->addDecorator('ViewHelper')->render();
                    } else {
                        $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, HM_Form_Element_MultiSet::ITEMS_NEW));
                    }
                }

                $type = strtolower(str_replace(array('Zend_Form_Element_', 'HM_Form_Element_'), '', $element->getType()));
                $set .= '<div class="multiset-cell multiset-type-' . $type . '">' . $element->render() . '</div>'; // использовать декоратор?
            }
        }
        $set .= '<div class="multiset-cell"><a href="#" class="multiset-row-delete" title="' . _('Удалить') . '"><img src="/images/blog/controls-delete.png" /></a></div>';
        $set .= '</div>';
    }

    protected function _addNewItems(&$set, $params)
    {
        $new_params = array();
        foreach($params as $key => $param){
            if(is_object($param)){
                $new_params[$key] = clone $param;
            } else {
                $new_params[$key] = $param;
            }
        }
        foreach ($params['item']['variant'] as $key => $variant) {
            if(!empty($variant)){
                $new_params['item']['is_correct'] = $params['item']['is_correct'][$key];
                $new_params['item']['variant'] = $variant;
                $this->_addItem($set, $new_params);
            }
        }
    }
    
}
