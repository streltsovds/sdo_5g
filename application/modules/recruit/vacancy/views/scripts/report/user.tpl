<div class="at-form-report">
    <v-layout wrap>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Отчёт о прохождении сессии подбора');?></v-card-title>
                <v-card-text>
                    <?php if (!$this->reportOnly): ?>
                        <?php if (!$this->print && count($this->candidate_id)):?>
                            <a class="report-link" href='<?php echo $this->url(array(
                                'module' => 'candidate',
                                'controller' => 'index',
                                'action' => 'resume',
                                'candidate_id' => $this->candidate_id,
                                ));?>'
                            >
                                <?php echo _('Резюме кандидата')?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php echo $this->reportList($this->lists['general']);?>
                    <?php echo $this->reportList($this->lists['session']);?>
                </v-card-text>
            </v-card>
        </v-flex>
        <v-flex xs12>
            <v-card>
                <v-card-title class="headline"><?php echo _('Этапы программы подбора');?></v-card-title>
                <?php Zend_Controller_Front::getInstance()->addModuleDirectory(APPLICATION_PATH . '/modules/at');?>
                <v-card-text>
                    <v-layout wrap>
                        <?php foreach ($this->methods as $params):?>
                            <?php if (!$params['name']) continue; ?>
                            <?php
                                $method = isset($params['evaluation']) ? $this->action($params['evaluation']->method, 'report-methods', 'session', $params) : '';
                                $bAddInfo = trim($params['status']) || trim($params['date']) || trim($params['comment']);
                            ?>
                            <v-flex xs12>
                                <h2 class="mb-1">
                                    <v-layout>
                                        <?php
                                            switch($params['status']) {
                                                case HM_Programm_Event_User_UserModel::STATUS_FAILED:
                                                   $statusIcon = 'highlight_off'; break;
                                                case HM_Programm_Event_User_UserModel::STATUS_PASSED:
                                                    $statusIcon = 'check_circle_outline'; break;
                                                case HM_Programm_Event_User_UserModel::STATUS_CONTINUING:
                                                    $statusIcon = 'panorama_fish_eye'; break;
                                            }
                                        ?>
                                        <?php if ($statusIcon): ?>
                                        <v-tooltip bottom class="mr-2">
                                            <v-icon slot="activator"><?php echo $statusIcon ?></v-icon>
                                            <span><?php echo HM_Programm_Event_User_UserModel::getTitle($params['status']); ?></span>
                                        </v-tooltip>
                                        <?php endif; ?>
                                        <span><?php echo $params['name']; ?></span>
                                    </v-layout>
                                </h2>
                                <div class="mt-2">
                                    <?php if ($params['date']): ?>
                                        <span class="secondary--text"><?php echo $params['date']; ?></span>
                                    <?php endif;?>
                                    <?php if ($params['comment']) : ?>
                                        <p><?php echo sprintf('%s, %s: %s', $params['comment_date'], $params['comment_user'], nl2br($params['comment'])); ?></p>
                                    <?php endif; ?>

                                    <?php if (trim($method)):?>
                                        <div class="recruit-step-event">
                                            <?php echo $method?>
                                        </div>
                                    <?php endif;?>
                                </div>
                            </v-flex>
                        <?php endforeach;?>
                    </v-layout>
                </v-card-text>
            </v-card>
        </v-flex>
    </v-layout>
</div>
<?php if (!$this->print):?>
    <hm-print-btn
            text='<?php echo _("Печать")?>'
            :url='<?php echo json_encode($this->url(array("module" => "vacancy", "controller" => "report", "action" => "user", "print" => 1)))?>'
            name='report'
    ></hm-print-btn>
<?php else:?>
    <?php $this->inlineScript()->captureStart(); ?>
        setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>

