<?php
include "cmdBootstraping.php";
$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');
$encoding = $config->charset;
$polls = $services->getService('Quest')->fetchAll([
    'status in (?)' => [HM_Quest_QuestModel::STATUS_RESTRICTED],
    'type = ?' => HM_Quest_QuestModel::TYPE_POLL,
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
<sphinx:field name="questions" attr="string"/>
<sphinx:field name="variants" attr="string"/>
</sphinx:schema>';

//собираем метки
$tagsByItemId = $services->getService('Tag')
    ->getItemsIdsWithTags(HM_Tag_Ref_RefModel::TYPE_POLL);

//собираем вопросы
$questionsByItemId = $services->getService('Quest')
    ->getQuestIdsWithQuestions(HM_Quest_QuestModel::TYPE_POLL);

//собираем варианты
$variantsByItemId = $services->getService('Quest')
    ->getQuestIdsWithVariants(HM_Quest_QuestModel::TYPE_POLL);

foreach ($polls as $poll) {
    $tags = '';
    if (isset($tagsByItemId[$poll->quest_id])) {
        $tags = implode(', ', $tagsByItemId[$poll->quest_id]);
    }

    $questions = '';
    if (isset($questionsByItemId[$poll->quest_id])) {
        $questions = implode(', ', $questionsByItemId[$poll->quest_id]);
    }

    $variants = '';
    if (isset($variantsByItemId[$poll->quest_id])) {
        $variants = implode(', ', $variantsByItemId[$poll->quest_id]);
    }

    echo '<sphinx:document id="' . ($poll->quest_id * 10 + HM_Search_Sphinx::TYPE_POLL) . '">
    <title><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($poll->name) . ' ]]></title>
    <nId>' . $poll->quest_id . '</nId>
    <description><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($poll->description) . ' ]]></description>
    <status>' . HM_Index_Abstract::convertAndFilter($poll->status) . '</status>
    <sphinx_type>' . HM_Search_Sphinx::TYPE_POLL . '</sphinx_type>
    <subject_id>' . $poll->subject_id . '</subject_id>
    
    <tags>' . $tags . '</tags>
    <questions><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($questions) . ' ]]></questions>
    <variants><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($variants) . ' ]]></variants>
    </sphinx:document>';
}
echo '</sphinx:docset>';
