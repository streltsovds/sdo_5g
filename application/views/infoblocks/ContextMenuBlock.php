<?php

class HM_View_Infoblock_ContextMenuBlock extends HM_View_Infoblock_Abstract
{
    protected $id = 'contextMenu';

    public function contextMenuBlock($param = null)
    {
        if (empty($options['partition'])) {
            return false;
        }

        // $options['view'] ��������� ����� �������� ���������������� HM_Navigation

        $locale = Zend_Registry::get('Zend_Locale');
        $localePath = ($locale != 'ru_RU') ? '/../data/locales/' . $locale : '';
        $config = new HM_Config_Xml(APPLICATION_PATH . /*$localePath .*/ '/settings/context.xml', $options['partition']);

        if($config == null){
            return false;
        }
        $navigation = new HM_Navigation($config, $options['substitutions'], isset($options['activityService']) ? $options['activityService'] : null);

        $this->view->getSubNavigation($navigation, $options['partition'], $options['substitutions']);
        $this->view->menu = $navigation;
        
        $content = $this->view->render('contextMenuBlock.tpl');

        if($title == null) {
            return $this->render($content);
        }
        return $this->render($content);

    }
}