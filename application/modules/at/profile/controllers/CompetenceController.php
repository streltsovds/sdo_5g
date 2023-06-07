<?php
class Profile_CompetenceController extends HM_Controller_Action_Profile
{
    public function indexAction()
    {
        // навигация вручную
        $this->view->setCurrentPage('mca:profile:criterion:corporate');
        $this->view->setSubHeader(_('Компетенции: профиль успешности'));

        $scaleId = $this->getService('Option')->getOption('competenceScaleId'); // шкала оценки компетенций; в подборе может использоваться другая шкала
        // @todo: надо отсортировать по ScaleValue.value (не факт ,что они в базе в нужном порядке)
//        $scale = $this->getService('Scale')->fetchAllDependenceJoinInner(
//            'ScaleValue',
//            $this->getService('Scale')->quoteInto('ScaleValue.scale_id = ?', $scaleId)
//        )->current();



        $scale = $this->getService('Scale')->findOne($scaleId);
        if (false !== $scale) {
            $scaleValues = $this->getService('ScaleValue')->fetchAll(
                $this->getService('ScaleValue')->quoteInto("scale_id = ?", $scale->scale_id),
                array('value')
            );

            $scale->scaleValues = $scaleValues;
        }


        $criteriaValues = $this->getService('AtProfileCriterionValue')->fetchAllDependence(array('Criterion'), array('profile_id = ?' => $this->_profileId, 'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE));
        $results = $criteriaValues->getList('criterion_id', 'value_id');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $values = $this->_getAllParams();
            if (isset($values['results']) && (count($values['results']) == count($criteriaValues))) {
                foreach ($values['results'] as $criterionId => $valueId) {
                    $this->getService('AtProfileCriterionValue')->updateWhere(array(
                        'value_id' => $valueId,
                        'value' => HM_Scale_Converter::getInstance()->id2value($valueId, $scaleId),
                    ), array(
                        'profile_id = ?' => $this->_profileId,
                        'criterion_id = ?' => $criterionId,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_CORPORATE,
                    ));
                }
                $this->view->success = true;
            } else {
                $this->view->success = false;
            }
            $results = $values['results'];
        }

        $this->view->profile = $this->_subject;
        $this->view->criteriaValues = $criteriaValues;
        $this->view->scale = $scale;
        $this->view->results = $results;
    }
   
    public function professionalAction()
    {
        // навигация вручную
        $this->view->setCurrentPage('mca:profile:criterion:professional');
        $this->view->setSubHeader(_('Квалификации: профиль успешности'));
        $error = array();

        $results = array();
        if (count($criteriaValues = $this->getService('AtProfileCriterionValue')->fetchAllDependence(array('CriterionTest'), array('profile_id = ?' => $this->_profileId, 'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL)))) {
            foreach ($criteriaValues as $criteriaValue) {
                if (count($criteriaValue->criterionTest)) {
                    $results[$criteriaValue->criterion_id] = $criteriaValue->value;
                }
            }
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $values = $this->_getAllParams();

            // Валидация
            foreach ($values['results'] as $criterionId => $value) {
                if (!is_numeric($value)) $error[] = $criterionId;
            }

            if (isset($values['results']) && (count($values['results']) == count($results)) && empty($error)) {
                foreach ($values['results'] as $criterionId => $value) {
                    $this->getService('AtProfileCriterionValue')->updateWhere(array(
                        'value' => $value,
                    ), array(
                        'profile_id = ?' => $this->_profileId,
                        'criterion_id = ?' => $criterionId,
                        'criterion_type = ?' => HM_At_Criterion_CriterionModel::TYPE_PROFESSIONAL,
                    ));

                }
                $this->_flashMessenger->addMessage(_('Элемент успешно обновлён'));
            } else {
                $this->_flashMessenger->addMessage(array(
                    'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
                    'message' => _('Внимание! Не все поля заполнены корректно!')
                ));
            }
            $results = $values['results'];
        }

        $this->view->profile = $this->_subject;
        $this->view->criteriaValues = $criteriaValues;
        $this->view->results = $results;
        $this->view->error = $error;
    }
}
