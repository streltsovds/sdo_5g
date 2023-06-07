<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 21.04.2015
 * Time: 19:35
 */
abstract class HM_Export_Meeting_PdfManager extends HM_Export_PdfManager{

    protected $_templateFiles = array(
        'main' => '',
    );
    protected $_meetings = null;

    protected $_errorMessage = "При экспорте произошли ошибки";

    public function __construct($meetingIds)
    {
        $this->_meetings = $this->filterMeetings($meetingIds);
        if (!count($this->_meetings)) {
            throw new HM_Exception(_($this->getErrorMessage()));
        }
        $this->_templateFiles = array(
            'main' => realpath(APPLICATION_PATH.'/../data/templates/export/meeting/main.html'),
        );
    }

    /**
     * @param $name
     * @return HM_Service_Abstract
     * @throws Zend_Exception
     */

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getExportPdf($variantsCount = 1, $withAnswer = false)
    {
        $mainTemplate = file_get_contents($this->_templateFiles['main']);
        $mainTemplate = str_replace('{{DOMAIN}}', "{$_SERVER["HTTP_HOST"]}", $mainTemplate);
        $meetingsTemplate = $this->getMeetingsHtml($variantsCount, $withAnswer);
        $mainTemplate = str_replace('{{LESSONS}}', $meetingsTemplate, $mainTemplate);

        return $this->sendToPdflib($mainTemplate);
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * @param $meetingIds
     * @return HM_Collection
     */
    abstract protected function filterMeetings($meetingIds);

    abstract protected function getMeetingsHtml($variantsCount, $withAnswer);

}