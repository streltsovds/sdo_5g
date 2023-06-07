<?php
/**
 * Created by PhpStorm.
 * User: sitnikov
 * Date: 21.04.2015
 * Time: 19:35
 */
abstract class HM_Export_Lesson_PdfManager extends HM_Export_PdfManager{

    protected $_templateFiles = array(
        'main' => '',
    );
    protected $_lessons = null;

    protected $_errorMessage = "При экспорте произошли ошибки";

    public function __construct($lessonIds)
    {
        $this->_lessons = $this->filterLessons($lessonIds);
        if (!count($this->_lessons)) {
            throw new HM_Exception(_($this->getErrorMessage()));
        }
        $this->_templateFiles = array(
            'main' => realpath(APPLICATION_PATH.'/../data/templates/export/lesson/main.html'),
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
        $lessonsTemplate = $this->getLessonsHtml($variantsCount, $withAnswer);
        $mainTemplate = str_replace('{{LESSONS}}', $lessonsTemplate, $mainTemplate);

        return $this->sendToPdflib($mainTemplate);
    }

    public function getErrorMessage() {
        return $this->_errorMessage;
    }

    /**
     * @param $lessonIds
     * @return HM_Collection
     */
    abstract protected function filterLessons($lessonIds);

    abstract protected function getLessonsHtml($variantsCount, $withAnswer);

}