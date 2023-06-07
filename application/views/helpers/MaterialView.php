<?php
require_once APPLICATION_PATH .  '/views/helpers/material/MaterialAbstract.php';

/*
 * Proxy для всех типов материалов
 * (реально пока только resource и course)
 *
 */
class HM_View_Helper_MaterialView extends HM_View_Helper_Abstract
{
    public function materialView($material, $lesson = null)
    {
        $result = '';
        if(empty($material)) return $result;

        $this->view->setSubHeader($material->getName());
        $helperPath = rtrim(Zend_Registry::get('config')->path->helpers->default, '/') . '/material';
        $this->view->addHelperPath($helperPath, "HM_View_Helper");

        $materialClass = get_class($material);

        switch ($materialClass) {
            case 'HM_Course_CourseModel':
                // видимо, lesson нужен для возврата статистики чрез scorm
                $result = $this->view->materialCourse($material, $lesson);
                break;
            case 'HM_Resource_ResourceModel':
                $result = $this->_getResourceView($material);
                break;
        }

        return $result;
    }

    private function _getResourceView($material)
    {
        $result = '';
        switch ($material->type) {
            case HM_Resource_ResourceModel::TYPE_HTML:
                if ($material->edit_type == HM_Resource_ResourceModel::EDIT_TYPE_SLIDER) {
                    $result = $this->view->materialHtmlSlider($material);
                } else {
                    $result = $this->view->materialHtml($material);
                }
                break;
            /*case HM_Resource_ResourceModel::TYPE_HTML_SLIDER:
                $result = $this->view->materialHtmlSlider($material);
                break;*/
            case HM_Resource_ResourceModel::TYPE_URL:
            case HM_Resource_ResourceModel::TYPE_FILESET:
                $result = $this->view->materialUrl($material);
                break;
            case HM_Resource_ResourceModel::TYPE_CARD:
                // TODO
                $this->view->material = $material;
                $result = $this->view->render("material/card.tpl");
                break;
            case HM_Resource_ResourceModel::TYPE_WEBINAR:
            case HM_Resource_ResourceModel::TYPE_ACTIVITY:
                // не используется?
                break;
            default:
                // файлы разного типа
                $result = $this->_getDefaultResourceView($material);
                break;
        }

        return $result;
    }

    private function _getDefaultResourceView($material)
    {
        $result = $this->view->materialDownload($material);

        if ($material->isViewable()) {
            switch(HM_Files_FilesModel::getFileType($material->filename)) {
                case HM_Files_FilesModel::FILETYPE_TEXT:
                    $result = $this->view->materialText($material);
                    break;
                case HM_Files_FilesModel::FILETYPE_IMAGE:
                    $result = $this->view->materialImage($material);
                    break;
                case HM_Files_FilesModel::FILETYPE_AUDIO:
                    $result = $this->view->materialAudio($material);
                    break;
                case HM_Files_FilesModel::FILETYPE_VIDEO:
                    $result = $this->view->materialVideo($material);
                    break;
                case HM_Files_FilesModel::FILETYPE_FLASH:
                    $result = $this->view->materialFlash($material);
                    break;
                case HM_Files_FilesModel::FILETYPE_PDF:
                    $result = $this->view->materialPdf($material);
                    break;
            }
        }

        return $result;
    }
}