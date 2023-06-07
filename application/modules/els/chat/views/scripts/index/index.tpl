<?php
if (!$this->gridAjaxRequest) {
    if (!$this->channel->lesson_id && !$this->isCallFromLesson) {
        if (!$this->channel->lesson_id) {
            echo $this->actions();
        } elseif($this->canCreate) {
            echo $this->actions();
        }
    }
    echo $this->headScript();
}
?>

<div class="chat">
    <div class="chat-channel">
        <div class="channel-header">
            <div class="channel-title"><?php echo $this->channel->name?></div>
            <div class="channel-controls">
                <?php if($this->canEdit):?>
                <a class="edit" href="<?php echo $this->url(array(
                    'module' => 'chat',
                    'controller' => 'index',
                    'action' => 'edit',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'channel_id' => $this->channel->id
                ))?>"></a>
                <?php endif;?>
                <?php if($this->canDelete):?>
                <a class="delete" href="<?php echo $this->url(array(
                    'module' => 'chat',
                    'controller' => 'index',
                    'action' => 'delete',
                    'subject' => $this->subjectName, 
                    'subject_id' => $this->subjectId,
                    'channel_id' => $this->channel->id
                ))?>"></a>
                <?php endif;?>
            </div>
        </div>
        <div class="chat-controls">
            <?php echo $this->chat('HMChat', array(
                'channel' => $this->channel
            ))?>
        </div>
        <div class="chat-body">
            <?php include 'chat.tpl';?>
            <a href="<?php echo $this->url(array(
                'module' => 'chat',
                'controller' => 'index',
                'action' => 'view',
                'subject' => $this->subjectName, 
                'subject_id' => $this->subjectId,
                'channel_id' => $this->channel->id
            ))?>"><?php echo _('Все сообщения');?></a>
        </div>
        <div class="spacer"></div>
    </div>
</div>