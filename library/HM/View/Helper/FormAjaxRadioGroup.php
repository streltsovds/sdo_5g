<?php

class HM_View_Helper_FormAjaxRadioGroup extends Zend_View_Helper_FormRadio
{

    public function formAjaxRadioGroup($name, $value = null, $attribs = null,
        $options = null, $listsep = "<br />\n")
    {
        $formThis = $attribs['form'];
        
        $dependences = $attribs['dependences'];
        unset($attribs['form']);       
        unset($attribs['dependences']);
        
        $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
        extract($info); // name, value, attribs, options, listsep, disable
       
        // retrieve attributes for labels (prefixed with 'label_' or 'label')
        $label_attribs = array();
        foreach ($attribs as $key => $val) {
            $tmp    = false;
            $keyLen = strlen($key);
            if ((6 < $keyLen) && (substr($key, 0, 6) == 'label_')) {
                $tmp = substr($key, 6);
            } elseif ((5 < $keyLen) && (substr($key, 0, 5) == 'label')) {
                $tmp = substr($key, 5);
            }

            if ($tmp) {
                // make sure first char is lowercase
                $tmp[0] = strtolower($tmp[0]);
                $label_attribs[$tmp] = $val;
                unset($attribs[$key]);
            }
        }

        $labelPlacement = 'append';
        foreach ($label_attribs as $key => $val) {
            switch (strtolower($key)) {
                case 'placement':
                    unset($label_attribs[$key]);
                    $val = strtolower($val);
                    if (in_array($val, array('prepend', 'append'))) {
                        $labelPlacement = $val;
                    }
                    break;
            }
        }

        // the radio button values and labels
        $options = (array) $options;

        // build the element
        $xhtml = '';
        $list  = array();

        // should the name affect an array collection?
        $name = $this->view->escape($name);
        if ($this->_isArray && ('[]' != substr($name, -2))) {
            $name .= '[]';
        }

        // ensure value is an array to allow matching multiple times
        $value = (array) $value;

        // XHTML or HTML end tag?
        $endTag = ' />';
        if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
            $endTag= '>';
        }

        // add radio buttons to the list.
        require_once 'Zend/Filter/Alnum.php';
        $filter = new Zend_Filter_Alnum();
        $jquery = '';

        foreach ($options as $opt_value => $opt_label) {

            // Should the label be escaped?
            if ($escape) {
                $opt_label = $this->view->escape($opt_label);
            }

            // is it disabled?
            $disabled = '';
            if (true === $disable) {
                $disabled = ' disabled="disabled"';
            } elseif (is_array($disable) && in_array($opt_value, $disable)) {
                $disabled = ' disabled="disabled"';
            }

            // is it checked?
            $checked = '';
            //pr($value);
            if (in_array($opt_value, $value)) {
                $checked = ' checked="checked"';
            }

            // generate ID
            $optId = $id . '-' . $filter->filter($opt_value);

            $onClick = '';
            if (isset($dependences[$opt_value])) {
                $dependencesUrl = $dependences[$opt_value];
                
                $trueMethod = true;
                $useArrayOptions = true;
                if (!is_array($dependencesUrl)) {
                    if (strpos($dependencesUrl, "'") !== false) {
                        $trueMethod = false;
                    } else {
                        $useArrayOptions = false;
                    }
                }
                
                /* TODO: ПРАВИЛЬНО определять dependences так:
                 * 
                 * dependences => array(
                 *     'value1' => array(
                 *         'base'    => 'постоянная часть url',
                 *         'dinamic' => 'js-код для динамического добавления в конец url параметров'
                 *     )
                 * )
                 * 
                 * или так:
                 * 
                 * dependences => array(
                 *     'value1' => 'url'
                 * )
                 * 
                 * Комментарий: в старом способе используется очень плохой костыль,
                 * смысл которого заключался в том, что в конец url надо было добавлять 
                 * ' 
                 * или что-то вроде этого: 
                 * ' + $(selector).val() 
                 * 
                 */
                if ($trueMethod) {
                    $dependencesUrlDinamicPart = "''";
                    
                    if (!$useArrayOptions) {
                        $dependencesUrlBasePart = $dependencesUrl;
                    } else {
                        $dependencesUrlBasePart    = $dependencesUrl['base'];
                        
                        if (!empty($dependencesUrl['dinamic'])) {
                            $dependencesUrlDinamicPart = $dependencesUrl['dinamic'];
                        }
                    }
                    
                    $onClickJS =  '$(\'#'.$name.'-'.$opt_value.'-Group\').load(\''.$dependencesUrlBasePart.'\' + '.$dependencesUrlDinamicPart.');';
                } else {
                    // для поддержки старого неправильного кода
                    $onClickJS =  '$(\'#'.$name.'-'.$opt_value.'-Group\').load(\''.$dependencesUrl.');';
                }
                
                $onClick = ' onClick = "'.$onClickJS.'"';
                
                if (in_array($opt_value, $value)) {
                    $jquery .=  $onClick;
                }
            }

            // Wrap the radios in labels
            $radio = '<label'
                    . $this->_htmlAttribs($label_attribs) . ' for="' . $optId . '">'
                    . (('prepend' == $labelPlacement) ? $opt_label : '')
                    . '<input type="' . $this->_inputType . '"'
                    . ' name="' . $name . '"'
                    . ' id="' . $optId . '"'
                    . ' value="' . $this->view->escape($opt_value) . '"'
                    . $checked
                    . $onClick
                    . $disabled
                    . $this->_htmlAttribs($attribs)
                    . $endTag
                    . (('append' == $labelPlacement) ? $opt_label : '')
                    . '</label>';

            // add to the array of radio buttons
            if (isset($dependences[$opt_value])) {
                $radioSub= '<dl class = "' . $name . '-' . $opt_value.'-Group" id = "' . $name . '-' . $opt_value.'-Group" style="padding-left: 20px;"></dl>' ;
                $radio .= $radioSub;
            }
            $list[] = $radio;
        }        
            
        $this->view->jQuery()->addOnLoad($jquery);

        $xhtml .= implode($listsep, $list);

        
        return $xhtml;
    }
}
