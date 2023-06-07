<?php if (!Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(
	Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), 
	HM_Role_Abstract_RoleModel::ROLE_ENDUSER
)):?>

<?php if (!$this->isAjaxRequest):?>
<style>
    .grid-filters-from{
        margin-bottom: 10px;
    }
    .grid-filters-from dd,
    .grid-filters-from dt{
        display: inline;
    }
    .grid-filters-from dd{
        margin-right: 5px;
    }

</style>

<?php if ($this->showSelect): ?>
    <form class="grid-filters-from" method="post">
        <?php echo $this->selectMy; ?>
        <?php echo $this->submit; ?>
    </form>
<?php endif;?>

<?php endif;?>

<?php echo $this->grid;?>

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
