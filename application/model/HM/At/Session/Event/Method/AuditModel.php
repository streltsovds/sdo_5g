<?php
/**
 * Старый код из pm
 * Анкета аудита
 */
class HM_At_Session_Event_Method_AuditModel extends HM_At_Session_Event_EventModel implements HM_At_Session_Event_Method_Interface // @todo
{
    const MEMO1 = '1';
    const MEMO2 = '2';
    const MEMO3_EXTRA_CRITERIA = '3';

    const VALUE_WEIGHT_DEFAULT = 10;

    public $type = HM_At_Evaluation_EvaluationModel::TYPE_AUDIT;

    public $criteria = array(); // array of criteria
    public $selectedCriteria = array();

    public function init()
    {
        $this->evaluation = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->findManyToMany('Criterion', 'EvaluationCriterion', $this->evaluation_id)->current();

        $customCriteria = array();
        if (count($this->evaluationResults)) {
            foreach ($this->evaluationResults as $result) {
                $key = $result->criterion_id ? $result->criterion_id : 'custom' . $result->result_id;
                $this->selectedCriteria[$key] = array(
                    'applicable'    => $result->value_weight,
                    'achieved'      => floor($result->value_weight * $result->value_id / 100),
                    'ratio'         => $result->value_id,
                    'isCustom'      => empty($result->criterion_id),
                );
                if (empty($result->criterion_id)) {
                    $customCriterion = new stdClass();
                    $customCriterion->custom_id = 'custom' . $result->result_id;
                    $customCriterion->name = $result->custom_criterion_name;
                    $customCriteria[] = $customCriterion;
                }
            }
        }

        foreach ($this->evaluation->criteria as $criterion) {
        	$this->criteria = array_merge($this->criteria, $criterion->getPlainContent());
        }
        $this->criteria = array_merge($this->criteria, $customCriteria);
    }

    static public function getMemos()
    {
        return array(
            self::MEMO1 => _('Основные сильные стороны ТЕ'),
            self::MEMO2 => _('Навыки и компетенции, требующие развития'),
        );
    }

    public function isValid()
    {
        if (count($this->scale->type) != HM_At_Scale_ScaleModel::TYPE_CONTINUOUS) { // user input
            return _('Неверно задана шкала оценки');
        }
        return true;
    }

    public function getMethodValue($value)
    {
        return floor(100*$value['achieved']/$value['applicable']);
    }

    public function getWeight($value)
    {
        return $value['applicable'];
    }


    public function savesubmethod($values)
    {
        $data = array();
        // save extra criteria
        if (is_array($values['extra_criteria']))
            foreach ($values['extra_criteria'] as $key => $value) {
                $data['extraCriteria'][] = array(
                    'criterion_id'  => $key,
                    'session_event_id'  => $this->session_event_id,
                    'value_id'  => $this->getMethodValue($value),
                    'value_weight'  => (($weight = $this->getWeight($value)) !== false) ? $weight : null,
                );
            }
        // save route specific data
        $data['dateRoute'] = $values['dateRoute'];
        $data['valueTotal'] = $values['valueTotal'];

        // required
        parent::_savesubmethod($data);
        return true;
    }
}