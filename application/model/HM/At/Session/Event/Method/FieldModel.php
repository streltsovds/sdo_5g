<?php
/**
 * Старый код из pm
 * Анкета field-coaching
 */
class HM_At_Session_Event_Method_FieldModel extends HM_At_Session_Event_EventModel implements HM_At_Session_Event_Method_Interface // @todo
{
    const MEMO1 = '1';
    const MEMO2 = '2';
    const MEMO3 = '3';

    const SCALE_VALUE_NEGATIVE = '0'; // должны быть в db_dump2!!!
    const SCALE_VALUE_POSITIVE = '1';

    public $type = HM_At_Evaluation_EvaluationModel::TYPE_FIELD;

    public $criteria = array(); // array of criteria (from evaluation type)
    public $forest = array(); // array of criteria trees
    public $selectedCriteria = array();

    public function init()
    {
        $this->evaluation = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->findManyToMany('Criterion', 'EvaluationCriterion', $this->evaluation_id)->current();

        $selectedCriteria = $customCriteria = array();
        if (count($this->evaluationResults)) {
            foreach ($this->evaluationResults as $result) {
                if (!empty($result->criterion_id)) {
                    $selectedCriteria[$result->criterion_id] = $result->value_id;
                } else {
                    $customCriteria[$result->custom_criterion_parent_id][$result->custom_criterion_name] = $result->value_id;
                }
            }
            $selectedCriteriaCollection = count($selectedCriteria) ? Zend_Registry::get('serviceContainer')->getService('AtCriterion')->fetchAll(array('criterion_id IN (?)' => new Zend_Db_Expr(implode(',', array_keys($selectedCriteria))))) : array();
        }

        foreach ($this->evaluation->criteria as $criterion) {

        	$this->forest[$criterion->criterion_id] = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->getTreeContent($criterion->criterion_id, 0, array_keys($selectedCriteria));

        	$this->selectedCriteria[$criterion->criterion_id] = array();
            if (count($selectedCriteriaCollection)) {
                foreach ($selectedCriteriaCollection as $selectedCriterion) {
                    if (in_array($selectedCriterion->criterion_id, array_keys($criterion->getPlainContent()))) {
                    	$this->selectedCriteria[$criterion->criterion_id][] = array(
                    	   'title' => $selectedCriterion->name,
                    	   'key' => $selectedCriterion->criterion_id,
                    	   'value' => $this->scale->getValueValue($selectedCriteria[$selectedCriterion->criterion_id]),
                           'isCustom' => false,   
                    	);
                    }
                }
            }
            if (is_array($customCriteria[$criterion->criterion_id])) {
                foreach ($customCriteria[$criterion->criterion_id] as $name => $value) {
                    $this->selectedCriteria[$criterion->criterion_id][] = array(
                            'title' => $name,
                            'key'   => 0, // ?
                            'value' => $this->scale->getValueValue($value),
                            'isCustom' => true,
                    );
                }
            }
        	$this->criteria[$criterion->criterion_id] = $criterion;
        }
    }

    static public function getMemos()
    {
        return array(
            self::MEMO1 => _('Наиболее эффективные навыки, знания и компетенции'),
            self::MEMO2 => _('Навыки, знания и компетенции, требующие развития'),
            self::MEMO3 => _('Цели и мероприятия для самостоятельного развития'),
        );
    }

    public function isValid() {
        if (count($this->scale->scaleValues) != 2) { // yes|no
            return _('Неверно задана шкала оценки');
        }
        return true;
    }

    public function getMethodValue($value)
    {
        return (isset($value['value'])) ? $value['value'] : $value; // whether custom or not
    }

    public function getWeight($value)
    {
        return 1;
    }

    public function savesubmethod($values)
    {
        return true;
    }
}