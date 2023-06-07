<?php
class Criterion_IndicatorController extends HM_Controller_Action
{
    use HM_Controller_Action_Trait_Grid {
        newAction as newActionTraitGrid;
        editAction as editActionTraitGrid;
    }

    private $_criterion;

    public function init()
    {
        $form = new HM_Form_Indicator();
        $this->_setForm($form);

        parent::init();

        $criterionId = (int) $this->_getParam('criterionId', 0);
        if ($criterionId) {
            $this->_criterion = $this->getOne(
                $this->getService('AtCriterion')->find($criterionId)
            );
            $this->view->setHeader($this->_criterion->name);
            $this->view->setBackUrl($this->view->url([
                'module' => 'criterion',
                'controller' => 'competence',
                'action' => 'index',
                'criterionId' => null,
            ]));
        } else {
            $this->_redirector->gotoSimple('index', 'list', 'criterion');
        }

        if (!$this->getService('Option')->getOption('competenceUseIndicators')) {

// индикаторы нужны еще для парных сравнений
// вообще, не факт что нужно запрещать редакирование при выключенных настройках
//       	
//             $this->_flashMessenger->addMessage(array(
//                 'type'    => HM_Notification_NotificationModel::TYPE_ERROR,
//                 'message' => _('Использование индикаторов компетенций отключено в настройках методики оценки')
//             ));
//             $this->_redirector->gotoSimple('index', 'competence', 'criterion');
        }


        $this->view->setSubHeader(_('Индикаторы'));
    }

    public function indexAction()
    {
        $select = $this->getService('AtCriterionIndicator')->getSelect();

        $select->from(
            array(
                'i' => 'at_criteria_indicators'
            ),
            array(
                'indicator_id',
                'name',
                'doubt' => new Zend_Db_Expr('CASE WHEN doubt = 1 THEN \'Да\' ELSE \'Нет\' END'),
            )
        );

        $select
            ->where('criterion_id = ?', $this->_criterion->criterion_id);

        $grid = $this->getGrid($select, array(
            'indicator_id' => array('hidden' => true),
            'name' => array(
                'title' => _('Описание'),
            ),
            'doubt' => array(
                'title' => _('Респондент может не оценивать данную компетенцию'),
            ),
        ),
            array(
                'name' => null,
                'doubt' => null,
            )
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'indicator',
            'action' => 'edit'
        ),
            array('indicator_id'),
            $this->view->svgIcon('edit', 'Редактировать')
        );

        $grid->addAction(array(
            'module' => 'criterion',
            'controller' => 'indicator',
            'action' => 'delete'
        ),
            array('indicator_id'),
            $this->view->svgIcon('delete', 'Удалить')
        );

        $grid->addMassAction(
            array(
                'module' => 'criterion',
                'controller' => 'indicator',
                'action' => 'delete-by',
            ),
            _('Удалить индикаторы'),
            _('Данное действие может быть необратимым. Вы действительно хотите продолжить?')
        );

        $this->view->grid = $grid;

    }

    public function newAction()
    {
        $this->view->setSubHeader(_('Создание индикатора'));
        $this->newActionTraitGrid();
    }

    public function editAction()
    {
        $this->view->setSubHeader(_('Редактирование индикатора'));
        $this->editActionTraitGrid();
    }

    protected function _redirectToIndex()
    {
        $this->_redirector->gotoSimple('index', null, null, array('criterionId' => $this->_criterion->criterion_id));
    }

    public function create($form)
    {
        $values = $form->getValues();
        unset($values['indicator_id']);
        $values['criterion_id'] = $this->_criterion->criterion_id;
        $indicatorValues = array(
            'name' => $values['name'],
            'name_questionnaire' => $values['name_questionnaire'],
            'criterion_id' => $this->_criterion->criterion_id,
            'description_positive' => $values['description_positive'],
            'description_negative' => $values['description_negative'],
            'reverse' => $values['reverse'],
            'order' => $values['order'],
            'doubt' => $values['doubt'],
        );
        $res = $this->getService('AtCriterionIndicator')->insert($indicatorValues);
        $values['indicator_id'] = $res->indicator_id;
        $this->updateScaleValues($values);
    }

    public function update($form)
    {
        $values = $form->getValues();
        $values['criterion_id'] = $this->_criterion->criterion_id;
        $this->updateScaleValues($values);
        $res = $this->getService('AtCriterionIndicator')->update($values);
    }

    public function updateScaleValues(&$values)
    {
        $atService = $this->getService('AtCriterionIndicatorScaleValue');
        if ($values['criterion_id']) {
            $atService->deleteBy(array(
                'indicator_id = ?' => $values['indicator_id'],
            ));
        }
        $copy = $values;
        foreach ($copy as $key => $value) {
            $check = stristr($key, 'scale_value_questionnaire_');
            $repVal = $check ? 'scale_value_questionnaire_' : 'scale_value_';
            $insVal = $check ? 'description_questionnaire' : 'description';
            $valueId = (int)str_replace($repVal, '', $key);
            $insertArray = array(
                'value_id' => $valueId,
                'indicator_id' => $values['indicator_id'],
            );
            if ($valueId) {
                if (strlen($value)) {
                    $indicator = $this->getOne($atService->fetchAll($atService->quoteInto(array(
                        'value_id = ?',
                        ' AND indicator_id = ?'
                    ), array(
                        $valueId,
                        $values['indicator_id']
                    ))));
                    $insertArray[$insVal] = $value;
                    if (!$indicator) {
                        $atService->insert($insertArray);
                    } else {
                        $insertArray['criterion_indicator_value_id'] = $indicator->criterion_indicator_value_id;
                        $atService->update($insertArray);
                    }
                }
                unset($values[$key]);
            }
        }
    }

    public function delete($id) {
        $this->getService('AtCriterionIndicator')->delete($id);
    }


    public function setDefaults(Zend_Form $form)
    {

        $indicatorId = $this->_getParam('indicator_id', 0);
        $indicator = $this->getService('AtCriterionIndicator')->findDependence('CriterionIndicatorScaleValue',$indicatorId)->current();
        $data = $indicator->getData();

        if (count($indicator->scaleValues)) {
            foreach ($indicator->scaleValues as $value) {
                $data['scale_value_' . $value->value_id] = $value->description;
                $data['scale_value_questionnaire_' . $value->value_id] = $value->description_questionnaire;
            }
        }

        $form->populate($data);
    }
}
