<?php
class HM_Tc_ApplicationImpersonal_ApplicationImpersonalService extends HM_Service_Abstract
{
    public function getClaimantListSource($options)
    {
        $default = array(
            'sessionId'           => 0,
            'departmentId'        => 0,
            'sessionDepartmentId' => 0,
// показываем и согласованные в том числе;
// страницу "Сформированный план убрали совсем"
// теперь заявки не перетекают со страницы на страницу
            'status'              => array(
                HM_Tc_ApplicationImpersonal_ApplicationImpersonalModel::STATUS_ACTIVE,
                HM_Tc_ApplicationImpersonal_ApplicationImpersonalModel::STATUS_COMPLETE,
            )
        );

        $options = array_merge($default, $options);

        $select = $this->getSelect();

        $fields = array(
            'application_impersonal_id' => 'tcai.application_impersonal_id',
            'session_id2' => 'tcai.session_id',
            'subjectId' => 'tcai.subject_id',
            'subject_status' => 's.status',
            'provider_status' => 'tcp.status',
            'department_name' => "sod.name", //"sod3.name",
            'manager_id' => new Zend_Db_Expr('GROUP_CONCAT(som.mid)'),
            'subject' => 's.name',
            'quantity' => 'tcai.quantity',
            'provider_id' => 'tcp.provider_id',
            'provider_name' => 'tcp.name',
            'price' => 'tcai.price',
            'format' => 's.format',
            'period' => 'tcai.period',
            'longtime' => 's.longtime',
            'department' => 'son.name',
            'category' => 'tcai.category',
            'cost_item' => 'tcai.cost_item',
            'event_name' => 'tcai.event_name',
            'quarter' => 'cy.quarter'
        );

        $select->from(array('tcai'=>'tc_applications_impersonal'),
            $fields
        );

        $select->joinLeft(
            array('s' => 'subjects'),
            's.subid = tcai.subject_id',
            array()
        );

        $select->joinLeft(
            array('tcp' => 'tc_providers'),
            'tcp.provider_id = tcai.provider_id',
            array()
        );

        $select->joinLeft(
            array('sod' => 'structure_of_organ'),
            'sod.soid = tcai.department_id',
            array()
        );
        $select->joinLeft(
            array('som' => 'structure_of_organ'),
            'som.owner_soid = sod.soid AND som.is_manager ='.HM_Orgstructure_OrgstructureModel::MANAGER,
            array()
        );
        $select->joinLeft(
            array('tsq' => 'tc_sessions_quarter'),
            'tsq.session_quarter_id = tcai.session_quarter_id',
            array());
        $select->joinLeft(
            array('cy' => 'cycles'),
            'cy.cycle_id = tsq.cycle_id',
            array());
        $select->joinLeft(
            array('tcsd' => 'tc_session_departments'),
            'tcai.session_department_id = tcsd.session_department_id',
            array());
        $select->joinLeft(
            array('son' => 'structure_of_organ'),
            'son.soid = tcsd.department_id',
            array()
        );


        $select->group(array(
                'tcai.application_impersonal_id',
                'tcai.session_id',
                'tcai.subject_id',
                's.status',
                'tcp.status',
                's.name',
                'tcai.price',
                'tcai.quantity',
                's.format',
                'tcp.provider_id',
                'tcp.name',
                'tcai.period',
                's.longtime',
                'tcai.category',
                'tcai.event_name',
                'sod.name',
                'tcai.cost_item',
                'cy.quarter',
                'son.name'
            )
        );

        $select->where($this->quoteInto('tcai.status in (?)', $options['status']));
        if ($options['sessionDepartmentId']) {
            $select->where($this->quoteInto('tcai.session_department_id = ?', $options['sessionDepartmentId']));
        } else {
            if ($options['sessionId']) {
                $select->where($this->quoteInto('tcai.session_id in (?)', $options['sessionId']));
            }
            if ($options['departmentId']) {
                $select->where($this->quoteInto('tcai.department_id in (?)', $options['departmentId']));
            }
        }
        return $select;
    }

    public function getYearPlanArray($sessionId, $status)
    {
        $options = array(
            'sessionId'  => $sessionId,
            'status'     => $status
        );

        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_SUPERVISOR, HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL))) {
            $this->_department = $this->getService('Orgstructure')->getResponsibleDepartment();
            $options['departmentId'] = $this->_department->soid;
        }
        $listSource = $this->getClaimantListSource($options);

/*        $listSource->joinLeft(array('pr' => 'at_profiles'), 'pr.profile_id = sop.profile_id', array());
        $listSource->joinLeft(array('cat' => 'at_categories'), 'cat.category_id=pr.category_id', array(
            'category'       => new Zend_Db_Expr('max(cat.name)'),
            'at_category_id' => 'cat.category_id',
        ));
*/
        $listSource->joinLeft(
            array('cl3' => 'classifiers_links'),
            'cl3.item_id = s.subid AND cl3.type ='. HM_Classifier_Link_LinkModel::TYPE_SUBJECT,
            array());
        $listSource->joinLeft(
            array('c3' => 'classifiers'),
            'c3.classifier_id = s.direction_id AND c3.type =' . HM_Classifier_Type_TypeModel::BUILTIN_TYPE_STUDY_DIRECTIONS,
            array(
                'subject_direction'=>new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(c3.name))')));
//        $listSource->group(array('cat.category_id'));

        $sourceData = $listSource->query()->fetchAll();

        return $sourceData;
    }

}