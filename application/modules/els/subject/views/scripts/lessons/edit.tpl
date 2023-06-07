<?php
// @todo: autoload it
require_once APPLICATION_PATH .  '/views/helpers/Score.php';

/** @var HM_Lesson_LessonModel_Interface $lesson */
?>
<?php echo $this->actions(); ?>

<hm-lessons :subject-id="view.subjectId" :lessons='view.lessons' :sections='view.sections' hash='<?php echo $this->folderHash ?>'></hm-lessons>


<?php //echo $this->vueServerFile('extraMaterials', array(
//    'connectorUrl' => $this->url(array(
//        'module' => 'storage',
//        'controller' => 'index',
//        'action' => 'elfinder',
//        'subject' => HM_Storage_StorageModel::CONTEXT_SUBJECT_EXTRA_MATERIALS,
//        'subject_id' => $this->subjectId,
//    )),
//    'isModal' => false,
//    'lang' => Zend_Registry::get('config')->wysiwyg->params->language,
//)); 
?>