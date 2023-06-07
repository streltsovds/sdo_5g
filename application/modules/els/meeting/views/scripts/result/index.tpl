<?php if ($this->content):?>
	<?php echo $this->content?>
<?php endif;?>
<?php if($this->grid):?>
    <?php if (!$this->gridAjaxRequest):?>
        <?php if ($this->allowBack):?>
            <?php echo $this->formButton('cancel', _('Назад'), array('onClick' => 'window.location.href = "'.$this->serverUrl($this->url(array('module' => 'meeting', 'controller' => 'result', 'action' => 'index', 'project_id' => $this->projectId, 'meeting_id' => $this->meetingId), null, true)).'"'))?>
        <?php endif; ?>
    	<?php echo $this->headSwitcher(array('module' => 'meeting', 'controller' => 'result', 'action' => 'index', 'switcher' => 'index'), 'result', $this->disabledMods);?>
    <?php endif; ?>
    <?php echo $this->grid?>
<?php endif;?>