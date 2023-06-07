<script>
    $( document ).ready(
        function () {
            $( "td.filters_td.grid-department_name" ).css({"z-index": "20"});
        }
    );
</script>
<?php if (!$this->isGridAjaxRequest):?>
    <?php if (Zend_registry::get('serviceContainer')->getService('TcSession')->isApplicable($this->session)):?>
        <?php echo $this->actions('subject-courses', array(
            array(
                'title' => _('Создать обезличенную заявку'),
                'url' => $this->url(array('module' => 'application', 'controller' => 'impersonal', 'action' => 'create', 'session_id' => $this->session->session_id))
            )
        ))?>
    <?php endif;?>
<?php endif;?>

<?php echo $this->grid ?>