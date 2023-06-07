<?php

class Demo_VueController extends HM_Controller_Action {

    public function init()
    {
        parent::init();

//        $isAdmin = $this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ADMIN);

//        if (!$isAdmin) {
//            throw new Zend_Controller_Action_Exception('Access Denied', 403);
//        }
    }

    public function betterScrollAction() {
    }

    public function dataAction() {
        $this->view->testCaption = 'My caption2';

        $this->view->tableHeaders = [
          ['text' => 'Колонка A', 'value' => 'a',],
          ['text' => 'Колонка \'B\'', 'value' => 'b',],
        ];

        $this->view->tableData = [
           ['a' => 15, 'b' => 10],
           ['a' => 5, 'b' => 6],
        ];
    }

    public function fontsAction() {
        /** @see https://www.npmjs.com/package/satisfy.js */
        $this->view->demoTypographyCssSelectors = [
            '.display-4',
            '.display-3',
            '.display-2',
            '.display-1',
            '.headline-3', // .hm-user-content h1
            '.headline-2', // .hm-user-content h2
            '.headline',
            '.title',
            '.subheading-2',
            '.subheading',
            '.body-2',
            '.body-1',  // .hm-user-content p
            '.caption',
            '.button',
            '.link',
            '.overline',
            '._info',
            '.hm-user-content h1',
            '.hm-user-content h2',
            '.hm-user-content h3',
            '.hm-user-content h4',
            '.hm-user-content h5',
            '.hm-user-content h6',
            '.hm-user-content p',
            '.hm-user-content a',
            '.hm-user-content em',
            '.hm-user-content q',
            '.hm-user-content ul li:2',
        ];
    }

    public function formsAction() {

    }

    public function iconsAction() {

    }

    public function imageMultiSelectAction() {

    }

    public function modalAction() {

    }

    public function pdfAction() {

    }


    public function translateAction() {

    }

    public function tsAction() {

    }
}
