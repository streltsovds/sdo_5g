<?php echo $this->headSwitcher(array('module' => 'profile', 'controller' => 'index', 'action' => 'card', 'switcher' => 'index'), 'profileCard');?>
<?php $cardId = $this->id('card-inline'); ?>
<div class="ui-dialog pcard pcard_inline"  id="<?php echo $this->escape($cardId) ?>">
	<div class="ui-dialog-content-wrapper">
		<div class="lightdialog ui-dialog-content ui-widget-content" id="ui-lightdialog-2">
            <div class="card_photo">
            	<img src="<?php echo $this->serverUrl($this->profile->getIcon());?>" alt="<?php echo $this->escape($this->profile->name)?>" align="left"/>
            </div>
<?php echo $this->card(
    $this->profile,
    array(
        'getCategory()'  => _('Категория должности'),
        'description' => _('Краткое описание'),            
    ),
    array(
        'title' => _('Карточка профиля должности')
    ));
?>
		</div>
	</div>
</div>