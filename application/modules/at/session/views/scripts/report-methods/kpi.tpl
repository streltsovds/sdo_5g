<h2><?php echo _('Оценка достижения целей');?></h2>
<?php echo $this->reportText(Zend_Registry::get('serviceContainer')->getService('Option')->getOption('kpiReportComment', $this->session->getOptionsModifier()));?>
<div class="pagebreak"></div>

<div class="clearfix">
<?php echo $this->reportTable(
    $this->tables['kpis'],
    _('Таблица 1. Результаты оценки достижения целей')
);?>
</div>
<?php if (Zend_Registry::get('serviceContainer')->getService('Option')->getOption('kpiUseCriteria', $this->session->getOptionsModifier())): ?>
    <p></p>
    <div class="clearfix">
    <?php echo $this->reportTable(
        $this->tables['kpiCriteria'],
        _('Таблица 2. Оценка способа достижения')
    );?>
    </div>
<?php endif;?>

<?php if ($this->kpiTotal && $this->programm_type != HM_Programm_ProgrammModel::TYPE_ADAPTING): ?>
<h3>Итого ранг результативности: <?php echo $this->kpiTotal?></h3>
<?php endif;?>

<div class="pagebreak"></div>
