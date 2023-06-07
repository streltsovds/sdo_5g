<div class="at-form-report">
<?php if ($this->print):?>
    <h1 class="headline"><?php echo _('Индивидуальный отчет');?></h1>
<?php endif;?>

<?php if ($this->sessionUser->status != HM_At_Session_User_UserModel::STATUS_COMPLETED): ?>
    <v-alert
            :value="true"
            color="error"
            icon="warning"
            outlined
    >
        <?php echo _('ВНИМАНИЕ! Пользователь ещё не прошел оценку, результаты предварительные');?>
    </v-alert>
<?php endif;?>
    <v-card class="report-summary">
        <v-card-title class="headline">
            <?php echo _('Общая информация');?>
        </v-card-title>
        <v-card-text>
            <v-layout row wrap>
                <v-flex xs12 sm6>
                    <?php echo $this->reportList($this->lists['general']);?>
                </v-flex>
                <v-flex xs12 sm6>
                    <?php echo $this->reportList($this->lists['session']);?>
                </v-flex>
                <v-flex xs12>
                    <?php echo $this->reportText($this->texts['general'], _('Вводная часть'));?>
                </v-flex>
            </v-layout>
        </v-card-text>
    </v-card>

    <div class="pagebreak"></div>

    <?php foreach ($this->methods as $method => $params): ?>
        <?= $this->action($method, 'report-methods', 'session', $params); ?>
    <?php endforeach; ?>

    <?php if ($this->showCommentForm): ?>
        <br>

        <?php echo $this->commentForm; ?>

    <?php endif; ?>

</div>
<?php if (!$this->print):?>
    <div>
        <hm-print-btn
                text='<?php echo _("Печать")?>'
                :url='<?php echo json_encode($this->url(array("module" => "session", "controller" => "report", "action" => "user", "session_user_id" => $this->sessionUser->session_user_id, "print" => 1)))?>'
                name='report'
        ></hm-print-btn>
        <?php if ($this->sessionUser->status == HM_At_Session_User_UserModel::STATUS_COMPLETED): ?>
            <v-btn
                color="warning"
                dark
                target="_blank"
                href="/file/get/report/session_id/<?php echo $this->sessionUser->session_id;?>/session_user_id/<?php echo $this->sessionUser->session_user_id;?>">
                <?php echo _("Скачать как PDF")?>
            </v-btn>
        <?php endif;?>
    </div>
<?php endif;?>

<?php if ($this->print && !$this->pdf):?>
    <?php $this->inlineScript()->captureStart(); ?>
        setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>
