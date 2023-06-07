<?php

/**
 *
 */
class HM_DataGrid_Column_Callback_LessonsCache extends HM_DataGrid_Column_Callback_Abstract
{
    private $lessonsCache;
    
    public function callback(...$args)
    {
        list($dataGrid, $lessonIds) = func_get_args();

        $serviceContainer = $dataGrid->getServiceContainer();

        if (!$this->lessonsCache) {
            $this->lessonsCache = [];
            $smtp = $dataGrid->getSelect()->query();
            $res = $smtp->fetchAll();
            $tmp = [];
            foreach ($res as $val) $tmp[] = $val['lessons'];
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $tmp = array_filter($tmp);
            if (count($tmp)) {
                $this->lessonsCache = $serviceContainer->getService('Lesson')->fetchAll(array('SHEID IN (?)' => $tmp), 'title');
            }
        }

        $lessonsIds = array_filter(array_unique(explode(',', $lessonIds)));

        $result =  array();
        if (is_a($this->lessonsCache, 'HM_Collection')) {
            if (!empty($lessonIds)) {
                foreach ($lessonsIds as $lesson)
                    if ($tempModel = $this->lessonsCache->exists('SHEID', $lesson))
                        $result[] = "<p>{$tempModel->title}</p>";

//                $collection = $lessonService->fetchAll(['SHEID IN (?)' => $lessonIds], 'title');
//                foreach ($collection as $lesson) {
//                    $result[] = "<p>" . sprintf('<a href="%s">%s</a> ', $this->getView()->url([
//                            'module' => 'subject',
//                            'controller' => 'lesson',
//                            'action' => 'index',
//                            'lesson_id' => $lesson->SHEID,
//                        ]), $lesson->title) . "</p>";
//                }
            }
        }

        if ($result) {
            if (count($result) > 1) {
                array_unshift($result, '<p class="total">' . $serviceContainer->getService('Lesson')->pluralFormCount(count($result)) . '</p>');
            }
            return implode('', $result);
        } else {
            return _('Нет');
        }
    }
}