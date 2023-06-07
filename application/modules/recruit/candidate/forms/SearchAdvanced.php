<?php
class HM_Form_SearchAdvanced extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST)
            ->setName('search-advanced');
        
        $vacancyId = $this->getParam('vacancy_id');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'form'))
        ));

        $this->addElement($this->getDefaultTextElementName(), 'resume', array(
            'Label' => _('Содержимое резюме'),
            'Description' => _('Применимо только к кандидатам, у которых резюме загружено в форматах .docx и .txt'),
            'class' => 'wide',
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'fio', array(
            'Label' => _('ФИО'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'profile', array(
            'Label' => _('Профиль должности'),
            'Description' => _('Применительно к кандидатам внешнего резерва это профиль должности, на которую они проходили отбор ранее'),                
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'position', array(
            'Label' => _('Должность'),
            'Description' => _('Применительно к кандидатам внешнего резерва это должность, на которую они проходили отбор ранее'),                
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement($this->getDefaultTextElementName(), 'department', array(
            'Label' => _('Подразделение'),
            'Description' => _('Применительно к кандидатам внешнего резерва это подразделение, в которое они проходили отбор ранее'),                
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $criterionIds = $criterionElements = $maxScaleValues = array();
        
        // в отличие от автопоиска здесь ищем только по тем параметрам, которые есть в программе подбора
        if (count($criteriaTypes = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->getVacancyCriteria($vacancyId))) {
            foreach ($criteriaTypes as $methodId => $criterionIds) {
                $method = HM_At_Evaluation_EvaluationModel::getMethodTitle($methodId);
                $criteria = $this->getService('AtCriterion')->getCriteriaByMethod($methodId, $criterionIds);
                if (!isset($maxScaleValues[$methodId])) {
                    $maxScaleValues[$methodId] = Zend_Registry::get('serviceContainer')->getService('AtEvaluation')->getMaxScaleValue($methodId);
                }
                 
                foreach ($criteria as $criterion) {
                    
                    if ($methodId == HM_At_Evaluation_EvaluationModel::TYPE_PSYCHO) continue;
                    
                    $criterionType = HM_At_Criterion_CriterionModel::getCriteriaTypeByMethod($methodId);
                    $this->addElement($this->getDefaultSliderElementName(), $criterionElements[] = "criterion_{$criterionType}_{$criterion->criterion_id}", array(
                        'Label' => implode(': ', array($method, $criterion->name)),
                        //'Description' => _('Результаты по данному виду оценки за прошлые сессии подбора или оценки.'),
                        'min' => 0,
                        'max' => $maxScaleValues[$methodId],
                        'step' => 1,
                    ));
                }
            }
        }
        
        

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'resume',
            ),
            'paramsGroup1',
            array('legend' => _('Поиск по резюме'))
        );

        $this->addDisplayGroup(
            array(
                'fio',
                'profile',
                'position',
                'department',
                'submit'
            ),
            'paramsGroup2',
            array('legend' => _('Поиск по атрибутам'))
        );
        
        $this->_HHAttributes();

        if (count($criterionElements)) {
            $this->addDisplayGroup(
                $criterionElements,
                'sliders',
                array('legend' => _('Поиск по результатам прошлой оценки'))
            );
        }
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Найти')));

        $action = array('module' => 'candidate', 'controller' => 'search', 'action' => 'advanced-search', 'page' => null);
        foreach ($this->getElements() as $element) {
        	$action[$element->getName()] = null;
        }
        $this->setAction($this->getView()->url($action));

        parent::init(); // required!
	}
        
        private function _HHAttributes()
        {
            try {
                $huntingService = $this->getService('RecruitServiceFactory')->getRecruitingService('hh');
                $educationTypes = $huntingService->getEducationLevel();
            } catch (Exception $e) {
                return;
            }

            $this->addElement(new HM_Form_Element_FcbkComplete('hh_area', array(
                    'required' => false,
                    'Label' => _('Регион'),
                    'json_url' => '/recruit/vacancy/hh/region-search',
                    'newel' => false,
                    'maxitems' => 1
                )
            ));

//            $this->addElement(new HM_Form_Element_FcbkComplete('hh_area', array(
//                    'required' => false,
//                    'Label' => _('Регион'),
//                    'json_url' => '/recruit/vacancy/hh/region-search',
//                    'newel' => false,
//                    'maxitems' => 1
//                )
//            ));
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_salary_min', array(
                'Label' => _('Зарплата от'),
                'Required' => false,
                'Validators' => array('Int'),
//                'Filters' => array('Int')
            ));
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_salary_max', array(
                'Label' => _('Зарплата до'),
                'Required' => false,
                'Validators' => array('Int'),
//                'Filters' => array('Int')
            ));
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_total_experience_min', array(
                'Label' => _('Опыт работы от'),
                'Required' => false,
                'Validators' => array('Int'),
            ));
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_total_experience_max', array(
                'Label' => _('Опыт работы до'),
                'Required' => false,
                'Validators' => array('Int'),
            ));
            
            $this->addElement($this->getDefaultSelectElementName(), 'hh_education', array(
                'Label' => _('Образование'),
                'required' => false,
                'validators' => array(
                    'int',
                ),
                'filters' => array('int'),
                'multiOptions' => array(0 => _('Не выбрано')) + $educationTypes,
            ));

            $this->addElement($this->getDefaultTagsElementName(), 'hh_citizenship', array(
                'required' => false,
                'Label' => _('Гражданство'),
                'json_url' => '/recruit/vacancy/hh/region-search/countries_only/1',
                'newel' => false,
                'maxitems' => 1
            ));

//            $this->addElement(new HM_Form_Element_FcbkComplete('hh_citizenship', array(
//                    'required' => false,
//                    'Label' => _('Гражданство'),
//                    'json_url' => '/recruit/vacancy/hh/region-search/countries_only/1',
//                    'newel' => false,
//                    'maxitems' => 1
//                )
//            ));
            
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_age_min', array(
                'Label' => _('Возраст от'),
                'Required' => false,
                'Validators' => array('Int'),
//                'Filters' => array('Int')
            ));
            
            $this->addElement($this->getDefaultTextElementName(), 'hh_age_max', array(
                'Label' => _('Возраст до'),
                'Required' => false,
                'Validators' => array('Int'),
//                'Filters' => array('Int')
            ));
            
            $this->addElement($this->getDefaultSelectElementName(), 'hh_gender', array(
                'Label' => _('Пол'),
                'required' => false,
                'Filters' => array('StripTags'),
                'multiOptions' => array(
                    0        => _('Не выбрано'),
                    "male"   => _("Мужской"),
                    "female" => _("Женский"),
                ),
            ));  
            
            $this->addDisplayGroup(
                array(
                    'hh_area',
//                    'hh_metro',
                    'hh_salary_min',
                    'hh_salary_max',
                    'hh_total_experience_min',
                    'hh_total_experience_max',
                    'hh_education',
                    'hh_citizenship',
                    'hh_age_min',
                    'hh_age_max',
                    'hh_gender',
                ),
                'hhParams',
                array('legend' => _('Поиск по атрибутам HeadHunter'))
            );
            
        }
        
}