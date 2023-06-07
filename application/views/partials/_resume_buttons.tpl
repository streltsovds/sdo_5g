<?php if (!$this->isInitiator):?>
    <?php if ($this->declineable):?>
    <hm-resume-btn
            color="warning"
            :url='<?php echo json_encode($this->declineUrl); ?>'
            text="<?php echo _('Отклонить')?>"
            confirm-text="<?php echo _('Вы действительно желаете отклонить данного кандидата?')?>">
    </hm-resume-btn>
    <?php endif;?>
<?php else: ?>
    <?php if ($this->showComment): ?>
        <p style="font-weight: bold;"><?php echo $this->comment; ?></p>
    <?php else: ?>
        <hm-resume-btn
                color="success"
                :chief="true"
                text="<?php echo _('Пригласить')?>"
                :url='<?php echo json_encode($this->saveCommentUrl); ?>'
                :form-data='<?php echo json_encode($this->formData);?>'
                comment-hint="<?php echo _('Пожалуйста, предложите удобное для Вас время') ?>"
                confirm-text="<?php echo _('Вы действительно желаете пригласить данного кандидата на собеседование?'); ?>">
        </hm-resume-btn>
        <hm-resume-btn
                color="warning"
                :chief="true"
                :url='<?php echo json_encode($this->saveCommentUrl); ?>'
                text="<?php echo _('Отказать')?>"
                :form-data='<?php echo json_encode($this->formData);?>'
                comment-hint="<?php echo _('Пожалуйста, прокомментируйте Ваше решение') ?>"
                confirm-text="<?php echo _('Вы действительно желаете отклонить данного кандидата?')?>">
        </hm-resume-btn>
    <?php endif;?>
<?php endif;?>


