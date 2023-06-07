<?php $editUrl = null;?>
<?php $reportLinks = [
    [
        'title' => 'Нет доступа',
    ],
];?>

<hm-sidebar-session
    title='<?php echo $this->model->name?>'
    edit-url='<?php echo $editUrl?>'
    :report-links='<?= json_encode($reportLinks)?>'
>

</hm-sidebar-session>