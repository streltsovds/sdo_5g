<?php
class HM_Controller_Action_Quest_Crud extends HM_Controller_Action_Crud
{
    protected $service     = 'Quest';
    protected $idParamName = 'quest_id';
    protected $idFieldName = 'quest_id';
    protected $id          = 0;       
    
    protected $_quest;

    public function init()
    {
        $this->questRestrict();

        parent::init();

        $questId = (int) $this->_getParam('quest_id', 0);
        if ($questId) {
            $this->_quest = $this->getOne(
                $this->getService('Quest')->findDependence(array('Cluster'), $questId)
            );
            
            if (!$this->isAjaxRequest()) {
                $subjectId = (int) $this->_getParam('subject_id', 0);
                $this->view->setExtended(
                    array(
                        'subjectName' =>        $subjectId ? 'Subject'   : $this->service,
                        'subjectId' =>          $subjectId ? $subjectId  : $this->_quest->quest_id,
                        'subjectIdParamName' => $subjectId ? 'subject_id': $this->idParamName,
                        'subjectIdFieldName' => $subjectId ? 'subject_id': $this->idFieldName,
                        'subject' =>            $subjectId ? $this->getOne($this->getService('Subject')->find($subjectId)):$this->_quest
                    )
                );   
            }

            // продублировано во всех контроллерах, которые не наследуют от HM_Controller_Action_Quest
            if ($this->_quest && $this->_quest->type != HM_Quest_QuestModel::TYPE_PSYCHO) {
                 $this->view->addContextNavigationModifier(
                     new HM_Navigation_Modifier_Remove_Page('resource', 'cm:quest:page4')
                 );
            }            
        }
    }
}