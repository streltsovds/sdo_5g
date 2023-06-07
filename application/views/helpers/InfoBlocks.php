<?php
class HM_View_Helper_InfoBlocks extends HM_View_Helper_Abstract
{
    protected function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function infoBlocks($adminMode = false, $userRole = false, $roles = array(), $isAjax = false)
    {
        /** @var HM_Infoblock_InfoblockService $infoBlockService */
        $infoBlockService = $this->getService('Infoblock');
        /** @var HM_User_UserService $userService */
        $userService = $this->getService('User');


        // не поддерживается в 5g
        //$allowEditByUsers = (bool) Zend_Registry::get('config')->infoblocks->allowEditByUsers;

        if (!$adminMode) {
            $userId = $userService->getCurrentUserId();
            $userRole = $userService->getCurrentUserRole(true);
        } else {
            $userId = 0;
        }

        /*if (in_array($userRole, array(HM_Role_Abstract_RoleModel::ROLE_ENDUSER, HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR))) {
            $allowEditByUsers = false;
        }*/

        $blocks = $infoBlockService->getTree($userRole, false, $userId);
        $forcedBlocks = $userId ? $infoBlockService->getForcedInfoblocks($userRole) : array();


        usort($blocks['current'], function($block1, $block2) {
            if ($block1['y'] == $block2['y']) {
                return $block1['x'] < $block2['x'] ? -1 : 1;
            } else {
                return $block1['y'] < $block2['y'] ? -1 : 1;
            }
        });

        array_walk($blocks['current'], function(&$block){
            $explode = explode('_', $block['name']);
            if (count($explode) == 2) list($block['name'], $block['param']) = $explode;
        });

        // если нечетное количество forced-блоков -
        // принудительно делаем последний блок wide,
        // чтоб не сбивался порядок обычных виджетов ниже
        if (count($forcedBlocks) % 2) {
            $block = array_pop($forcedBlocks);
            $block['layout'] = 'wide';
            array_push($forcedBlocks, $block);
        }

        $blocks = array_merge($forcedBlocks, $blocks['current']);

        foreach ($blocks as $key => $block) {
            $innerHtml = isset($block['param']) ?
                $this->view->{$block['name']}($block['param']) :
                $this->view->{$block['name']}();

            if($innerHtml) {
                $blocks[$key]['innerHtml'] = $innerHtml;
            } else {
                unset($blocks[$key]);
            }
        }

        $blocks = array_values($blocks);

//        if (!$adminMode) {
//            $blocks = array_filter($blocks, function ($block) {
//                // @todo: рефакторить блоки на frontend, возвращать пустую строку если пусто
//                return strpos(trim($block['innerHtml']), '<v-alert') !== 0;
//            });
//        }

        $this->view->infoBlocks = $blocks;
        $this->view->role = $userRole;

        if ($isAjax) {
            return $blocks;
        } else {
            // починка json_encode() ошибки
//            foreach ($blocks as &$block) {
//                if ($block['innerHtml']) {
//                    $block['innerHtml'] = mb_convert_encoding($block['innerHtml'], 'UTF-8', 'UTF-8');
//                }
//            }

            $this->view->infoBlocksJson = HM_Json::encodeErrorThrow($blocks);
            return $this->view->render('infoBlocks.tpl');
        }
    }

}
