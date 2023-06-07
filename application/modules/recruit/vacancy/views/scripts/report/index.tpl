<?php //echo $this->headSwitcher(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'index', 'switcher' => 'report'), 'vacancyCard');?>
<?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
<div class="at-form-report">
<?php if ($this->print):?>
    <h1><?php echo $this->vacancy->name;?></h1>
<?php endif;?>

<h2><?php echo _('Общая информация');?> <a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['general']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['compensation']);?>
    </div>
</div>

<h2><?php echo _('Задачи и обязанности');?> <a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="clearfix">
<?php echo $this->reportTable($this->tables['tasks'], _('Задачи и обязанности'), '#');?>
</div>

<h2><?php echo _('Параметры вакансии');?> <a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['options-1']);?>
    </div>
    <div class="right-block">
        <?php echo $this->reportList($this->lists['options-2']);?>
    </div>
</div>

<h2><?php echo _('Требования по профстандартам');?> <!--a class="edit" href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'index', 'action' => 'skills'))?>" class="edit">&nbsp;</a--></h2>
<div class="clearfix">
<?php echo $this->reportTable($this->tables['skills'], ''/*, _('№ п/п')*/);?>
</div>

<h2><?php echo _('Дополнительные требования для кандидата (отличные от профиля)');?><a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="report-summary clearfix">
    <?php echo $this->reportList($this->lists['options-misc']);?>
</div>

<h2><?php echo _('Каналы поиска');?> <a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <?php echo $this->reportList($this->lists['search-channels']);?>
    </div>
    <div class="right-block">
        <?php foreach($this->searchChannelOptions as $title => $key): ?>
        <h3><?php echo $title;?></h3>
        <?php echo $this->reportList($this->lists[$key], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
        <?php endforeach;?>
    </div>
</div>

<?php if ($this->lists['experience'] || $this->lists['experience_companies']): ?>
<h2><?php echo _('Опыт работы кандидата');?> <a class="edit" href="<?php echo $this->url(array('module' => 'vacancy', 'controller' => 'index', 'action' => 'edit'))?>" class="edit">&nbsp;</a></h2>
<div class="report-summary clearfix">
    <div class="left-block">
        <h3><?php echo _('Сфера деятельности');?></h3>
        <?php echo $this->reportList($this->lists['experience'], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
    </div>
    <div class="right-block">
        <h3><?php echo _('Компании');?></h3>
        <?php echo $this->reportList($this->lists['experience_companies'], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
    </div>
</div>
<?php endif; ?>

<?php if ($this->criteriaTypes[HM_At_Evaluation_EvaluationModel::TYPE_TEST]):?>
<h2><?php echo _('№ п/п');?> <a class="edit" href="<?php echo $this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'edit', 'programm_id' => $this->programm->programm_id, 'vacancy_id' => $this->vacancy->vacancy_id, 'method' => HM_At_Evaluation_EvaluationModel::TYPE_TEST, 'baseUrl' => ''));?>" class="edit">&nbsp;</a></h2>
<div class="clearfix">
<?php echo $this->reportList($this->lists['criteria-test'], HM_View_Helper_ReportList::CLASS_WITHOUT_KEYS);?>
</div>
<?php endif; ?>

<?php if ($this->criteriaTypes[HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE]):?>
<h2><?php echo _('Корпоративные компетенции');?> <a class="edit edit-programm" href="<?php echo $this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'edit', 'programm_id' => $this->programm->programm_id, 'vacancy_id' => $this->vacancy->vacancy_id, 'submethod' => implode('_', array(HM_At_Evaluation_EvaluationModel::TYPE_COMPETENCE, HM_At_Evaluation_EvaluationModel::RELATION_TYPE_RECRUITER)), 'baseUrl' => ''));?>" class="edit">&nbsp;</a></h2>
<div class="clearfix">
<?php echo $this->reportTable($this->tables['criteria']);?>
</div>
<?php endif; ?>

<?php if ($this->criteriaTypes[HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO]):?>
<h2><?php echo _('Личностные характеристики');?> <a class="edit edit-programm" href="<?php echo $this->url(array('module' => 'programm', 'controller' => 'evaluation', 'action' => 'edit', 'programm_id' => $this->programm->programm_id, 'vacancy_id' => $this->vacancy->vacancy_id, 'submethod' => implode('_', array(HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO, HM_At_Evaluation_EvaluationModel::RELATION_TYPE_SELF)), 'baseUrl' => ''));?>" class="edit">&nbsp;</a></h2>
<div class="clearfix">
<?php echo $this->reportTable($this->tables['criteria-personal']);?>
</div>
<?php endif; ?>

<?php if (!$this->print):?>
    <div>
        <input type="button" id="button-print" class="hm-report-button-print" value="<?php echo _('Печать')?>">
    </div>
<?php endif;?>
<?php echo $this->partial('_report-custom.tpl', array(
    'print' => $this->print,
    'url' => $this->url(array('module' => 'vacancy', 'controller' => 'report', 'action' => 'index', 'print' => 1)),
)); ?>
</div>
<?php $this->inlineScript()->captureStart();?>
<?php if (!$this->editable):?>
    $('.at-form-report .edit').css('display', 'none');
<?php endif;?>
<?php if (!$this->programmEditable):?>
    $('.at-form-report .edit-programm').css('display', 'none');
<?php endif;?>
<?php $this->inlineScript()->captureEnd();?>
