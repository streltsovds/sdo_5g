<?php //@todo: headswitcher'ы 2 двух разных страниц указывают на одну страницу materials; переход обратно всегда в resources ?>
<?php if (!$this->isGridAjaxRequest) echo $this->headSwitcher(array('module' => 'resource', 'controller' => 'list', 'action' => 'index', 'switcher' => 'index'), 'materialresource');?>
<?php if (!$this->isGridAjaxRequest):?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:course:import:project')):?>
        <?php echo $this->actions('project-courses', array(
			array(
	            'title' => _('Создать учебный модуль'),
	            'url' => $this->url(array('module' => 'course', 'controller' => 'list', 'action' => 'new', 'project_id' => $this->projectId))
	        ),
            array(
                'title' => _('Импортировать учебный модуль'),
                'url' => $this->url(array('module' => 'course', 'controller' => 'import', 'action' => 'project', 'project_id' => $this->projectId))
            )
        ))?>
    <?php endif;?>
<?php endif;?>
<?php echo $this->grid?>