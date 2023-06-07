<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');
$encoding = $config->charset;

$tasks = $services->getService('Task')->fetchAll([
    'status in (?)' => [HM_Task_TaskModel::STATUS_STUDYONLY, HM_Task_TaskModel::STATUS_PUBLISHED],
]);

/*
 * Чтобы не раздувать память будем выводить все сразу
 */

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="title" attr="string"/>
<sphinx:field name="description" attr="string"/>
<sphinx:attr name="nId" type="int" bits="32" default="0"/>
<sphinx:attr name="status" type="int" bits="32" default="0"/>
<sphinx:attr name="sphinx_type" type="int" bits="8" default="0"/>
<sphinx:attr name="subject_id" attr="int" default="0"/>

<sphinx:field name="tags" attr="string"/>
<sphinx:field name="variants" attr="string"/>
</sphinx:schema>';

//собираем метки
$tagsByItemId = $services->getService('Tag')
    ->getItemsIdsWithTags(HM_Tag_Ref_RefModel::TYPE_TASK);

//варианты
$taskVariantsByItemId = $services->getService('Task')
    ->getTasksIdsWithVariants();

foreach ($tasks as $task) {
    $tags = '';
    if (isset($tagsByItemId[$task->task_id])) {
        $tags = implode(', ', $tagsByItemId[$task->task_id]);
    }

    $variants = '';
    if (isset($taskVariantsByItemId[$task->task_id])) {
        $variants = implode(', ', $taskVariantsByItemId[$task->task_id]);
    }

    echo '<sphinx:document id="' . ($task->task_id * 10 + HM_Search_Sphinx::TYPE_TASK) . '">
    <title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($task->title) . ' ]]></title>
    <nId>' . $task->task_id . '</nId>
    <description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($task->description) . ' ]]></description>
    <status>' . HM_Index_Abstract::convertAndFilter($task->status) . '</status>
    <sphinx_type>' . HM_Search_Sphinx::TYPE_TASK . '</sphinx_type>
    <tags>' . $tags . '</tags>
    <variants><![CDATA[' . HM_Index_Abstract::convertAndFilter($variants) . ']]></variants>
    <subject_id>' . $task->subject_id . '</subject_id>
    </sphinx:document>';

}
echo '</sphinx:docset>';
