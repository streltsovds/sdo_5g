<?php
include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');

$resources = $services->getService('Course')->fetchAll([
    'status in (?)' => [HM_Course_CourseModel::STATUS_ACTIVE, HM_Course_CourseModel::STATUS_STUDYONLY],
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
<sphinx:attr name="subject_id" type="int" bits="32" default="0"/>

<sphinx:field name="tags" attr="string"/>
</sphinx:schema>';

//собираем метки
$tagsByItemId = $services->getService('Tag')
    ->getItemsIdsWithTags(HM_Tag_Ref_RefModel::TYPE_COURSE);

foreach ($resources as $resource) {
    $tags = '';
    if (isset($tagsByItemId[$resource->resource_id])) {
        $tags = implode(', ', $tagsByItemId[$resource->resource_id]);
    }

    echo '<sphinx:document id="' . ($resource->CID * 10 + HM_Search_Sphinx::TYPE_COURSE) . '">
<title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->Title) . ' ]]></title>
<nId>' . $resource->CID . '</nId>
<description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($resource->Description) . ' ]]></description>
<status>' . HM_Index_Abstract::convertAndFilter($resource->Status) . '</status>
<sphinx_type>' . HM_Search_Sphinx::TYPE_COURSE . '</sphinx_type>
<tags>' . $tags . '</tags>
<subject_id>' . $resource->subject_id . '</subject_id>
</sphinx:document>';

}
echo '</sphinx:docset>';
