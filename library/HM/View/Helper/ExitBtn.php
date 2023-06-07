<?php
class HM_View_Helper_ExitBtn extends HM_View_Helper_Abstract
{
    public function exitBtn()
    {
        $default = new Zend_Session_Namespace('default');

        $serviceContainer = Zend_Registry::get('serviceContainer');
        $currentRole = $serviceContainer->getService('User')->getCurrentUserRole();
        $isGuest = $serviceContainer->getService('Acl')->inheritsRole($currentRole, HM_Role_Abstract_RoleModel::ROLE_GUEST);
        $infoblocksHelper= Zend_Registry::get('view')->getHelper('InfoBlocks');
        $authBlockHtml = $infoblocksHelper->view->Authorization();

        $hmLoginFormData = $this->getService('Option')->getDesignSettingAuthForm();

        if ($isGuest) { 
            return "<hm-login-button :background-color='colors.header'>
                        <hm-login-form :data='".$hmLoginFormData."' is-modal>"
                        . $authBlockHtml .
                        '</hm-login-form>
                    </hm-login-button>';
        }

        if (isset($default->userRestore)) {
            $restoreUser = $default->userRestore;
        }

        if (!isset($restoreUser) || !$restoreUser) {
            return '<hm-logout :background-color="colors.header"></hm-logout>';
        } else {
            return '
                <v-tooltip bottom>
                <template v-slot:activator="{on: exit}">
                    <v-btn icon color="" v-on="exit" href="/restore">
                        <svg-icon :stroke-width="1" name="exit" color="#e57373"/> 
                    </v-btn>
                </template>
                    <span>'._('Выйти из режима').'</span>
                </v-tooltip>';
        }
    }
}