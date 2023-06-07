<?php
    $save_comment_url =  "/recruit/candidate/index/set-chief-comment";
    $decline_url = $this->vacancyCandidate ? $this->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'change-status', 'blank' => null, 'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id, 'status' => '2_-1' /*отклонен*/)) : '';
    $print_url = $this->url(array('module' => 'candidate', 'controller' => 'index', 'action' => 'resume', 'print' => 1, 'blank' => 1));
?>
<div class="at-form-report-resume at-form-report-resume-html">
    <div class="report-summary resume-margin">
        <?php if (!$this->isAjax): ?> <v-card> <?php endif; ?>
            <v-card-title class="headline"><?php echo $this->name;?></v-card-title>
            <?php if (!$this->print):?>
                <v-card-text>
                    <v-layout wrap>
                        <?php echo $this->partial('_resume_buttons.tpl', array(
                            'isInitiator' => $this->isInitiator,
                            'declineable' => $this->declineable,
                            'showComment' => (!empty($this->stateData->comment) && $this->currentUserHasComments) || $this->hideButtons,
                            'comment' => $this->stateData->comment,
                            'saveCommentUrl' => $save_comment_url,
                            'declineUrl' => $decline_url,
                            'formData' => [
                                'processId' => $this->processId,
                                'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id,
                            ]
                        )); ?>
                        <?php if ($print_url && !$this->isAjax):?>
                            <hm-print-btn
                                    text='<?php echo _("Печать")?>'
                                    :url='<?php echo json_encode($print_url)?>'
                                    name='report'
                            ></hm-print-btn>
                        <?php endif;?>
                    </v-layout>
                </v-card-text>
            <?php endif;?>

            <v-card-text>
                <?php echo $this->candidate->resume_html; ?>
            </v-card-text>

            <?php if (!$this->print):?>
                <v-card-text>
                    <v-layout wrap>
                        <?php echo $this->partial('_resume_buttons.tpl', array(
                            'isInitiator' => $this->isInitiator,
                            'declineable' => $this->declineable,
                            'showComment' => (!empty($this->stateData->comment) && $this->currentUserHasComments) || $this->hideButtons,
                            'comment' => $this->stateData->comment,
                            'saveCommentUrl' => $save_comment_url,
                            'declineUrl' => $decline_url,
                            'formData' => [
                                'processId' => $this->processId,
                                'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id,
                            ]
                        )); ?>
                        <?php if ($print_url && !$this->isAjax):?>
                            <hm-print-btn
                                    text='<?php echo _("Печать")?>'
                                    :url='<?php echo json_encode($print_url)?>'
                                    name='report'
                            ></hm-print-btn>
                        <?php endif;?>
                    </v-layout>
                </v-card-text>
            <?php endif;?>
        </div>
    <?php if (!$this->isAjax): ?> <v-card> <?php endif; ?>
</div>

<?php if ($this->print):?>
    <?php $this->inlineScript()->captureStart(); ?>
        setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>
