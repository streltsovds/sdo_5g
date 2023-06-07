<?php
class HM_Form_Application extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('application');
        $commonGroup =
        $paymentTypeGroup = array();

        $sessionId = $this->getParam('session_id');

        $this->addElement('hidden', 'cancelUrl', array(
                'Required' => false,
                'Value'    => $this->getView()->url(
                    array(
                        'module' => 'application',
                        'controller' => 'list',
                        'action' => 'index',
                        'session_id' => $sessionId,
                        'application_id' => null
                    )
                )
            )
        );

        $categoryOptions = array(
            'Label'        => _('Тип обучения'),
            'Required'     => true,
            'filters' => array(array('int')),
            'multiOptions' => HM_Tc_Application_ApplicationModel::getApplicationCategories()
        );

        if ($this->getRequest()->getActionName() == 'create-recommended') {
            $categoryOptions['Value'] = HM_Tc_Application_ApplicationModel::CATEGORY_ADDITION;
        }

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'category', $categoryOptions);

        $this->addElement('RadioGroup', $paymentTypeGroup[] = 'payment_type', array(
                'Label' => '',
                'MultiOptions' => HM_Tc_Application_ApplicationModel::getPaymentTypes(),
                'form' => $this,
                'dependences' => array(
                    HM_Tc_Application_ApplicationModel::PAYMENT_PARTIAL => array('payment_percent')
                )
            )
        );

        $this->addElement($this->getDefaultTextElementName(), $paymentTypeGroup[] = 'payment_percent', array(
                'Label' => 'Процент оплаты пользователем',
                'validators' => array(
                    'Int',
                    array('GreaterThan', false, array(-1)),
                    array('LessThan', false, array(100))
                ),
                'filters' => array('int'),
                'value' => 0,
                'Required' => false,
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
                array('GreaterThan', false, array('min' => -1, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
            ),
        ));

        $this->addElement($this->getDefaultSelectElementName(), $commonGroup[] = 'subject_id', array(
                'Label'        => _('Курс'),
                'Required'     => true,
                'Validators' => array(
                    'int',
                    array('GreaterThan', false, array('min' => 0, 'messages' => array(Zend_Validate_GreaterThan::NOT_GREATER => _("Необходимо выбрать значение из списка"))))
                )
            )
        );

        $this->addElement($this->getDefaultMultiSelectElementName(), $commonGroup[] = 'users', array(
                'Label'        => _('Пользователь(и)'),
                'Required'     => true,
                'multiple'     => true
            )
        );

        $this->addDisplayGroup(
            $commonGroup,
            'pricinggroup',
            array('legend' => _('Общие свойства'))
        );

        $this->addDisplayGroup(
            $paymentTypeGroup,
            'typegroup',
            array('legend' => _('Тип финансирования'))
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        parent::init();
    }
}