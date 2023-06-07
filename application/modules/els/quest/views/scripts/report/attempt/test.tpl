<div class="at-form-report at-form-report-page">
    <v-card class="report-summary profile-report__wrap">

        <v-layout fill-height>
            <v-flex row wrap>
                <v-flex xs12 sm12 md12 class="pa-0">
                    <v-subheader class="title secondary--text start-testings-header">
                        <h2><?php echo _('Отчет о тестировании'); ?></h2>
                    </v-subheader>
                </v-flex>
            </v-flex>
        </v-layout>

        <v-layout fill-height>
            <v-flex row wrap>
                <v-flex xs12 sm12 md12 class="pa-0">
                    <v-card-text class="start-testings-header">
                        <h2><?php echo sprintf(_('Результат: %s'), $this->totalResult); ?></h2>
                    </v-card-text>
                </v-flex>
            </v-flex>
        </v-layout>

        <v-layout style="margin-bottom: 26px" fill-height>
            <v-flex row wrap>
                <v-flex>
                    <div class="testings">
                        <?php echo $this->reportList(array_merge($this->lists['general-test'], $this->lists['general-context'])); ?>
                    </div>
                </v-flex>
            </v-flex>
        </v-layout>

        <v-layout style="margin-bottom: 26px" fill-height>
            <v-flex row wrap>
                <v-flex>
                    <v-list subheader>

                        <v-subheader class="title secondary--text start-testings-header">
                            <h2><?php echo _('Результаты по темам'); ?></h2>
                        </v-subheader>

                        <div class="testings">
                            <?php echo $this->reportList($this->lists['clusters']); ?>
                        </div>
                    </v-list>
                </v-flex>
            </v-flex>
        </v-layout>


        <?php if (count($this->questions)): ?>

            <v-layout style="margin-bottom: 26px" fill-height>
                <v-flex row wrap>
                    <v-flex>
                        <v-list subheader>

                            <v-subheader class="title secondary--text start-testings-header">
                                <h2><?php echo _('Подробные результаты'); ?></h2>
                            </v-subheader>
                            <?php foreach ($this->questions as $question): ?>

                                <h3><?php echo ++$i . '. ' . $question->shorttext; ?></h3>
                                <?php echo $this->reportList($this->lists['question-' . $question->question_id], HM_View_Helper_ReportList::CLASS_COLORED_QUESTION); ?>

                            <?php endforeach; ?>

                        </v-list>
                    </v-flex>
                </v-flex>
            </v-layout>

            <v-layout style="margin-bottom: 16px" fill-height>
                <v-flex row wrap>
                    <v-flex>

                        <?php if (!$this->print):
                        // Не надо забирать все HTTP-параметры, сделаем некое ограничение
                        $params = array_intersect_key($this->getRequest()->getParams(), array_flip(['module', 'controller', 'action', 'attempt_id']));
                        $params['print'] = 1;
                        ?>
                            <hm-print-btn
                                    text='<?php echo _('Печать') ?>'
                                    :url='<?php echo json_encode($this->url($params)) ?>'
                                    name='report'
                            ></hm-print-btn>
                        <?php endif; ?>
                    </v-flex>
            </v-layout>
        <?php endif; ?>
    </v-card>
</div>

<?php if ($this->print): ?>
    <?php $this->inlineScript()->captureStart(); ?>
    setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>
