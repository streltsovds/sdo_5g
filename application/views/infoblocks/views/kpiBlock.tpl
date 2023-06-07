<v-card-text id="kpi" class="hm-infoblock kpi">
    <?php if ($this->message):?>
        <v-alert type="info" outlined value="true">
            <?php echo $this->message;?>
        </v-alert>
    <?php else:?>
    <div class="kpi__progress">
        <h4 class="kpi__progress-title">
            <?php echo _('Прогресс достижения');?>
        </h4>
        <div class="kpi__progress-bar">
            <?= $this->progress($this->progress, 'large'); ?>
        </div>
    </div>
    <?php foreach($this->clusters as $cluster => $kpis):?>
    <div class="kpi__header">
        <div class="kpi__header-column kpi__header-column--column-1">
            <?php if ($cluster != HM_At_Kpi_Cluster_ClusterModel::NONCLUSTERED):?>
                <h4>
                    <?php echo $cluster; ?>
                </h4>
            <?php endif;?>
        </div>
        <div class="kpi__header-column">
            <div class="kpi-desc">
                <?php echo _('план');?>
            </div>
        </div>
        <div class="kpi__header-column">
            <div class="kpi-desc">
                <?php echo _('факт') . HM_View_Helper_Footnote::marker(1);?>
            </div>
        </div>
        <div class="kpi__header-column">
            <div class="kpi-desc">
                <?php echo _('комментарий');?>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
        <?php foreach($kpis as $kpiId => $userKpi):?>
            <div class="kpi__body lesson_min">
                <div class="kpi__body-column kpi__body-column--column-1">
                    <?php echo $userKpi['name']; ?><?php echo $userKpi['unit'] ? ', ' . $userKpi['unit'] : ''; ?>
                </div>
                <?php if ($userKpi['value_type'] == 2): // качественная ?>
                    <div class="kpi__body-column kpi__body-column--span-2">
                        <?php echo $this->score(array(
                            'score' => (int)$userKpi['value_fact'] ? $userKpi['value_fact'] : -1,
                            'lesson_id' => $userKpi['user_kpi_id'],
                            'user_id' => $userKpi['user_id'],
                            'scale_id' => HM_Scale_ScaleModel::TYPE_BINARY,
                            'mode' => HM_View_Helper_Score::MODE_FORSTUDENT,
                            'disabled' => $this->disabled
                        ));?>
                    </div>
                <?php else:?>
                    <div class="kpi__body-column">
                        <?php echo $this->score(array(
                            'score' => $userKpi['value_plan'],
                            'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                            'mode' => HM_View_Helper_Score::MODE_DEFAULT,
                        ));?>
                    </div>
                    <div class="kpi__body-column">
                        <?php echo $this->score(array(
                            'score' => (int)$userKpi['value_fact'] ? $userKpi['value_fact'] : -1,
                            'lesson_id' => $userKpi['user_kpi_id'],
                            'user_id' => $userKpi['user_id'],
                            'scale_id' => HM_Scale_ScaleModel::TYPE_CONTINUOUS,
                            'mode' => HM_View_Helper_Score::MODE_FORSTUDENT,
                            'placeholder' => _('ввод'),
                            'disabled' => $this->disabled
                        ));?>
                    </div>
                <?php endif;?>
                <div class="kpi__body-column">
                    <textarea <?php if ($this->disabled) echo 'disabled'; ?> class="tComment" data-id="<?php echo $userKpi['user_kpi_id'];?>"><?php echo $userKpi['comments'];?></textarea>
                </div>

            </div>
        <?php endforeach;?>
    <?php endforeach;?>
    <?php endif;?>
</v-card-text>

<?php echo $this->footnote();?>

<?php $this->inlineScript()->captureStart(); ?>
if(typeof initMarksheet=="function"){
    initMarksheet({
        url: {
            comments: "<?php echo $this->url(array('baseUrl' =>'at', 'module' =>'kpi', 'controller' => 'user', 'action' => 'set-comment'));?>",
            score: "<?php echo $this->url(array('baseUrl' =>'at', 'module' =>'kpi', 'controller' => 'user', 'action' => 'set-score'));?>"
        },
        l10n: {
            save: "<?php echo _("Сохранить"); ?>",
            noStudentActionSelected: "<?php echo _("Не выбрано ни одного действия со слушателем"); ?>",
            noStudentSelected: "<?php echo _("Не выбрано ни одного слушателя"); ?>",
            noLessonActionSelected: "<?php echo _("Не выбрано ни одного действия с занятием"); ?>",
            noLessonSelected: "<?php echo _("Не выбрано ни одного занятия"); ?>",
            formError: "<?php echo _("Ошибка формы") ?>",
            ok: "<?php echo _("Хорошо"); ?>",
            confirm: "<?php echo _("Подтверждение"); ?>",
            areUShure: "<?php echo _("Данное действие может быть необратимым. Вы действительно хотите продолжить?"); ?>",
            yes: "<?php echo _("Да"); ?>",
            no: "<?php echo _("Нет"); ?>"
        }
    });
}
<?php $this->inlineScript()->captureEnd(); ?>
