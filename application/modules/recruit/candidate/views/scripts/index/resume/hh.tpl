<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<?php if (!$this->isAjaxRequest): ?>
<a href="<?php echo $this->referer; ?>">Назад</a>
<?php endif; ?>
<div class="at-form-report at-form-report-resume">
    <?php if(!empty($this->lists['resumeGeneral'])):?>
    <div class="report-summary clearfix resume-margin" style="min-height: 150px;">
        <div class="photo-block">
            <img src="<?php echo $this->lists['resumePhoto'];?>" alt="Фото отсутствует"/>
        </div>
        <div class="right-block">
            <h1 id="at-form-report-title"><?php echo $this->lists['resumeGeneral']['ФИО:']; ?></h1>
            <p id="resume-short-description">
                <?php echo $this->lists['resumeGeneral']['Пол:']; ?>,
                <?php echo $this->lists['resumeGeneral']['Возраст:']; ?>,
                <?php echo $this->lists['resumeGeneral']['День рождения:']; ?>
            </p>
            <p id="resume-links">
                <?php echo $this->lists['resumeGeneral']['Мобильный телефон']; ?><br>
                <?php echo $this->lists['resumeGeneral']['Эл. почта']; ?>
            </p>
            <?php unset($this->lists['resumeGeneral']['ФИО:']); ?>
            <?php unset($this->lists['resumeGeneral']['Пол:']); ?>
            <?php unset($this->lists['resumeGeneral']['Возраст:']); ?>
            <?php unset($this->lists['resumeGeneral']['День рождения:']); ?>
            <?php unset($this->lists['resumeGeneral']['Мобильный телефон']); ?>
            <?php unset($this->lists['resumeGeneral']['Эл. почта']); ?>
            <p id="resume-place">
                Проживает: <?php echo $this->lists['resumeGeneral']['Расположение:']; ?><br>
                <?php unset($this->lists['resumeGeneral']['Расположение:']); ?>
                <?php $resumeGeneral = array(); ?>
                <?php foreach ($this->lists['resumeGeneral'] as $key => $value): ?>
                    <?php echo ($value) ? $value : $key; ?><br>
                <?php endforeach; ?>
            </p>

<!--            --><?php //echo $this->reportList($resumeGeneral);?>
        </div>
    </div>
    <?php endif;?>
    <?php if(!empty($this->lists['resumeTitle'])):?>
    <div class="report-summary clearfix resume-margin" style="padding-bottom: 10px;">
        <h3>Желаемая должность и зарплата</h3>
        <div style="float: left;">
            <h2><b><?php echo $this->lists['resumeTitle'];?></b></h2>
        </div>
        <div style="float: right;">
            <h2><?php echo $this->lists['resumeAmount'];?></h2>
        </div>
    </div>
    <?php endif;?>
    <?php if(!empty($this->lists['resumeSpecialization'])):?>
    <div class="report-summary clearfix resume-margin">
        <?php foreach($this->lists['resumeSpecialization'] as $key=>$value):?>
        <?php echo '<b>'.$key.'</b>';?><br><br>
        <?php echo $value;?><br>
        <?php endforeach;?>
    </div>
    <?php endif;?>
    <?php if(!empty($this->lists['resumeEmployments'])):?>
    <div class="report-summary clearfix resume-margin">
        <?php echo '<b>'._('Занятость: ').'</b>'.$this->lists['resumeEmployments'];?>
    </div>
    <?php endif;?>
    <?php if(!empty($this->lists['resumeSchedules'])):?>
    <div class="report-summary clearfix resume-margin">
        <?php echo '<b>'._('График работы: ').'</b>'.$this->lists['resumeSchedules'];?>
    </div>
    <?php endif;?>
    <?php if(!empty($this->lists['resumeExperience'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo $this->total_experience;?></h3>
        <?php foreach($this->lists['resumeExperience'] as $item){
        echo $this->reportList($item, 'normal', true);
        }?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeSkill_set'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _('Ключевые навыки');?></h3><br>
        <?php echo $this->lists['resumeSkill_set'];?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeSkills'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _('Обо мне');?></h3><br>
        <?php echo $this->lists['resumeSkills'];?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationLevel']) || !empty($this->lists['resumeEducationPrimary']) || !empty($this->lists['resumeEducationElementary'])):?>
    <div class="report-summary clearfix resume-margin">
        <?php if(!empty($this->lists['resumeEducationLevel'])):?>
        <h3><?php echo _("Образование: ").$this->lists['resumeEducationLevel'];?></h3>
        <?php endif; ?>
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
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeLanguage'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _("Знание языков");?></h3><br>
        <?php echo $this->lists['resumeLanguage'];?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationAdditional'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _("Курсы повышения квалификации");?></h3><br>
        <?php foreach($this->lists['resumeEducationAdditional'] as $item){
        echo $this->reportList($item);
        }?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeEducationAttestation'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _("Тесты, экзамены");?></h3><br>
        <?php foreach($this->lists['resumeEducationAttestation'] as $item){
        echo $this->reportList($item);
        }?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeCertificate'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _("Сертификаты");?></h3><br>
        <?php foreach($this->lists['resumeCertificate'] as $item){
        echo $this->reportList($item);
        }?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumePortfolio'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _('Портфолио');?></h3><br>
        <?php foreach($this->lists['resumePortfolio'] as $key=>$value):?>
        <div class="photo-block">
            <img src="<?php echo $key;?>" alt="Фото отсутствует"/>
        </div>
        <div class="right-photo-block">
            <?php echo $value;?>
        </div>
        <?php endforeach;?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeCitizenship']) || !empty($this->lists['resumeWork_ticket']) ||
    !empty($this->lists['resumeTravel_time'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _('Гражданство, время в пути до работы');?></h3><br>
        <?php if(!empty($this->lists['resumeCitizenship'])):?>
        <?php echo _('Гражданство: ').$this->lists['resumeCitizenship'];?><br>
        <?php endif; ?>
        <?php if(!empty($this->lists['resumeWork_ticket'])):?>
        <?php echo _('Разрешение на работу: ').$this->lists['resumeWork_ticket'];?><br>
        <?php endif; ?>
        <?php if(!empty($this->lists['resumeTravel_time'])):?>
        <?php echo _('Желательное время в пути до работы: ').$this->lists['resumeTravel_time'];?><br>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if(!empty($this->lists['resumeRecommendation'])):?>
    <div class="report-summary clearfix resume-margin">
        <h3><?php echo _("Рекомендации");?></h3><br>
        <?php if(is_array($this->lists['resumeRecommendation'])){
            echo $this->reportList($this->lists['resumeRecommendation']);
        }else{
            echo $this->lists['resumeRecommendation'];
        }
        ?>
    </div>
    <?php endif; ?>
</div>