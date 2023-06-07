<?php
$infoblocksHelper = $this->getHelper("InfoBlocks");
$authBlockHtml = $infoblocksHelper->view->Authorization();

$options = $this->getService('Option')->getDesignSettingAuthForm();
?>



<hm-login-form :data='<?=$options?>'> <?php echo $authBlockHtml ?> </hm-login-form>