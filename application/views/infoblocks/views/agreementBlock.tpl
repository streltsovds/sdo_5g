<div id="agreement">
<?php if ($this->order): ?>

    <?php if ($this->user->getPhoto()):?>
    <div class="card_photo">
    <img src="<?php echo $this->baseUrl($this->user->getPhoto());?>" alt="<?php echo $this->escape($this->user->getName())?>" align="left"/>
    </div>
    <?php else:?>
    <div class="card_photo">
    <img src="<?php echo $this->baseUrl('images/people/nophoto.gif');?>" alt="<?php echo $this->escape($this->user->getName())?>" align="left"/>
    </div>
    <?php endif;?>
    <?php echo $this->card($this->user, $this->user->getCardFields()); ?>
    <div style="clear:both;"></div>
     
    <div class="card_subject">
        <h6 style="width: 55%"><?php echo $this->order['subject_name']?></h6>
        <p style="width: 10%"><?php echo $this->order['subject_price'];?></p>
        <p style="width: 30%"><?php echo sprintf(_('с %s по %s'), $this->order['subject_begin'], $this->order['subject_end']);?></p>
        <div style="clear:both;"></div>
    </div>
    
    <input type="button" value="<?php echo _('Принять');?>" id="accept">  
    <input type="button" value="<?php echo _('Отклонить');?>" id="reject">  
<?php else:?>
<div><p><?php echo _('Отсутствуют данные для отображения'); ?></p></div>
<?php endif;?>
<?php if ($this->isDean):?>
<div class="bottom-links">
<hr/>
<a href="<?php echo $this->url(array('module' => 'order', 'action' => 'index', 'controller' => 'list'));?>"><?php echo _('Все заявки'); ?></a>
</div>    
<?php endif;?>
</div>
<?php $this->inlineScript()->captureStart(); ?>
jQuery(document).ready(function($){
    $('#agreement #accept').click(function(){
        document.location.href = '<?php echo $this->url(array('module' => 'order', 'controller' => 'index', 'action' => 'skip-event', 'claimant_id' => $this->order['claimant_id'], 'programm_event_id' => $this->order['programm_event_id']))?>';
    });
    $('#agreement #reject').click(function(){
        document.location.href = '<?php echo $this->url(array('module' => 'order', 'controller' => 'index', 'action' => 'fail', 'claimant_id' => $this->order['claimant_id'], 'programm_event_id' => $this->order['programm_event_id']))?>';
    });
});
<?php $this->inlineScript()->captureEnd(); ?>
