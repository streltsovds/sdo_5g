<?php
// Если кто-то будет это дебажить в PhpStorm и получать постоянный timeout от curl
// Отключите xdebug в шторме, он блокирует множественные запросы
define('HARDCODE_WITHOUT_SESSION', true);

require "../../../application/cmd/cmdBootstraping.php";
require("../../../library/dompdf/dompdf_config.inc.php");

$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');

$template = $_POST['template'];

if (!$template) return false;

// TODO проверку на необходимость аутентификации на хосте
$template = str_replace("http://{$_SERVER["HTTP_HOST"]}", APPLICATION_PATH . '/../public', $template);

$dompdf = new DOMPDF();
$dompdf->load_html($template);
$dompdf->set_paper("a4");

/** @see http://projects.hypermethod.com:8080/redmine/issues/38957#note-17 */
$adapter = strtolower(get_class($services->getService('User')->getSelect()->getAdapter()));
$adapter = str_replace("zend_db_adapter_", "", $adapter);
$adapter = str_replace("hm_db_adapter_", "", $adapter);
$isMsSql = in_array($adapter, ['mssql', 'sqlsrv', 'pdo_mssql']);

$dompdf->set_option('enable_html5_parser', $isMsSql);
try {
    $dompdf->render();
} catch (Exception $e) {

    $dompdf->set_option('enable_html5_parser', !$isMsSql);
    try {
        $dompdf->render();
    } catch (Exception $e) {
    }
}

$output = $dompdf->output();
echo $output;
