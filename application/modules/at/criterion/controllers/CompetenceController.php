<?php
class Criterion_CompetenceController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid;

    protected $profilesCache = array();

    public function init()
    {
        $form = new HM_Form_Competence();
        $this->_setForm($form);
        parent::init();
    }

    public function indexAction()
    {
        $sorting = $this->_request->getParam("ordergrid");
        if ($sorting == ""){
            $this->_request->setParam("ordergrid", $sorting = 'name_ASC');
        }

        $isAtCompetenceRemoved = (bool)$this->getService('Extension')->getRemover("HM_Extension_Remover_AtCompetenceRemover");

        $select = $this->getService('AtCriterion')->getSelect();

        $select->from(
            array(
                'c' => 'at_criteria'
            ),
            array(
                'c.criterion_id',
                'c.name',
                'cluster' => 'cl.name',
//                'category' => 'cc.name',
            	'indicators' => new Zend_Db_Expr('COUNT(DISTINCT i.indicator_id)'),
//                'profiles' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT p.profile_id)')
            )
        );

        $select
            ->joinLeft(array('cl' => 'at_criteria_clusters'), 'cl.cluster_id = c.cluster_id', array())
            ->joinLeft(array('cc' => 'at_categories'), 'cc.category_id = c.category_id', array())
            ->joinLeft(array('i' => 'at_criteria_indicators'), 'i.criterion_id = c.criterion_id', array())
            ->joinLeft(array('apcv' => 'at_profile_criterion_values'), 'apcv.criterion_id = c.criterion_id AND apcv.importance = 2', array())
            ->joinLeft(array('p' => 'at_profiles'), 'apcv.profile_id = p.profile_id', array())
            ->where('c.type IN (?)', new Zend_Db_Expr(implode(',', HM_At_Evaluation_Method_CompetenceModel::getCriterionTypes())))
// @todo: реализовать soft-delete и вернуть условие, оно правильное
//            ->where('c.status=?', HM_At_Criterion_CriterionModel::STATUS_ACTUAL)
            ->group(array(
                'c.criterion_id',
                'c.name',
                'cl.name',
                'cc.name',
                'c.type',
            ));
        ;

        $grid = $this->getGrid($select, array(
            'criterion_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Название'),
                'callback' => $isAtCompetenceRemoved ? null : array(
                    'function'=> array($this, 'updateIndicators'),
                    'params'=> array('{{criterion_id}}', '{{name}}')
                ),
            ),
            'cluster' => array(
                'title' => _('Кластер'),
            ),
//            'category' => array(
//                'title' => _('Категория должности'),
//            ),
            'indicators' => array(
                'title' => _('Количество индикаторов'),
                'callback' => array(
                    'function'=> array($this, 'updateIndicators'),
                    'params'=> array('{{criterion_id}}', '{{indicators}}')
                )
            ),
            'profiles' => array(
                'title' => _('Входит в профили'),
                'callback' => array(
                    'function'=> array($this, 'profilesCache'),
                    'params'=> array('{{profiles}}', $select)
                )
            ),
        ),
            array(
                'name' => null,
                'cluster' => null,
                'indicators' => null,
                'category' => null,
            )
        );

        if (!$this->currentUserRole(array(
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL
        ))) {
            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'competence',
                'action' => 'edit'
            ),
                array('criterion_id'),
                $this->view->svgIcon('edit', 'Редактировать')
            );

            $grid->addAction(array(
                'module' => 'criterion',
                'controller' => 'competence',
                'action' => 'check-before-delete'
            ),
                array('criterion_id'),
                $this->view->svgIcon('delete', 'Удалить')
            );

            $grid->addMassAction(
                array(
                    'module' => 'criterion',
                    'controller' => 'competence',
                    'action' => 'check-before-delete-by',
                ),
                _('Удалить компетенции'),
                _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
            );
        }

        $this->view->grid = $grid;
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['criterion_id']);
        $values['status'] = HM_At_Criterion_CriterionModel::STATUS_ACTUAL;
        $this->updateScaleValues($values);
        $res = $this->getService('AtCriterion')->insert($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $this->updateScaleValues($values);
        $res = $this->getService('AtCriterion')->update($values);
    }

    public function updateScaleValues(&$values)
    {
        if ($values['criterion_id']) {
            $this->getService('AtCriterionScaleValue')->deleteBy(array('criterion_id = ?' => $values['criterion_id']));
        }
        $copy = $values;
        foreach ($copy as $key => $value) {
            $valueId = (int)str_replace('scale_value_', '', $key);
            if ($valueId) {
                if (strlen($value)) {
                    $this->getService('AtCriterionScaleValue')->insert(array(
                        'criterion_id' => $values['criterion_id'],
                        'value_id' => $valueId,
                        'description' => $value,
                    ));
                }
                unset($values[$key]);
            }
        }
    }

    public function delete($id) {
        return $this->getService('AtCriterion')->delete($id);
    }

    public function checkBeforeDeleteAction() {
        $criterion_id = $this->_getParam('criterion_id', 0);
        $type = HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION;
        $subjectService = $this->getService('Subject');
        
        $subjects = $subjectService->getSubjectsWithCompetences($criterion_id, $type);
        if ($subjects) {
            $this->view->url = $this->view->url(array(
                'module' => 'criterion',
                'controller' => 'competence',
                'action' => 'delete',
            ));

            $this->view->subjects = $subjects;
            $this->view->criterion_id = $criterion_id;
        }  else {
            $this->deleteAction();
        }
    }
    
    public function checkBeforeDeleteByAction() {
        $postMassIds = $this->_getParam('postMassIds_grid', '');
        $type = HM_Tc_Subject_SubjectModel::FULLTIME_CRITERION_TYPE_CRITERION;
        $subjectService = $this->getService('Subject');
        
        if (strlen($postMassIds)) {
            $ids = explode(',', $postMassIds);
            if (count($ids)) {
                $subjects = $subjectService->getSubjectsWithCompetences($ids, $type);
            }
        }

        if ($subjects) {
            $this->view->url = $this->view->url(array(
                'module' => 'criterion',
                'controller' => 'competence',
                'action' => 'delete-by',
            ));

            $this->view->subjects = $subjects;
            $this->view->postMassIds = $postMassIds;
        } else {
           $this->deleteByAction();
        }
    }
    
    public function setDefaults(Zend_Form $form)
    {
        $criterionId = $this->_getParam('criterion_id', 0);
        $criterion = $this->getService('AtCriterion')->findDependence('CriterionScaleValue', $criterionId)->current();
        $data = $criterion->getData();

        if (count($criterion->scaleValues)) {
            foreach ($criterion->scaleValues as $value) {
                $data['scale_value_' . $value->value_id] = $value->description;
            }
        }

        $form->populate($data);
    }

    public function updateType($type)
    {
        $types = HM_At_Criterion_CriterionModel::getCompetenceTypes();
        return isset($types[$type]) ? $types[$type] : HM_At_Criterion_CriterionModel::TYPE_UNDEFINED;
    }

    public function updateIndicators($criterionId, $str)
    {
        if ($str == '0') return $str;
        return '<a href="' . $this->view->url(array('controller' => 'indicator', 'action' => 'index', 'criterionId' => $criterionId)) . '">' . $this->view->escape($str) . '</a>';
    }

    public function profilesCache($field, $select){

        if($this->profilesCache === array()){
            $smtp = $select->query();
            $res = $smtp->fetchAll();
            $tmp = array();
            foreach($res as $val){
                $tmp[] = $val['profiles'];
            }
            $tmp = implode(',', $tmp);
            $tmp = explode(',', $tmp);
            $tmp = array_unique($tmp);
            $this->profilesCache = $this->getService('AtProfile')->fetchAll(array('profile_id IN (?)' => $tmp));
        }

        $fields = array_unique(explode(',', $field));
        $fields = array_filter($fields, array(get_class($this), '_filterCachedProfiles'));
        $result = (is_array($fields) && (($count = count($fields)) > 1)) ? array('<p class="total">' . sprintf(_n('профиль plural', '%s профиль', $count), $count) . '</p>') : array();
        foreach($fields as $value){
            $tempModel = $this->profilesCache->exists('profile_id', $value);
            $result[] = "<p>{$tempModel->name}</p>";
        }
        if($result)
            return implode(' ',$result);
        else
            return '';
    }

    protected function _filterCachedProfiles($id) {
        return $this->profilesCache->exists('profile_id', $id);
    }

}
