<div class="ui-dialog pcard pcard_inline">
	<div class="ui-dialog-content-wrapper">
	<div class="lightdialog ui-dialog-content ui-widget-content" id="ui-lightdialog-2">
    <div class="card_photo">
    	<img src="<?php echo $this->serverUrl('/images/people/nophoto.gif');?>" align="left"/>
    </div>
	
<?php
echo $this->card(
    $this->candidate,
        array(
        	'fio'  => _('Кандидат'),
            //'getParentPositionName()' => _('Подразделение'),
    ),
    array(
        'title' => _('Карточка кандидата'),
    )
); ?>

		</div>
	</div>
</div>