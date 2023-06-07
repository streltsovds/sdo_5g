<?php

// TODO убрать код проверки роли пользователя, если $view->actions() и так её осуществляют
trait HM_Grid_Trait_VueGetMarkup
{
    /** действия с иконками слева-сверху */
    public $headerActionsHtml = '';

    public $headerActionsBeforeHtml = '';

    // TODO возможно, архитектурнее правильнее задавать в шаблоне
    //   `$grid->headerActionsHtml = $this->actions();`
    //   а потом вызывать `echo $grid->getMarkup()`, но на данный момент 165 раз раскопирована строка
    //   `if ($this->isAjaxRequest()) echo($grid->deploy()); else $this->view->grid = $grid->getMarkup()`
    //   в контроллерах, поэтому проще было задание действий в заголовке грида по-умолчанию сделать автоматическим
//    public $autoGetHeaderActions = true;

    // строка вида "mca:user:list:new"
//    public $autoGetHeaderActionsResourceString = null;

    /**
     * @param string $url
     * @param bool $withInitialData
     * @return string
     * @throws Zend_Exception
     */
    public function getMarkup($url = null, $withInitialData = true)
    {
        /**
         * получем теперь из данных ajax:
         * @see Bvb_Grid_Deploy_Vue::$autoGetHeaderActionsOnDeploy
         */

        /** @see HM_Grid_Trait_AutoloadActions */
//        $this->autorunGetHeaderActions();

        $loadUrl = isset($this->loadUrl) ? $this->loadUrl : null;

        $gridVueAttrs = array_filter([
            'debug' => APPLICATION_ENV === 'development',
            'loadUrl'=> $loadUrl
        ]);

        if (method_exists($this, 'getGridId')) {
            $gridVueAttrs['id'] = $this->getGridId();
        }

        if ($url) {
            $gridVueAttrs['load-url'] = $url;
        }

        $gridVueAttrsJson = HM_Json::encodeErrorThrow($gridVueAttrs);

        $gridOpen = "<hm-grid v-bind='${gridVueAttrsJson}'";

        if ($withInitialData) {
            $initialDataJson = $this->getInitialData();
            if ($initialDataJson) {
                $gridOpen .= " :initial-data='${initialDataJson}'";
            }
        }

        $gridOpen .= '>';

        $gridSlots = '';

        if ($this->headerActionsBeforeHtml) {
            $gridSlots .=
                '<template v-slot:header-actions-before>' .
                $this->headerActionsBeforeHtml .
                '</template>';
        }

        $gridClose = '</hm-grid>';
        return $gridOpen . $gridSlots . $gridClose;
    }

    public function getInitialData() {
        if (method_exists($this, 'buildGrid')) {
            return $this->buildGrid();
        }

        return $this->deploy(true);
    }

    /**
     * разместить в getMarkup целевого класса
     * @throws Zend_Exception
     */
//    public function autorunGetHeaderActions() {
//        if ($this->autoGetHeaderActions) {
//            $view = Zend_Registry::get('view');
//
//            $this->setHeaderActionsFromView(
//                $view,
//                $this->autoGetHeaderActionsResourceString
//            );
//        }
//    }

    /**
     * @param Hm_View $view
     * @param  string $checkResourceAccessString - строка вида "mca:user:list:new"
     * @throws Zend_Exception
     */
//    public function setHeaderActionsFromView($view, $checkResourceAccessString = null) {
//
//        if ($checkResourceAccessString &&
//            !(Zend_Registry::get('serviceContainer')
//                ->getService('Acl')
//                ->isCurrentAllowed($checkResourceAccessString)
//            )
//        ) {
//            return;
//        }
//
//        $this->headerActionsHtml = $view->actions();
//    }


}