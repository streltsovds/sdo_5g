<?php $editUrl = $this->url([
    'module' => 'session',
    'controller' => 'index',
    'action' => 'edit',
    'session_id' => $this->model->session_id,
]);?>
<?php $reportLinks = [
    [
        'title' => 'Общий отчет',
        'url' => $this->url([
            'module' => 'session',
            'controller' => 'report',
            'action' => 'index',
            'session_id' => $this->model->session_id,
            'redirect' => urlencode($_SERVER['REQUEST_URI']),
        ]),
    ],
    [
        'title' => 'Матрица успешности',
        'url' => $this->url([
            'module' => 'session',
            'controller' => 'report',
            'action' => 'matrix-progress',
            'session_id' => $this->model->session_id,
            'redirect' => urlencode($_SERVER['REQUEST_URI']),
        ])
    ],
];?>

<hm-sidebar-session
    title='<?php echo $this->model->name?>'
    edit-url='<?php echo $editUrl?>'
    :report-links='<?= json_encode($reportLinks)?>'
>
    <?php echo $this->workflow($this->model);?>
</hm-sidebar-session>