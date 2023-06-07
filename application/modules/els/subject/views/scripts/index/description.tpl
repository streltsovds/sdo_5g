<v-card>
    <v-card-text>
        <?php if ($this->subject->description):?>
            <?php echo $this->subject->description?>
        <?php elseif ($this->isDean): ?>
            <?php $link = sprintf("<a href='%s'>%s</a>", $this->url(['module' => 'subject', 'controller' => 'list', 'action' => 'edit', 'subject_id' => $this->subject->subid]), _('Создать'));?>
            <hm-empty empty-type="full" sub-label="<?php echo
                sprintf(_('Описание курса еще не создано. %s'), $link);?>">
            </hm-empty>
        <?php else: ?>
            <hm-empty empty-type="full" sub-label="<?php echo _('Описание курса не создано');?>"></hm-empty>
        <?php endif ;?>


        <?php if ($this->regStatus):?>
            <?php if ($this->regStatus->isButton):?>
                <div class="my-2">
                    <v-btn color="warning" dark href="<?= $this->regStatus->href?>"><?= $this->regStatus->text?></v-btn>
                </div>
            <?php endif;?>
        <?php endif;?>
    </v-card-text>
    <hm-feedback-course :feedbacks='<?= $this->feedbackData?>'></hm-feedback-course>
</v-card>