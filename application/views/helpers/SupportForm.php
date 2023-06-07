<?php
class HM_View_Helper_SupportForm extends HM_View_Helper_Abstract
{
    public function renderSupportForm() {
        return $this->view->render('support-form.tpl');
    }
}