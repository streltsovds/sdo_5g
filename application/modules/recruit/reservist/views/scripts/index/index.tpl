<style>
    tr:nth-child(even) {background: lightgrey}
    tr:nth-child(odd) {background: #F0F8FF}

    td.report-list-key {
        max-width: 400px;
        padding: 10px;
        font-style: italic;
    }

    td.report-list-value {
        min-width: 400px;
        padding: 10px;
    }

    .import-details {
        margin-top: 20px;
        margin-bottom: 20px;
        background-color: lightgrey;
        padding: 10px;
        width: 400px;
    }
</style>
<?php
$downloadLInk = $this->url(
    array(
        'module' => 'reservist',
        'controller' => 'index',
        'action' => 'export-zip',
        'reservist_id' => $this->reservistId,
        'baseUrl' => 'recruit'
    )
);
echo '<br>';
echo $this->reportList($this->history);
echo '<div class="import-details">';
echo '<i><b>Данные об импорте:</b><br>Импортировал: ' . $this->importDetails['who'] . ';<br>Дата импорта: ' . $this->importDetails['when'] . '.</i>';
if ($this->showButton) {
    echo '<br><br><button onclick="javascript: window.location.href = \''.$downloadLInk.'\'">Скачать материалы</button></div>';
} else {
    echo '<br><br></div>';
}
?>

<div>
    <button onclick="javascript: document.location.href='<?php echo $this->url(array('controller' => 'list', 'action' => 'index'))?>'">Назад</button>
</div>
