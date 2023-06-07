<?php

class HM_View_Helper_VueSingleChoice extends Zend_View_Helper_FormElement
{
    protected $id;

    /**
     * @param $name
     * @param array $value
     * @param array $attribs
     * @param array $errors
     * @return string
     */
    public function vueSingleChoice($name, $value = array(), $attribs = array(),  array $errors = array())
    {
        $this->id = $this->view->id('sc');

        $params = array(
            'name' => $name,
            'form' => isset($attribs['form']) ? $attribs['form'] : null,
            'item_id' => null,
        );

        try {
            // На всякий случай оберну
            $params['type'] = $this->view->quest->type;
        } catch (Exception $e) {}

        $params['item_id'] = null;

        $params  = ZendX_JQuery::encodeJson($params);
        $attribs = ZendX_JQuery::encodeJson($attribs);
        $value   = ZendX_JQuery::encodeJson($value);
        $errors  = ZendX_JQuery::encodeJson($errors);
        $value   = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-single-choice
    name='$this->id'
    :params='$params'
    :attribs='$attribs'
    :value='$value'
    :errors='$errors'
>
</hm-single-choice>
HTML;
    }
}