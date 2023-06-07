<?php
include "cmdBootstraping.php";
/*
 * @TODO Сделать kill-list
 * 
 */
$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');
$encoding = $config->charset;

//$lastResourceId = intval(file_get_contents('lastIndexedResource'));

$resources = $services->getService('Resource')->fetchAll([
    //'resource_id > ?' => $lastResourceId,
    'parent_id = ?' => 0,
    'status in (?)' => [HM_Resource_ResourceModel::STATUS_PUBLISHED, HM_Resource_ResourceModel::STATUS_STUDYONLY],
]);

/*
 * Чтобы не раздувать память будем выводить все сразу
 */

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="title" attr="string"/>
<sphinx:field name="description" attr="string"/>
<sphinx:field name="keywords" attr="string"/>
<sphinx:field name="filename" attr="string"/>
<sphinx:field name="content" attr="string"/>
<sphinx:attr name="nId" type="int" bits="32" default="0"/>
<sphinx:attr name="created_by" type="int" bits="32" default="0"/>
<sphinx:attr name="subject_id" type="int" bits="32" default="0"/>
<sphinx:attr name="status" type="int" bits="32" default="0"/>
<sphinx:attr name="location" type="int" bits="32" default="0"/>
<sphinx:attr name="sphinx_type" type="int" bits="8" default="0"/>
<sphinx:attr name="created" type="timestamp" bits="32" default="0"/>

<sphinx:field name="tags" attr="string"/>
<sphinx:attr name="classifiers" type="multi"/>
</sphinx:schema>';

/////////////////////////////////
//собираем все классификаторы
$classifiersByItemId = $services->getService('Classifier')
    ->getItemsIdsWithClassifiersIds(HM_Classifier_ClassifierModel::TYPE_RESOURCE);

/////////////////////////////////
//собираем метки
$tagsByItemId = $services->getService('Tag')
    ->getItemsIdsWithTags(HM_Tag_Ref_RefModel::TYPE_RESOURCE);

foreach ($resources as $resource) {

    ob_start();
    $services->getService('Resource')->printContent($resource);
    $content = ob_get_clean();

    /**
     * tags - метки
     * classifiers - классификаторы
     */

    $classifiers = '';
    if (isset($classifiersByItemId[$resource->resource_id])) {
        $classifiers = implode(',', $classifiersByItemId[$resource->resource_id]);
    }

    $tags = '';
    if (isset($tagsByItemId[$resource->resource_id])) {
        $tags = implode(', ', $tagsByItemId[$resource->resource_id]);
    }

    echo '<sphinx:document id="' . ($resource->resource_id * 10 + HM_Search_Sphinx::TYPE_RESOURCE) . '">
<content><![CDATA[ ' . $content . ' ]]></content>
<title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->title) . ' ]]></title>
<description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->description) . ' ]]></description>
<keywords><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->keywords) . ' ]]></keywords>
<filename><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->filename) . ' ]]></filename>
<nId>' . $resource->resource_id . '</nId>
<created_by>' . $resource->created_by . '</created_by>
<subject_id>' . $resource->subject_id . '</subject_id>
<status>' . $resource->status . '</status>
<location>' . $resource->location . '</location>
<sphinx_type>' . HM_Search_Sphinx::TYPE_RESOURCE . '</sphinx_type>
<created>' . strtotime($resource->created) . '</created>

<tags>' . $tags . '</tags>
<classifiers>' . $classifiers . '</classifiers>
</sphinx:document>';
}
echo '</sphinx:docset>';
