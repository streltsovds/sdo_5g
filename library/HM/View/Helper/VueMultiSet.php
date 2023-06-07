<?php

class HM_View_Helper_VueMultiSet extends Zend_View_Helper_FormElement
{
    protected $id;

    /**
     * @param $name
     * @param array $values - массив значений
     * @return string
     */
    public function vueMultiSet($name, $value = [], $attribs = [], array $errors = [])
    {
        $dependencesTemplate = [];
        $this->id = $this->view->id('ms');

        $dependences = $attribs['dependences'];
        $dependencesClasses = $attribs['dependences-classes'];

        $params = [
            'name' => $name,
            'form' => isset($attribs['form']) ? $attribs['form'] : null,
            'item_id' => null,
            'dependences' => $dependences,
        ];

        if (is_array($value) && count($value)) {
            foreach ($value as $params['item_id'] => $params['item']) {

                // какая-то проблема php при сравнении строки 'new' с нулем
                if($params['item_id'] === 'new'){
                    $newCounter = count($params['item']['variant']);
                    $addCounter = 0;

                    while ($addCounter < $newCounter) {

                        $newParams = $params;

                        $newParams['item'] = [
                            'variant' => $params['item']['variant'][$addCounter],
                            'is_correct' => $params['item']['is_correct'][$addCounter]
                        ];

                        $dependenceTemplate = $this->_addItem($newParams);
                        $dependencesTemplate[] = $dependenceTemplate;

                        $addCounter++;
                    }
                } else {
                    $dependenceTemplate = $this->_addItem($params);
                    $dependencesTemplate[] = $dependenceTemplate;
                }
            }
        }

        $params['item_id'] = null;

        $dependenceEmptyTemplate = $this->_addItem($params);
        ksort($dependencesTemplate);

        $dependencesTemplate = $dependencesTemplate ? ZendX_JQuery::encodeJson($dependencesTemplate) : "[]";
        $dependenceEmptyTemplate = ZendX_JQuery::encodeJson($dependenceEmptyTemplate);

        // Если не все классы зависимостей установлены, добьём пустыми записями
        $dependencesClasses = array_pad($dependencesClasses, count($dependences), '');
        $dependencesClasses = ZendX_JQuery::encodeJson($dependencesClasses);

        $attribs = ZendX_JQuery::encodeJson($attribs);
        $errors = ZendX_JQuery::encodeJson($errors);

        // value не используется, а устанавливается в соответствующие $dependencesTemplate
        $value = ZendX_JQuery::encodeJson($value);

        return <<<HTML
<hm-multi-set
    name='$this->id'
    :attribs='$attribs'
    :dependences='$dependencesTemplate'
    :dependences-classes='$dependencesClasses'
    :empty-dependence='$dependenceEmptyTemplate'
    :errors='$errors'
>
</hm-multi-set>
HTML;
    }

    protected function _addItem($params)
    {
        $response = [];
        if (is_array($params['dependences'])) {
            foreach ($params['dependences'] as $patternElement) {

                if (empty($patternElement) || !is_subclass_of($patternElement, 'Zend_Form_Element')) continue;

                $element = clone $patternElement;
                $elementName = $element->getName();
                $element->setAttrib('class', $element->getAttrib('class') . ' multiset-element-' . $elementName);
                $element
                    ->addPrefixPath('HM_Form_Decorator', 'HM/Form/Decorator', 'decorator')
                    ->clearDecorators()
                    ->addDecorator('VueViewHelper');

                $element->setAttrib('formName', $element->getAttrib('formName'));

                if ($params['item_id'] === 'new') {// какая-то проблема php при сравнении строки 'new' с нулем
                    $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, $params['item_id']))
                        ->setValue(htmlspecialchars($params['item'][$elementName]))
                        ->setIsArray(true);
                } else if ($params['item_id'] !== null) {
                    // populate
                    $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, $params['item_id']))
                        ->setValue(htmlspecialchars($params['item'][$elementName]))
                        ->setIsArray(false);
                } else {
                    // новый пустой элемент
                    $element->setName($elementName)
                        ->setValue('')
                        ->setIsArray(true);

                    // общепринятый хак чтоб checkbox возвращал также и пустые значения
//                    if (is_a($element, 'HM_Form_Element_VueCheckbox')) {
//                        $hiddenElement = new Zend_Form_Element_Hidden(sprintf('%s__%s__new', $params['name'], $elementName), array('value' => 1));
//                        $response[] = $hiddenElement->setIsArray(true)
////                            ->clearDecorators()
////                            ->addDecorator('VueViewHelper')
//                            ->render();
//                    } else {
//                        $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, HM_Form_Element_MultiSet::ITEMS_NEW));
//                    }
                    $element->setName(sprintf('%s__%s__%s', $params['name'], $elementName, HM_Form_Element_MultiSet::ITEMS_NEW));

                }

                $response[] =  str_replace("'", "&apos;", $element->render());
            }
        }

        return $response;
    }
}