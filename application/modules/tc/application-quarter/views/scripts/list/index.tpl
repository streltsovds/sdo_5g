<script>
    $( document ).ready(
        function () {
            $( "td.filters_td.grid-department_name" ).css({"z-index": "20"});
        }
    );
</script>
<?php if ($this->cost && !$this->isGridAjaxRequest){ ?>
<div class="at-form-report at-form-report-grid-summary">
    <?php $this->headLink()->appendStylesheet($this->serverUrl('/css/forms/at-forms.css'), 'screen,print');?>
    <div class="report-summary clearfix">
        <div class="left-block">
            <?php echo $this->reportList($this->cost); ?>
        </div>
    </div>
</div>
<?php } ?>

<?php if (!$this->isGridAjaxRequest):?>
    <?php if (Zend_registry::get('serviceContainer')->getService('TcSessionQuarter')->isApplicable($this->session)):?>
        <?php echo $this->actions('subject-courses', array(
            array(
                'title' => _('Создать заявку на обучение'),
                'url' => $this->url(array('module' => 'application-quarter', 'controller' => 'list', 'action' => 'create', 'session_quarter_id' => $this->session->session_quarter_id))
            )
        ))?>
    <?php endif;?>
<?php endif;?>

<?php echo $this->grid ?>