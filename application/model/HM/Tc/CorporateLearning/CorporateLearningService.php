<?php
class HM_Tc_CorporateLearning_CorporateLearningService extends HM_Service_Abstract
{

    
    public function getCorporateSource()
    {
        $select = $this->getSelect();

        $subSelectCity = clone $select;
        $subSelectCity->from(
            array('cl' => 'classifiers_links'),
            array(
                'cl.classifier_id',
                'cl.item_id',
                'cl.type',
                'c.name'
            ))
            ->joinInner(
                array('c' => 'classifiers'),
                $this->getService('Classifier')->quoteInto(
                    'c.classifier_id = cl.classifier_id AND c.type = ?',
                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES
                ),
            array()
        );

/*
        $subSelectFunctionalDirection = clone $select;
        $subSelectFunctionalDirection->from(array('cl' => 'classifiers_links'), array('c.name'));
        $subSelectFunctionalDirection->joinInner(
            array('c' => 'classifiers'),
            $this->getService('Classifier')->quoteInto(
                'c.classifier_id = cl.classifier_id AND c.type = ?',
                HM_Classifier_Type_TypeModel::BUILTIN_TYPE_FUNC_DIRECTION
            ),
            array()
        );
        $subSelectFunctionalDirection->where(
            'cl.item_id = tccp.corporate_learning_id AND cl.type = ?',
            HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING
        );
*/

        $select->from(array('tccp' => 'tc_corporate_learning'), array(
            'corporate_learning_id' => 'tccp.corporate_learning_id',
            'corporate_name'        => 'tccp.name',
            'manager_name'          => 'tccp.manager_name',
            'people_count'          => 'tccp.people_count',
            'meeting_type'          => 'tccp.meeting_type',
            'month'                 => 'tccp.month',
            'city'                  => 'cl.name',
            'sum'                   => new Zend_Db_Expr('SUM(p.cost)'),
            'participants'          => 'tccp.corporate_learning_id',//new Zend_Db_Expr('GROUP_CONCAT(p.participant_id)'),
            'cost_for_organizer'    => 'tccp.cost_for_organizer',
            'organizer'             => 'so.name',
//            'functional_direction'  => new Zend_Db_Expr('('.$subSelectFunctionalDirection.')'),
        ))
            ->joinLeft(
                array('so' => 'structure_of_organ'),
                'so.soid = tccp.organizer_id',
                array())
            ->joinLeft(
                array('p' => 'tc_corporate_learning_participant'),
                'tccp.corporate_learning_id = p.corporate_learning_id',
                array())
            ->joinLeft(
                array('cl' => $subSelectCity),
                $this->quoteInto(
                    'cl.item_id = tccp.corporate_learning_id AND cl.type = ?',
                    HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING
                ),
                array())

            ->group(array(
                'tccp.corporate_learning_id', 'tccp.name', 'tccp.manager_name',
                'tccp.people_count', 'tccp.meeting_type', 'tccp.month',
                'tccp.cost_for_organizer','tccp.organizer_id',
                'so.soid','so.name','cl.name',
            ))
        ;
        
//        Zend_Registry::get('log_system')->debug(var_export($select->__toString(), true));
        return $select;
    }
    
    public function setClassifiers($classifiers, $corporateLearningId)
    {
        $res = $this->getService('ClassifierLink')->deleteBy($this->quoteInto(
            array('item_id = ? ', ' AND type = ?'),
            array($corporateLearningId, HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING)
        ));

        if (count($classifiers) && $corporateLearningId) {

            foreach ($classifiers as $classifier) {
                $data = array(
                    'item_id' => $corporateLearningId,
                    'classifier_id' => (int)$classifier,
                    'type' => HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING
                );
                $this->getService('ClassifierLink')->insert($data);
            }
        }
    }
    
    public function getCities($corporateLearningId) {
        return $this->getService('Classifier')->fetchAllDependenceJoinInner('ClassifierLink',
            $this->quoteInto(
                array(
                    'ClassifierLink.type = ? ',
                    ' AND ClassifierLink.item_id = ?',
                    ' AND self.type = ?'
                ),
                array(
                    HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING,
                    $corporateLearningId,
                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_CITIES
                )
            )
        );
    }
    
    public function getFunctionalDirection($corporateLearningId) {
        return $this->getService('Classifier')->fetchAllDependenceJoinInner('ClassifierLink',
            $this->quoteInto(
                array(
                    'ClassifierLink.type = ? ',
                    ' AND ClassifierLink.item_id = ?',
                    ' AND self.type = ?'
                ),
                array(
                    HM_Classifier_Link_LinkModel::TYPE_TC_CORPORATE_LEARNING,
                    $corporateLearningId,
                    HM_Classifier_Type_TypeModel::BUILTIN_TYPE_FUNC_DIRECTION
                )
            )
        );
    }
    
    public function delete($id) {
        $service = $this->getService('TcCorporateLearningParticipant');
        $service->deleteBy(array('corporate_learning_id = ?' => $id));
        parent::delete($id);
    }
    
}