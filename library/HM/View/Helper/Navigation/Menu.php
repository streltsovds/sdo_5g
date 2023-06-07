<?php
/*
 * Пришлось переопределить, т.к. из коробки не работает -
 * - какие-то проблемы с $helper
 *
 */
class HM_View_Helper_Navigation_Menu extends Zend_View_Helper_Navigation_Menu
{
    public function renderPartial(Zend_Navigation_Container $container = null,
                                  $partial = null)
    {

        /** @var HM_User_UserService $userService */
        $userService = Zend_Registry::get('serviceContainer')->getService('User');


        if (null === $container) {
            $container = $this->getContainer();
        }

        if (null === $partial) {
            $partial = $this->getPartial();
        }

        if (empty($partial)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(
                'Unable to render menu: No partial view script provided'
            );
            $e->setView($this->view);
            throw $e;
        }

        $model = array(
            'helper' => $this,
            'container' => $container,
            'currentUser' => $userService->getCurrentUser()
        );

        if (is_array($partial)) {
            if (count($partial) != 2) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(
                    'Unable to render menu: A view partial supplied as '
                    .  'an array must contain two values: partial view '
                    .  'script and module where script can be found'
                );
                $e->setView($this->view);
                throw $e;
            }

            return $this->view->partial($partial[0], $partial[1], $model);
        }

        return $this->view->partial($partial, null, $model);
    }
}