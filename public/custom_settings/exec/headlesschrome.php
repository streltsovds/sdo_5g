<?php
define('HARDCODE_WITHOUT_SESSION', true);
require "../../../application/cmd/cmdBootstraping.php";

$config = Zend_Registry::get('config');
$serviceContainer = Zend_Registry::get('serviceContainer');

$warningMmessage = _("Что-то пошло не так... Обратитесь в тех.поддержку.");

if (!$config->headlessChrome->enabled)
    die($warningMmessage);

$url = $_POST['input'];
$fileName = $_POST['output'];
$directory = $_POST['directory'];

if (!is_dir($directory)) {
    $old_umask = umask(0);
    mkdir($directory, 0777, true);
    chmod($directory, 0777);
    umask($old_umask);
}

$view = Zend_Registry::get('view');

// Хром не хочет воспринимать пути с \..\, поэтому realpath
$file = realpath($directory) . DIRECTORY_SEPARATOR . $fileName;
$url = $view->serverUrl() . $url;

_runCommand($url, $file, $config);

// Иногда скачивается пустой файл размером ~1кб
// Поэтому здесь и в методе выше проверка filesize
if (file_exists($file) && (filesize($file) / 1000 > 2)) {

    if (!$directory) {
        header('Content-type: application/pdf');
        header("Content-Disposition: attachment; filename=$fileName");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Pragma: public");
        header("Content-Transfer-Encoding: binary");
        $content = file_get_contents($file);
        unlink($file);
        die($content);
    } else {
        // Положили и оставили
    }
} else {
    die($warningMmessage);
}

function _runCommand($url, $file, $config)
{
    $timeout = 15;
    $timeoutAdd = 5;
    $attempts = 5;

    $i = 0;
    do {
        // Удаление некорректного файла
        if (file_exists($file) && (filesize($file) / 1000 < 2)) {
            unlink($file);
        }

        // Время в мсек для --virtual-time-budget.
        // Если --virtual-time-budget не указать, JS не успевает выполниться и диаграмма будет неполная
        $chromeTimeout = 40000;
        // $chromeTimeout = $timeout * 2 * 1000; TODO: может, придумать формулу для расчета на основе $timeout с увеличением времени?
        if ($config->headlessChrome->linux) {
            // --disable-gpu для Linux потому что в тестах вылетала ошибка "ContextResult::kTransientFailure: Failed to send GpuControl.CreateCommandBuffer."
            $cmd = "google-chrome --headless --disable-gpu --print-to-pdf-no-header $url --run-all-compositor-stages-before-draw --print-to-pdf=\"$file\" --virtual-time-budget=$chromeTimeout";
        } else {
            // Windows
            $cmd = "start chrome --headless --no-sandbox --disable-gpu --print-to-pdf-no-header $url --run-all-compositor-stages-before-draw --print-to-pdf=\"$file\" --virtual-time-budget=$chromeTimeout";
        }
        system($cmd, $result);
        sleep($timeout);
        $timeout += $timeoutAdd;
    } while ((filesize($file) / 1000 < 2) && (++$i < $attempts));
}
