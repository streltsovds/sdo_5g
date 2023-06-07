<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');
$encoding = $config->charset;

$subjects = $services->getService('Subject')->fetchAll([
    'status != ?' => HM_Subject_SubjectModel::STATE_CLOSED,
]);

/*
 * Чтобы не раздувать память будем выводить все сразу
 */

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="title" attr="string"/>
<sphinx:field name="short_description" attr="string"/>
<sphinx:field name="description" attr="string"/>
<sphinx:attr name="nId" type="int" bits="32" default="0"/>
<sphinx:attr name="status" type="int" bits="32" default="0"/>
<sphinx:attr name="sphinx_type" type="int" bits="8" default="0"/>

<sphinx:field name="tags" attr="string"/>
<sphinx:field name="resource_title" attr="string"/>
<sphinx:field name="resource_description" attr="string"/>
<sphinx:field name="resource_keywords" attr="string"/>
<sphinx:field name="resource_filename" attr="string"/>
<sphinx:field name="resource_content" attr="string"/>
</sphinx:schema>';

//собираем метки
$tagsByItemId = $services->getService('Tag')
    ->getItemsIdsWithTags(HM_Tag_Ref_RefModel::TYPE_SUBJECT);


//собираем ресурсы
$resourcesFetch = $services->getService('Resource')->fetchAll(['subject_id in (?)' => $subjects->getList('subid')]);
$resources = [];

foreach ($resourcesFetch as $resourceModel) {
    $subjectId = $resourceModel->subject_id;
    $resources[$subjectId][] = $resourceModel;
}


foreach ($subjects as $subject) {
    $tags = '';
    if (isset($tagsByItemId[$subject->subid])) {
        $tags = implode(', ', $tagsByItemId[$subject->subid]);
    }

    $resourceTitle = '';
    $resourceDescription = '';
    $resourceKeywords = '';
    $resourceFilename = '';
    $resourceContent = '';

    if (isset($resources[$subject->subid])) {
        $subjectResources = $resources[$subject->subid];
        foreach ($subjectResources as $subjectResource) {
            $resourceTitle .= $subjectResource->title . ' ';
            $resourceDescription .= $subjectResource->description . ' ';
            $resourceKeywords .= $subjectResource->keywords . ' ';
            $resourceFilename .= $subjectResource->filename . ' ';
            ob_start();
            $services->getService('Resource')->printContent($subjectResource);
            $resourceContent .= ob_get_clean() . ' ';
        }
    }

    echo '<sphinx:document id="' . ($subject->subid * 10 + HM_Search_Sphinx::TYPE_SUBJECT) . '">
    <title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($subject->name) . ' ]]></title>
    <nId>' . $subject->subid . '</nId>
    <description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($subject->description) . ' ]]></description>
    <status>' . HM_Index_Abstract::convertAndFilter($subject->status) . '</status>
    <sphinx_type>' . HM_Search_Sphinx::TYPE_SUBJECT . '</sphinx_type>
   
    
    <tags>' . $tags . '</tags>
    <resource_title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resourceTitle) . ' ]]></resource_title>
    <resource_description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resourceDescription) . ' ]]></resource_description>
    <resource_keywords><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resourceKeywords) . ' ]]></resource_keywords>
    <resource_filename><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resourceFilename) . ' ]]></resource_filename>
    <resource_content><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resourceContent) . ' ]]></resource_content>
    </sphinx:document>';
}
echo '</sphinx:docset>';
