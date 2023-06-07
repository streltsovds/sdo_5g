<?php

class HM_Form_Element_Vue_SearchMaterial extends HM_Form_Element_Vue_Element {

    public function render(Zend_View_Interface $view = null)
    {
        $name = $this->getName();
        $attribs = $this->getAttribs();

        $classifiers = array_key_exists('classifiers', $attribs) ? json_encode($attribs['classifiers'], JSON_FORCE_OBJECT) : "{}";
        $type = array_key_exists('type', $attribs) ? json_encode($attribs['type'], JSON_FORCE_OBJECT) : "{}";
        $searchField = array_key_exists('search_field', $attribs) ? json_encode($attribs['search_field'], JSON_FORCE_OBJECT) : "{}";
        $url = json_encode($this->getAttrib('url'));

        $classifiersAndTypes = [
            'classifiers' => [],
            'types' => isset($attribs['type']['multiOptions']) ? $attribs['type']['multiOptions'] : [],
        ];

        if(isset($attribs['classifiers']['MultiOptions'])) {
            foreach ($attribs['classifiers']['MultiOptions'] as $classifierGroup) {
                if(count($classifierGroup['items'])) {
                    $classifiersAndTypes['classifiers'][] = $classifierGroup;
                }
            }
        }

        $classifiersAndTypesJson = json_encode($classifiersAndTypes, JSON_FORCE_OBJECT);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (($request->getControllerName() == 'extra') || $request->getActionName() == 'change-material') {
            $type = 'null';
        }

        return <<<HTML
<hm-material-search
    name='$name'
    :classifiers-and-type='$classifiersAndTypesJson'
    :classifiers='$classifiers'
    :type='$type'
    :search-field='$searchField'
    :url='$url'
>
</hm-material-search>
HTML;

    }
}