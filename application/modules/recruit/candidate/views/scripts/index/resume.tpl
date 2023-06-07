<?php
$decline_url = $this->vacancyCandidate ? $this->url(array('module' => 'candidate', 'controller' => 'list', 'action' => 'change-status', 'blank' => null, 'vacancy_candidate_id' => $this->vacancyCandidate->vacancy_candidate_id, 'status' => '2_-1' /*отклонен*/)) : '';
$save_comment_url = "/recruit/candidate/index/set-chief-comment";
$print_url = $this->url(array("module" => "candidate", "controller" => "index", "action" => "resume", "print" => 1, "blank" => 1));
?>
<div class="at-form-report at-form-report-resume">
    <?php if (!$this->isAjax): ?> <v-card> <?php endif; ?>
    <?php if(!empty($this->lists['resumeGeneral'])):?>
            <v-layout wrap>
                <v-flex xs4 sm3 md2>
                    <div class="photo-block">
                        <?php $photo = ($this->user && $this->user->getPhoto()) ? $this->user->getPhoto() : '/images/people/nophoto.gif'; ?>
                        <?php if (!$this->print): ?>
                            <v-img src="<?php echo $photo; ?>" alt="<?php echo $this->escape($this->user->getName())?>"/>
                        <?php else: ?>
                            <img src="<?php echo $photo; ?>" alt="<?php echo $this->escape($this->user->getName())?>"/>
                        <?php endif; ?>
                    </div>
                </v-flex>
                <v-flex xs8 sm9 md10>
                        <v-card-title>
                            <div>
                                <div class="headline"><?php echo $this->user->getName(); ?></div>
                                <span class="headline-subheader">
                                    <?php echo $this->lists['resumeGeneral']['Пол:']; ?>,
                                    <?php echo $this->lists['resumeGeneral']['Возраст:']; ?>,
                                    <?php echo $this->lists['resumeGeneral']['День рождения:']; ?>
                                </span>
                            </div>
                        </v-card-title>
                        <v-card-text class="resume-links">
                            <?php echo $this->lists['resumeGeneral']['Мобильный телефон']; ?><br>
                            <?php echo $this->lists['resumeGeneral']['Эл. почта']; ?>
                        </v-card-text>
                </v-flex>
                <?php unset($this->lists['resumeGeneral']['ФИО:']); ?>
                <?php unset($this->lists['resumeGeneral']['Пол:']); ?>
                <?php unset($this->lists['resumeGeneral']['Возраст:']); ?>
                <?php unset($this->lists['resumeGeneral']['День рождения:']); ?>
                <?php unset($this->lists['resumeGeneral']['Мобильный телефон']); ?>
                <?php unset($this->lists['resumeGeneral']['Эл. почта']); ?>
                <v-flex xs12>
                    <v-card-text>
                        Проживает: <?php echo $this->lists['resumeGeneral']['Расположение:']; ?><br>
                        <?php unset($this->lists['resumeGeneral']['Расположение:']); ?>
                        <?php $resumeGeneral = array(); ?>
                        <?php foreach ($this->lists['resumeGeneral'] as $key => $value): ?>
                            <?php echo ($value) ? $value : $key; ?><br>
                        <?php endforeach; ?>
                    </v-card-text>
                </v-flex>
            </v-layout>

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
                    </v-layout>
                </v-card-text>
            <?php endif;?>
    <?php endif;?>
        <v-card-title>
            <div>
                <div class="headline">Желаемая должность и зарплата</div>
                <?php if(!empty($this->lists['resumeTitle'])):?>
                    <v-layout wrap justify-space-between class="headline-subheader">
                        <v-flex><?php echo $this->lists['resumeTitle'];?></v-flex>
                        <v-flex style="flex-grow: 0"><?php echo $this->lists['resumeAmount'];?></v-flex>
                    </v-layout>
                <?php endif;?>
            </div>
        </v-card-title>
        <v-card-text>
            <?php if(!empty($this->lists['resumeSpecialization'])):?>
                <div class="report-summary resume-margin">
                    <?php foreach($this->lists['resumeSpecialization'] as $key=>$value):?>
                    <?php echo '<b>'.$key.'</b>';?><br><br>
                        <?php echo $value;?><br>
                    <?php endforeach;?>
                </div>
            <?php endif;?>
            <?php if(!empty($this->lists['resumeEmployments'])):?>
                <div class="report-summary resume-margin">
                <?php echo '<b>'._('Занятость: ').'</b>'.$this->lists['resumeEmployments'];?>
            </div>
            <?php endif;?>
            <?php if(!empty($this->lists['resumeSchedules'])):?>
            <div class="report-summary resume-margin">
                <?php echo '<b>'._('График работы: ').'</b>'.$this->lists['resumeSchedules'];?>
            </div>
            <?php endif;?>
        </v-card-text>
    <?php if(!empty($this->lists['resumeExperience'])):?>
            <v-card-title class="headline">
                <?php echo $this->total_experience;?>
            </v-card-title>
            <v-card-text>
                <?php foreach($this->lists['resumeExperience'] as $item){
                    echo $this->reportList($item, 'normal', true);
                }?>
            </v-card-text>
    <?php endif; ?>

    <?php if(!empty($this->lists['resumeSkill_set'])):?>
        <v-card-title class="headline"><?php echo _('Ключевые навыки');?></v-card-title>
        <v-card-text>
            <?php echo $this->lists['resumeSkill_set'];?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeSkills'])):?>
        <v-card-title><?php echo _('Обо мне');?></v-card-title>
        <v-card-text><?php echo $this->lists['resumeSkills'];?></v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationLevel']) || !empty($this->lists['resumeEducationPrimary']) || !empty($this->lists['resumeEducationElementary'])):?>
        <?php if(!empty($this->lists['resumeEducationLevel'])):?>
            <v-card-title class="headline"><?php echo _("Образование: ").$this->lists['resumeEducationLevel'];?></v-card-title>
        <?php endif; ?>
        <v-card-text>
            <?php if(!empty($this->lists['resumeEducationPrimary'])):?>
                <?php foreach($this->lists['resumeEducationPrimary'] as $item){
                echo $this->reportList($item, 'normal', true);
                }?>
            <?php endif; ?>
            <?php if(!empty($this->lists['resumeEducationElementary'])):?>
                <?php foreach($this->lists['resumeEducationElementary'] as $item){
                echo $this->reportList($item);
                }?>
            <?php endif; ?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeLanguage'])):?>
        <v-card-title class="headline"><?php echo _("Знание языков");?></v-card-title>
        <v-card-text><?php echo $this->lists['resumeLanguage'];?></v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationAdditional'])):?>
        <v-card-title class="headline"><?php echo _("Курсы повышения квалификации");?></v-card-title>
        <v-card-text>
            <?php foreach($this->lists['resumeEducationAdditional'] as $item){
                echo $this->reportList($item);
            }?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationAttestation'])):?>
        <v-card-title class="headline"><?php echo _("Тесты, экзамены");?></v-card-title>
        <v-card-text>
            <?php foreach($this->lists['resumeEducationAttestation'] as $item){
                echo $this->reportList($item);
            }?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeCertificate'])):?>
        <v-card-title><?php echo _("Сертификаты");?></v-card-title>
        <v-card-text>
            <?php foreach($this->lists['resumeCertificate'] as $item){
                echo $this->reportList($item);
            }?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumePortfolio'])):?>
        <v-card-title><?php echo _('Портфолио');?></v-card-title>
        <v-card-text>
            <?php foreach($this->lists['resumePortfolio'] as $key=>$value):?>
                <div class="photo-block">
                    <img src="<?php echo $key;?>" alt="Фото отсутствует"/>
                </div>
                <div class="right-photo-block">
                    <?php echo $value;?>
                </div>
            <?php endforeach;?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeCitizenship']) || !empty($this->lists['resumeWork_ticket']) ||
        !empty($this->lists['resumeTravel_time'])):?>
        <v-card-title class="headline"><?php echo _('Гражданство, время в пути до работы');?></v-card-title>
        <v-card-text>
            <?php if(!empty($this->lists['resumeCitizenship'])):?>
                <?php echo _('Гражданство: ').$this->lists['resumeCitizenship'];?><br>
            <?php endif; ?>
            <?php if(!empty($this->lists['resumeWork_ticket'])):?>
                <?php echo _('Разрешение на работу: ').$this->lists['resumeWork_ticket'];?><br>
            <?php endif; ?>
            <?php if(!empty($this->lists['resumeTravel_time'])):?>
                <?php echo _('Желательное время в пути до работы: ').$this->lists['resumeTravel_time'];?><br>
            <?php endif; ?>
        </v-card-text>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeRecommendation'])):?>
        <v-card-title class="headline">
            <?php echo _("Рекомендации");?>
        </v-card-title>
        <v-card-text>
            <?php if(is_array($this->lists['resumeRecommendation'])){
            echo $this->reportList($this->lists['resumeRecommendation']);
            }else{
            echo $this->lists['resumeRecommendation'];
            }
            ?>
        </v-card-text>
    <?php endif; ?>
<?php if (!$this->isAjax): ?> </v-card> <?php endif; ?>
    <?php if (!$this->print):?>
        <v-card-text>
            <v-layout justify-space-between>
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
                </v-layout>
                <?php if (!$this->isBlankView && !$this->isInitiator && count($this->candidate->vacancies)):?>
                    <a class="report-link"
                                    href='<?php echo $this->url(array(
                            "module" => "vacancy",
                            "controller" => "report",
                            "action" => "user",
                            "vacancy_id" => $this->vacancyCandidate->vacancy_id,
                            "vacancy_candidate_id" => $this->vacancyCandidate->vacancy_candidate_id,
                            ));
                        ?>'>
                    <?php echo _('Отчёт о прохождении подбора')?>
                </a>
                <?php endif;?>
            </v-layout>
            <?php if ($print_url):?>
                <hm-print-btn
                        text='<?php echo _("Печать")?>'
                        :url='<?php echo json_encode($print_url)?>'
                        name='report'
                ></hm-print-btn>
            <?php endif;?>
        </v-card-text>
    <?php endif;?>
</div>
<?php if ($this->print):?>
    <?php $this->inlineScript()->captureStart(); ?>
        setTimeout(_.bind(window.print, window), 2000);
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif;?>

