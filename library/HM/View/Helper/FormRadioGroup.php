<?php

class HM_View_Helper_FormRadioGroup extends Zend_View_Helper_FormRadio
{

    public function formRadioGroup($name, $value = null, array $attribs = array(), array $errors = array(),  array $options = array())
    {

        if (!empty($attribs['inputtype'])) $this->_inputtype = $attribs['inputtype'];
        $formthis = $attribs['form'];

        $dependences = isset($attribs['dependences']) ? $attribs['dependences'] : [];
        $descriptions = isset($attribs['Descriptions']) ? $attribs['Descriptions'] : [];

        unset($attribs['form']);
        unset($attribs['dependences']);
        unset($attribs['inputtype']);

        $info = $this->_getinfo($name, $value, $attribs, $options);
        extract($info); // name, value, attribs, options, listsep, disable

        // the radio button values and labels
        $options = (array) $options;

        // should the name affect an array collection?
        $name = $this->view->escape($name);

        // ensure value is an array to allow matching multiple times
        $value = (array) $value;

        // build the element
        $list  = array();
        // add radio buttons to the list.
        $remove = array();
        $dependencesTemplate = [];

        $radioGroupValue = null;

        foreach ($options as $opt_value => $opt_label) {

            $option = [];

            $option['value'] = $opt_value;
            $option['description'] = isset($descriptions[$opt_value]) ? $descriptions[$opt_value] : null;

            $option['label'] = $opt_label;

            // should the label be escaped?
            if ($escape = false) {
                $option['label'] = $this->view->escape($option['label']);
            }

            // is it checked?
            if (in_array($opt_value, $value)) {
                $radioGroupValue = $opt_value;
            }

            // add to the array of radio buttons
            $sub ='';
            if (isset($dependences[$opt_value])) {
                if(is_array($dependences[$opt_value])){
                    foreach($dependences[$opt_value] as $value1){

                        if($formthis->getElement($value1) == false)
                            continue;

                        $formthis->getElement($value1)->removeDecorator('fieldset');
                        $formthis->getElement($value1)->removeDecorator('htmltag');
                        $sub .= $formthis->getElement($value1)->render();
                        $remove[] = $value1;
                    }
                }
            }
            // для избежания конфликтов с кавычками у зависимостей заменим их на Ⓠ
//            $sub = str_replace("'", "Ⓠ", $sub);
            $dependencesTemplate[$opt_value] = $sub;
            $list[] = $option;
        }

        foreach($remove as $val){
            $formthis->removeElement($val);
        }

        if ($radioGroupValue === null && count($list) > 0 && $list[0]['value']) $radioGroupValue = $list[0]['value'];

        $dependencesTemplate = HM_Json::encodeErrorSkip($dependencesTemplate, HM_Json::JSON_ENCODE_OPTS_DEFAULT | JSON_FORCE_OBJECT);
        $options = ZendX_JQuery::encodeJson($list);
        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);
        $radioGroupValue = ZendX_JQuery::encodeJson($radioGroupValue);

        return <<<HTML
            <hm-radio-group 
              name='$name' 
              :value='$radioGroupValue' 
              :options='$options' 
              :dependences='$dependencesTemplate'
              :attribs='$attribs'
              :errors='$errors'
            >
            </hm-radio-group>
HTML;
    }
}
