<?php $reportLinks = [
    [
        'title' => 'Индивидуальный отчёт',
        'url' => $this->url([
            'module' => 'session',
            'controller' => 'report',
            'action' => 'my',
            'session_id' => $this->model->session_id,
            'redirect' => urlencode($_SERVER['REQUEST_URI']),
        ]),
    ],
    [
        'title' => 'Анализ результатов',
        'url' => $this->url([
            'module' => 'session',
            'controller' => 'report',
            'action' => 'my-analytics',
            'session_id' => $this->model->session_id,
            'redirect' => urlencode($_SERVER['REQUEST_URI']),
        ]),
    ],
];?>

<hm-sidebar-session
    title='<?php echo $this->model->name?>'
    :report-links='<?= json_encode($reportLinks)?>'
/>