<?php
require_once APPLICATION_PATH .  '/views/helpers/material/preview/MaterialAbstract.php';

class HM_View_Helper_MaterialPreview extends HM_View_Helper_Abstract
{
    const MODE_DEFAULT = 'default';
    const MODE_EDITABLE = 'editable';

    public function materialPreview($material, $lesson = null, $mode = self::MODE_DEFAULT)
    {
        if (!(
            is_a($material, 'HM_Course_CourseModel') ||
            is_a($material, 'HM_Resource_ResourceModel') ||
            is_a($material, 'HM_Subject_SubjectModel')
        )) return '';

        $isStatsAllowed =
            Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), HM_Role_Abstract_RoleModel::ROLE_ENDUSER) &&
            (is_a($material, 'HM_Course_CourseModel') &&
            in_array($material->format, HM_Course_CourseModel::getInteractiveFormats()) ||
            is_a($material, 'HM_Subject_SubjectModel'));

        $lessonAttribs = null;
        $statsUrl = null;
        $editUrl = null;
        $deleteUrl = null;
        $actions = [];

        if ($lesson) {
            if ($isStatsAllowed) {
                $statsUrl = $this->view->url(array(
                    'action' => 'listlecture',
                    'controller' => 'result',
                    'module' => 'lesson',
                    'lesson_id' => $lesson->SHEID,
                    'subject_id' => $lesson->CID,
                    'userdetail' => 'yes1',
                    'switcher' => 'listlecture',
                ), false, true);
            }

            if (0) { // @todo
                $editUrl = $this->view->url(array(
                    'module' => 'subject',
                    'controller' => 'material',
                    'action' => 'edit',
                    'lesson_id' => $lesson->SHEID,
                    'subject_id' => $lesson->CID
                ), false, true);
            }

            $deleteUrl = $this->view->url(array(
                'module' => 'subject',
                'controller' => 'material',
                'action' => 'delete',
                'lesson_id' => $lesson->SHEID,
                'subject_id' => $lesson->CID
            ), false, true);

            if ($mode == HM_View_Helper_MaterialPreview::MODE_EDITABLE) {
                $actions = [
                    'edit' => $editUrl,
                    'delete' => $deleteUrl
                ];
            }

            $lessonAttribs = array('href' => $this->view->url(array(
                'module' => 'lesson',
                'controller' => 'execute',
                'action' => 'index',
                'lesson_id' => $lesson->SHEID,
                'subject_id' => $lesson->CID
            ), false, true));

            if (is_a($material, 'HM_Course_CourseModel')) {
                $lessonAttribs['target'] = $lesson->isNewWindow();
            }
        }

        $this->view->addHelperPath(Zend_Registry::get('config')->path->helpers->default . 'material/preview', "HM_View_Helper");
        $helperName = $this->getHelperNameForMaterial($material);

        $materialUrl = $this->view->url(array(
            'module' => 'resource',
            'controller' => 'index',
            'action' => 'index',
            'resource_id' => $material->resource_id
        ));

        $options = [
            'mode'   => $mode,
            'lesson' => $lesson,
            'title' => $lesson ? $lesson->title : $material->title,
            'description' => $lesson ? $lesson->descript : $material->description,
            'material' => $material,
            'url' => $lessonAttribs ? $lessonAttribs['href'] : $materialUrl,
            'statsUrl' => $statsUrl,
            'actions' => $actions,
            'helperName' => $helperName,
            'rating' => $material->rating,
            'classifiers' => $material->classifiers,
            'tags' => $material->tag
        ];

        if (is_a($material, 'HM_Subject_SubjectModel')) {
            $options['title'] = $material->name;
            $options['url']   = $this->view->url([
                'module' => 'subject',
                'controller' => 'index',
                'action' => 'description',
                'subject_id' => $material->subid
            ]);
        }

        if (is_a($material, 'HM_Resource_ResourceModel')) {
            $options['title'] = $material->title;
        }

        $this->view->assign($options);

        return $this->view->render('material-preview.tpl');
    }

    public function getHelperNameForMaterial($material)
    {
        $class = get_class($material);
        if (in_array($class, ['HM_Course_CourseModel', 'HM_Subject_SubjectModel'])) {
            return "materialCourse";
//            return $this->view->materialCourse($material, $lesson);

        } elseif ($class == 'HM_Resource_ResourceModel') {
            switch ($material->type) {
                case HM_Resource_ResourceModel::TYPE_HTML:
                    return "materialHtml";
//                    return $this->view->materialHtml($material);
                    break;
                case HM_Resource_ResourceModel::TYPE_URL:
                case HM_Resource_ResourceModel::TYPE_FILESET:
                    return "materialUrl";
//                    return $this->view->materialUrl($material);
                    break;

                case HM_Resource_ResourceModel::TYPE_CARD:
                case HM_Resource_ResourceModel::TYPE_WEBINAR:
                case HM_Resource_ResourceModel::TYPE_ACTIVITY:
                    // не используется?
                    return false;
                    break;

                default:
                    // файлы разного типа
                    if ($material->isViewable()){
                        switch(HM_Files_FilesModel::getFileType($material->filename)) {
                            case HM_Files_FilesModel::FILETYPE_TEXT:
                                return "materialText";
//                                return $this->view->materialText($material);
                                break;
                            case HM_Files_FilesModel::FILETYPE_IMAGE:
                                return "materialImage";
                                return $this->view->materialImage($material);
                                break;
                            case HM_Files_FilesModel::FILETYPE_AUDIO:
                                return "materialAudio";
//                                return $this->view->materialAudio($material);
                                break;
                            case HM_Files_FilesModel::FILETYPE_VIDEO:
                                return "materialVideo";
//                                return $this->view->materialVideo($material);
                                break;
                            case HM_Files_FilesModel::FILETYPE_FLASH:
                                return "materialFlash";
//                                return $this->view->materialFlash($material);
                                break;
                            case HM_Files_FilesModel::FILETYPE_PDF:
                                return "materialPdf";
//                                return $this->view->materialPdf($material);
                                break;
                        }
                    } else {
                        return "materialDownload";
//                        return $this->view->materialDownload($material);
                    }
                    break;
            }
        }
        return false;
    }
}