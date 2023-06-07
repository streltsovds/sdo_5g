<div class="at-form-report at-form-report-resume at-form-report-resume-small">
    <?php if (!$this->isAjax): ?> <v-card> <?php endif; ?>
        <v-card-title class="headline"><?php echo $this->name;?></v-card-title>
        <v-card-text>
            <hm-download-btn
                    text='<?php echo _("Скачать резюме")?>'
                    url='<?php echo $this->url(array("module" => "candidate", "controller" => "index", "action" => "download", "candidate_id" => $this->candidate->candidate_id))?>'
                    name='resume'
            ></hm-download-btn>
            <?php if (!$this->print):?>
                <v-layout wrap>
                    <?php echo $this->partial('_resume_buttons.tpl', array(
                        'isInitiator' => $this->isInitiator,
                        'declineable' => $this->declineable,
                        'showComment' => (!empty($this->stateData->comment) && $this->currentUserHasComments) || $this->hideButtons,
                        'comment' => $this->stateData->comment,
                        'saveCommentUrl' => "/recruit/candidate/index/set-chief-comment",
                        'declineUrl' => $this->vacancyCandidate ? $this->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'change-status', 'blank' => null, 'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id, 'status' => '2_-1' /*отклонен*/)) : '',
                        'formData' => [
                            'processId' => $this->processId,
                            'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id,
                        ]
                    )); ?>
                </v-layout>
            <?php endif;?>
        </v-card-text>
    <?php if (!$this->isAjax): ?> </v-card> <?php endif; ?>
</div>

<?php if ($this->print):?>
    <?php $this->inlineScript()->captureStart(); ?>
        setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>