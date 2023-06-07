<?php
class HM_View_Helper_ReportColorScale extends HM_View_Helper_Abstract
{
    protected $_session;
    protected $_vacancy;

    protected $_sessionUser;
    protected $_criteriaCache = array();
    protected $_criteriaTestCache = array();
    protected $_criteriaPersonalCache = array();
    protected $_indicatorsCache = array();

    protected $_profile;
    protected $_position;
    protected $_cycle;

    public function reportColorScale($sessionId)
    {
        if (!$sessionId) return $this->view->render('report-color-scale.tpl');

        $session = Zend_Registry::get('serviceContainer')->getService('AtSession')->find($sessionId)->current();
        $sessionUser = Zend_Registry::get('serviceContainer')->getService('AtSessionUser')->fetchAllDependence(array('User', 'Position', 'Session', 'EvaluationResults', 'EvaluationIndicators', 'CriterionValue'), array(
            'session_id = ?' => $session->session_id,
            'user_id = ?' =>  $this->view->user->MID,
        ))->current();

        $this->_position = Zend_Registry::get('serviceContainer')->getService('Orgstructure')->getOne(Zend_Registry::get('serviceContainer')->getService('Orgstructure')->findDependence(array('Parent'), $sessionUser->position_id));
        $this->_profile = Zend_Registry::get('serviceContainer')->getService('AtProfile')->getOne(Zend_Registry::get('serviceContainer')->getService('AtProfile')->findDependence(array('Evaluation', 'CriterionValue'), $this->_position->profile_id));
        $this->_cycle = Zend_Registry::get('serviceContainer')->getService('Cycle')->getOne(Zend_Registry::get('serviceContainer')->getService('Cycle')->find($this->_session->cycle_id));

        // ВАЖНО!!! т.к. мы не прошиваем кластеры и компетенции в базу, мы просто хардкодим связку названий и паттерна в DOCX-шаблоне
        // Если в дальнейшем мы внесем эти данные в дамп, можно будет убрать сопоставление и использовать связку ИД с паттерном шаблона, изменив шаблон,
        // если ИД в базе отличаются от паттернов.
        // Хардкодим названия компетенций для связи их с шаблоном, совпадения ИД  - "случайны"
        $standardCompetencies = array(93=>'Готовность к изменениям', 94=>'Ориентация на достижение результата', 95=>'Ориентация на развитие', 96=>'Лидерство', 97=>'Перспективное мышление',98=>'Управленческая ответственность',99=>'Эффективное администрирование',100=>'Эффективное взаимодействие, влияние');
        // Хардкодим названия кластеров, совпадения ИД  - "случайны"
        $standardClusters = array(6=>'Управленческий профиль', 5=>'Мотивационный профиль');
        $realClusters2standard = array();

        $stdExpertTypes = array(
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS,
            HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN,
        );

        //Получение данных по компетенциям и результатам оценки
        $optionsAt = Zend_Registry::get('serviceContainer')->getService('Option')->getOptions(HM_Option_OptionModel::SCOPE_EVALUATION_METHODS, HM_Option_OptionModel::MODIFIER_AT);
        $competenceData = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->profileResultsByRelationType($sessionUser, $optionsAt+array('position'=>$this->_position));

        if(!$competenceData['results']) {
            return $this->view->render('report-color-scale.tpl');
        }

        $competencesDB = Zend_Registry::get('serviceContainer')->getService('AtCriterion')->fetchAllDependence('CriterionCluster', array('criterion_id IN (?)'=>array_keys($competenceData['results'])));

        //Формирование единого массива с компетенциями и результатами для дальнейшего разбора и вычислений
        $competences = array();
        foreach($competencesDB as $competence) {
            $cluster_id = array_search($competence->cluster->current()->name, $standardClusters);
            $realClusters2standard[$competence->cluster->current()->cluster_id] = $cluster_id;
            $pattern_id = array_search($competence->name, $standardCompetencies);
            if(!$pattern_id) continue;
            $stdEvals = $parentEvals = array();
            foreach($competenceData['results'][$competence->criterion_id]['criterion'] as $role=>$score) {
                if(array_search($role, $stdExpertTypes)!==false) {
                    $stdEvals[$role] = $score;
                }
                if(array_search($role, array(HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT, HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT_FUNCTIONAL))!==false) {
                    $parentEvals[$role] = $score;
                }
            }
            $competences[$pattern_id] = array('raw'=>$competence, 'evals'=>$stdEvals, 'parentEvals'=>$parentEvals, 'cluster_pattern'=>$cluster_id);
        }

        //ТАБЛИЦА
        $data = array();
        foreach($competences as $competence_pattern=>$competence) {
            $evals = $competence['evals'];
            $avg = $this->avg($evals);
            $data1[$competence_pattern] = 0;

            //Сильные стороны (S)
            if($avg>=3) {
                $data1[$competence_pattern] += round($avg, 1);
            }
            //Зоны развития (Z)
            if($avg<3) {
                $data1[$competence_pattern] += round($avg, 1);
            }
            //Разногласия (R)
            $eParent = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_PARENT];
            $eChildren = $evals[HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN];
            $diffR = abs($eParent-$eChildren);
            if(!($this->isEmptyValue($eParent) || $this->isEmptyValue($eChildren) || $diffR<1.5)) {
                $data1[$competence_pattern] -= $eChildren;
                $data1[$competence_pattern] -= $eParent;
            }
            $color = $this->getOptions($data1[$competence_pattern]);
            $data[$standardClusters[$competence['cluster_pattern']]][] = array(
                'name' => $standardCompetencies[$competence_pattern],
                'color' => $color['fill']
            );
        }

        $this->view->data = $data;
        return $this->view->render('report-color-scale.tpl');
    }

    private function avg($array) {
        $count = $summ = 0;
        foreach($array as $value) {
            if($this->isEmptyValue($value) || intval($value)<0) continue;
            $summ += $value;
            $count++;
        }
        return $summ/$count;
    }

    private function isEmptyValue($value) {
        if(is_array($value)) {
            foreach($value as $v) {
                if(!$this->isEmptyValue($v)) return false;
            }
            return true;
        }
        return !$value && !($value==='0' || $value===0);
    }

    private function getOptions($avg) {
        if($this->isEmptyValue($avg)) return null;//пустое значение - не красим ячейку
        return array('fill'=> $avg>=3.5 ?  '538135' : ($avg>=3 ? '92D050' : ($avg>=2 ? 'FFFF00' : 'FF0000')));
    }
}