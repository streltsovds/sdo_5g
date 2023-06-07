<?php if (!Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
	Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), 
	HM_Role_Abstract_RoleModel::ROLE_ENDUSER
)):?>

<?php echo $this->grid;?>
<?php if (!$this->gridAjaxRequest):?>
    <?php echo $this->footnote();?>
<?php endif;?>

<?php if (!$this->isAjaxRequest): ?>
        <style>
            .hm-newcomer-dolg {
                color: #ffffff;
                background-color: #cc0000;
                font-weight: bold;
                font-size: 14px;
                padding: 1px 6px;
                border-radius: 3px;
            }
        </style>
<?php endif; ?>

<?php else:?>
<p>
	Данный функционал доступен в Кабинете руководителя. 
</p>
<?php endif;?>

<?php if ($this->switchRole):?>
<p>
	Если Вы являетесь руководителем подразделения, сейчас произойдёт автоматическое перенаправление в Кабинет руководителя.
</p>
<script>
	document.location.href = '/switch/role/<?php echo $this->switchRole;?>';
</script>
<?php endif;?>
