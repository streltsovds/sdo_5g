<?php
class HM_Form_ApplicationImpersonal extends HM_Form
{

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('applicationImpersonal');
        $commonGroup = array();

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(
                array(
                    'module'     => 'application',
                    'controller' => 'impersonal',
                    'action'     => 'index',
                    'session_id' =>  $this->getParam('session_id', 0)
                ), null, true
            )
        ));

        $this->addElement('hidden', 'application_impersonal_id', array(
            'Required'   => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getParam('application_impersonal_id', 0)
        ));

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'category', array(
                'Label'        => _('Тип обучения'),
                'Required'     => true,
                'filters'      => array(array('int')),
                'multiOptions' => HM_Tc_Application_ApplicationModel::getApplicationCategories(),
                'onclick'      => "getInitialCoursesList()"
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'period', array(
                'Label'        => _('Планируемый срок обучения'),
                'Required'     => true,
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'cost_item', array(
            'Label' => 'Статья расходов',
            'Required' => false,
            'Filters' => array('int'),
            'Validators' => array(
                'int',
                array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
            'OnChange' => "onCostItemChange(this.value)"
        ));

        $this->addElement($this->getDefaultTextElementName(), $commonGroup[] = 'event_name', array(
                'Label' => _('Название мероприятия'),
                'Required' => false,
            )
        );

        $this->addElement($this->getDefaultTextElementName(), $commonGroup[] = 'price', array(
                'Label' => _('Стоимость (на 1 чел.)'),
                'Required' => false,
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'subject_id', array(
                'Label'        => _('Курс'),
                'Required'     => false,
                'Filters'      => array(
                    'Int'
                ),
            )
        );

        $this->addElement($this->getDefaultTextElementName(), $commonGroup[] = 'empty_positions', array(
                'Label'        => _('Количество пользователей'),
                'Required'     => true,
                'Validators'   => array(
                    'Int'
                ),
            )
        );

        $this->addElement($this->getDefaultTreeSelectElementName(), $commonGroup[] = 'department', array(
            'Label' => _('Подразделение'),
            'Required' => true,
            'validators' => array(
                'int',
                'ThirdOrLowerLevelDepartment'
            ),
            'filters' => array('int'),
            'params' => array(
                'remoteUrl' => $this->getView()->url(array('module' => 'application', 'controller' => 'ajax', 'action' => 'departments'))
            )
        ));

        $this->addDisplayGroup($commonGroup,
            'application',
            array('legend' => _('Общие свойства'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init();
    }


}