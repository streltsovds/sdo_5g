<?php
class HM_Form_Application extends HM_Form
{

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('tcRequiredApplication');

        $this->addElement('hidden', 'cancelUrl', array(
                'Required' => false,
                'Value' => $this->getView()->url(
                    array(
                        'module'     => 'session',
                        'controller' => 'education',
                        'action'     => 'required',
                        'session_id' =>  $this->getParam('session_id', 0)
                    ), null, true
                )
            )
        );

        $this->addElement('hidden', 'application_id', array(
            'Required'   => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'value' => $this->getParam('application_id', 0)
        ));


        $potentialUsers = $this->getApplicationPotentialUsers();
        $this->addElement($this->getDefaultSelectElementName(), 'user_id', array(
                'Label'        => _('Пользователь'),
                'Required'     => false,
                'multiOptions' => $potentialUsers,
                'Filters'      => array(
                    'Int'
                ),
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'subject_id', array(
                'Label'        => _('Курс'),
                'Required'     => true,
                'Filters'      => array(
                    'Int'
                ),
            )
        );

        $this->addElement($this->getDefaultSelectElementName(), 'period', array(
                'Label'        => _('Планируемый срок обучения'),
                'Required'     => true,
            )
        );

        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array(
            'Label' => _('Сохранить')
        ));

        $this->addDisplayGroup(array(
                'user_id',
                'subject_id',
                'period',
                'submit'
            ),
            'application',
            array('legend' => _('Назначение'))
        );

        parent::init();
    }

    protected function getApplicationPotentialUsers()
    {
        $positions = array();
        $applicationId = $this->getParam('application_id', 0);

        if ($application = $this->getService('TcApplication')->getOne($this->getService('TcApplication')->find($applicationId))) {
            if ($department = $this->getService('Orgstructure')->getOne($this->getService('Orgstructure')->find($application->department_id))) {

                $positions = $this->getService('Orgstructure')->fetchAllDependence('User', array(
                    'type = ?' => HM_Orgstructure_OrgstructureModel::TYPE_POSITION,
                    'lft > ?' => $department->lft,
                    'rgt < ?' => $department->rgt,
                ));
            }
        }

        $users = array(0 => '-');
        foreach ($positions as $position) {
            /** @var HM_User_UserModel $user */
            if ($user = count($position->user) ? $position->user->current() : false) {
                $users[$position->mid] = sprintf('%s (%s)', $user->getName(), $position->name);
            }
        }
        asort($users);

        return $users;
    }
}