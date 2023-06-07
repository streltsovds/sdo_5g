<?php
class Section_AjaxController extends HM_Controller_Action
{
    public function init()
    {
        $this->_helper->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
    }

    public function changeTitleAction()
    {
        $user = $this->getService('User')->getCurrentUser();

        if (!$user) {
            return $this->responseJson(array('error' => _('Вы не авторизованы')));
        }

        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() && $request->isPost()) {
            $text = $this->_getParam('text', '');
            $sectionId = $this->_getParam('section_id', 0);

            $section = $this->getOne($this->getService('Section')->find($sectionId));

            if (!$section) {
                return $this->responseJson(array('error' => _('Данного раздела не существует')));
            }

            $this->getService('Section')->updateField($sectionId, $text, 'name');

            return $this->responseJson(array('text' => $text));
        } else {
            return $this->responseJson(array('error' => _('Запрос неверный')));
        }
    }
}
