<?php

//    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');
//    $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/competencies.css'), 'screen,print');
?>
<div class="ts-end__header" style="margin-bottom: 30px">
    <span style="font-size: 34px;font-weight: 300; line-height: 32px; color: #2a2a2a"><?php echo $this->quest->name;?></span>
</div>
<v-card>
    <v-card-text>
        <?php if (1 || $this->results['show_result']) : ?>
        <v-layout wrap fill-height>
            <v-flex>
                <v-list subheader>
                    <v-subheader class="title secondary--text ts-end__result">  <!--  ts - testings  ts-end__result - testing end result ( конец результатов тестирования )   -->
                        <span style="font-size: 20px; font-weight: 500; line-height: 24px; color: #1e1e1e"><?=_('Результаты')?></span>
                    </v-subheader>
<!--                    <v-divider></v-divider>-->
                    <div class="ts-end__result-info" >
                        <?php echo $this->reportList($this->results['global']);?>
                    </div>
                </v-list>
            </v-flex>
            <?php if ($this->results['clusters'] && $this->quest && $this->quest->type !== HM_Quest_QuestModel::TYPE_POLL) : ?>
            <v-flex>
                <v-list subheader>
                    <v-subheader class="title secondary--text">
                        <?=$this->results['show_result']?_('Результаты по темам'):_('Вопросы по темам')?>
                    </v-subheader>
<!--                    <v-divider></v-divider>-->
                    <?php echo $this->reportList($this->results['clusters']);?>
                </v-list>
            </v-flex>
            <?php endif; ?>
        </v-layout>
        <?php endif; ?>
        <?php if (!$this->results['show_result']): ?>

        <?php if ($this->quest->type === HM_Quest_QuestModel::TYPE_POLL): ?>
            <v-alert type="success" value="true" outlined>
                <?= _('Опрос завершен, спасибо за уделенное время!') ?>
            </v-alert>
        <?php endif; ?>

       <?php if ($this->quest->type === HM_Quest_QuestModel::TYPE_TEST): ?>
            <v-alert type="success" value="true" outlined>
                <?= _('Тестирование завершено, спасибо за уделенное время!') ?>
            </v-alert>
        <?php endif; ?>

    </v-card-text>
    <?php else: ?>
        </v-card-text>
    <?php endif; ?>
    <v-card-actions style="padding-left: 24px; padding-bottom: 26px">
        <?php if ($this->detailsUrl): ?>
            <v-btn color="warning" href="<?= $this->detailsUrl ?>" target="_top" title="<?= _('Посмотреть детальный отчет') ?>"
                   class="at-form-button at-form-stop" style="width: 125px; display: flex; justify-content: center; align-items: center; margin-right: 28px">
                <span style="font-size: 16px; font-weight: 300; text-transform: none">Отчёт</span>
            </v-btn>
        <?php endif; ?>
        <v-btn color="primary" text id="exit-btn" href="<?= $this->stopUrl ?>" target="_top" class="at-form-button at-form-next" style="width: 125px; display: flex; justify-content: center; align-items: center; border: 1px solid #70889E">
            <span style="font-size: 16px; font-weight: 400; text-transform: none; color: #1e1e1e">Выйти</span>
        </v-btn>
        <?php if ($this->detailsUrl) : ?>
<!--        <v-divider class="ml-1 mr-1" vertical></v-divider>-->
        <?php endif; ?>
    </v-card-actions>
</v-card>


<?php $this->inlineScript()->captureStart(); ?>
    document.querySelector('#exit-btn').onclick = (e) => {
        window.COMMON_DATA = {
        event_id: "close_window"
        }
    }
<?php $this->inlineScript()->captureEnd(); ?>