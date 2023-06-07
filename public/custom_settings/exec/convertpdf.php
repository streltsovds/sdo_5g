<?php
define('HARDCODE_WITHOUT_SESSION', true);
require "../../../application/cmd/cmdBootstraping.php";

if (empty($_POST) || empty($_POST['sourcePath']) || empty($_POST['targetPath'])) return false;

$sourcePath = $_POST['sourcePath'];
$targetPath = $_POST['targetPath'];

if (!is_file($sourcePath) || !is_readable($sourcePath)) return false;

// Проверка, что файл-источник находится в директории проекта
$projectDir = realpath(dirname(__FILE__) . '/../../../');
if (strpos($sourcePath, $projectDir) !== 0) return false;

$converterPath = Zend_Registry::get('config')->src->officeConverter;

$params = [
    '--headless',
    '--convert-to pdf',
    '--outdir ' . escapeshellarg($targetPath),
    escapeshellarg($sourcePath)
];

$command = escapeshellarg($converterPath) . ' ' . implode(' ', $params);

//if (is_file($converterPath)) {
    $str = exec($command);
    exit(sprintf('%s => %s', $command, $str));
//}
