<?php

class HM_View_Infoblock_FaqBlock extends HM_View_Infoblock_Abstract
{
    const ITEMS_COUNT = 5;

    protected $id = 'faq';

    //Определяем класс отличный от других
    protected $class = 'scrollable';

    public function faqBlock($param = null)
    {
        $serviceContainer = Zend_Registry::get('serviceContainer');

        $order = 'RAND()';

        if ($serviceContainer->getService('Faq')->getSelect()->getAdapter() instanceof Zend_Db_Adapter_Oracle) {
            $order = 'dbms_random.value';
        }
        $currentRole = $this->getService('User')->getCurrentUserRole();
        if ($this->getService('Acl')->inheritsRole($currentRole, HM_Role_Abstract_RoleModel::ROLE_ENDUSER)) $currentRole = HM_Role_Abstract_RoleModel::ROLE_ENDUSER;
        if (
            $serviceContainer->getService('Acl')->inheritsRole($serviceContainer->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)
            //in_array($serviceContainer->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ADMIN))
        ) {
            $faqs = $serviceContainer->getService('Faq')->fetchAll(
                'published = 1',
                $order,
                self::ITEMS_COUNT
            );
        } else {
            $faqs = $serviceContainer->getService('Faq')->fetchAll(
                $serviceContainer->getService('Faq')->quoteInto('roles LIKE ?', '%'.$currentRole.'%').' AND published = \'1\'',
                $order,
                self::ITEMS_COUNT
            );
        }

        foreach ($faqs as $faq) {
            $faq->answer = strip_tags($faq->answer);
            $faq->question = strip_tags($faq->question);
        }

        $this->view->faqs = HM_Json::encodeErrorSkip($faqs->asArrayOfArrays());

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/faq/style.css');

        $content = $this->view->render('faqBlock.tpl');
        return $this->render($content);

    }
}