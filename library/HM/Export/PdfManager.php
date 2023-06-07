<?php

abstract class HM_Export_PdfManager
{
    protected function sendToPdflib($template)
    {
        $config = Zend_Registry::get('config');

        if (!$config->pdflib->enabled) return false;

        set_time_limit(0);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Zend_Registry::get('view')->serverUrl() . $config->pdflib->path);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['template' => $template]);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    /**
     * @param $inputUrl
     * @param $outputUrl
     * @param $saveDirectory - Сохранить файл на сервере (путь) или отдать в браузер (false)
     * @return bool|string
     * @throws Zend_Exception
     */
    public static function sendToHeadlessChrome($inputUrl, $outputFile, $saveDirectory = false)
    {
        $config = Zend_Registry::get('config');

        if (!$config->headlessChrome->enabled)
            return false;

        set_time_limit(0);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, Zend_Registry::get('view')->serverUrl() . $config->headlessChrome->path);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['input' => $inputUrl, 'output' => $outputFile, 'directory' => $saveDirectory]);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

}