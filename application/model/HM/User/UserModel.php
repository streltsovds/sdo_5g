<?php

class HM_User_UserModel
    extends HM_Model_Abstract
    implements  HM_Search_Item_Interface,
                HM_Model_ContextInterface,
                HM_Rest_Interface
{
    const PASSWORD_LENGTH = 7;
    const PHOTO_WIDTH = 200;
    const PHOTO_HEIGHT = 200;

    const EMAIL_NOT_CONFIRMED = 0;
    const EMAIL_CONFIRMED = 1;

    const NEED_EDIT_AFTER_FIRST_LOGIN = 1;

    const BLOCKED_OFF = 0;
    const BLOCKED_ON = 1;

    /**
     * @var HM_User_Metadata_MetadataModel
     */
    private $_metadata = null;

    protected $_primaryName = 'MID';

    public function __construct($data)
    {
        parent::__construct($data);

        if (isset($data['Information'])) {
            if (null == $this->_metadata)
            {
                $this->_metadata = new HM_User_Metadata_MetadataModel(array());
            }

            $this->_metadata->parseString($data['Information']);
        }
    }

    public function getCardFields()
    {
        $return = array (
            //'LastName' => _('Фамилия'),
            //'FirstName' => _('Имя'),
            //'Patronymic' => _('Отчетство'),
            'Login' => _('Логин'),
            //'BirthDate'      => _('Год рождения'),
        );

        $this->getService()->getService('Activity')->initializeActivityCabinet('', '', 0);
        $isModerator = $this->getService()->getService('Activity')->isUserActivityPotentialModerator(
            $this->getService()->getService('User')->getCurrentUserId()
        );

        if ($isModerator || !$this->getService()->getService('Option')->getOption('disable_personal_info')) {
            $return['BirthDate'] = _('Год рождения');
            $return['EMail'] = _('Email');
            $return['Phone'] = _('Рабочий телефон');
            $return['Fax'] = _('Мобильный телефон');
        }

        if ($additional = $this->getService()->getService('Option')->getOption('userFields')) {
            $additional = json_decode($additional);
            foreach ($additional as $addField) {
                $return['getAdditionalField('.$addField->field_id.')'] = $addField->field_name;
            }
        }
        $return['getTags()'] = _('Метки');
        return $return;
    }

    public function getDescription()
    {
        $return = '';
        if (count($this->positions)) {
            $position = $this->positions->current();
            if (count($this->profile)) $profile = $this->profile->current();
            $return = sprintf(_('Должность: %s (%s) '), $position->name, $profile->name);
        } elseif (count($this->vacancies)) {

            $links = array();
            foreach ($this->vacancies as $vacancy) {
                $links[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>',
                    "/recruit/vacancy/report/user/vacancy_id/{$vacancy->vacancy_id}/vacancy_candidate_id/{$vacancy->vacancy_candidate_id}",
                    _('Отчёт о прохождении подбора'),
                    $vacancy->name
                );
            }
            $return = sprintf(_('Проходил(а) отбор на вакансии: <p>%s</p>'), implode(', ', $links));
        }
        return $return;
    }

    public function getViewUrl()
    {
        return array('module' => 'user', 'controller' => 'edit', 'action' => 'card', 'user_id' => $this->MID,  'baseUrl' => '');
    }

    public function getCreateUpdateDate()
    {
        return false; // sprintf(_('Учетная запись создана: %s'), $this->dateTime($this->Registered));
    }

    public function getIconClass()
    {
        return false;

        $return = 'position-icon ';
        if (count($this->positions)) {
            $position = $this->positions->current();
            $return .= $position->is_manager ? 'manager' : 'employee';
        } else {
            $return .= 'candidate';
        }
        return $return;
    }

    public function getCardUrl()
    {
        return array('module' => 'user', 'controller' => 'list', 'action' => 'view', 'user_id' => $this->MID,  'baseUrl' => '');
    }

    public function getTags()
    {
        $tags = $this->getService()->getService('Tag')->getTags($this->MID, $this->getService()->getService('TagRef')->getUserType());
        return $tags ? "<p>".implode("\r\n<p>", $tags) : '';
    }

    public function getAdditionalField($fieldId)
    {
        if (!isset($this->additionalFields)) {
            $this->additionalFields = $this->getService()->getService('User')->getAdditionalFields($this->MID);
        }
        return isset($this->additionalFields['userField_' . $fieldId])
            ? $this->additionalFields['userField_' . $fieldId]
            : '';
    }

    public function setMetadataValue($name, $value)
    {
        if (null == $this->_metadata)
        {
            $this->_metadata = new HM_User_Metadata_MetadataModel(array());
        }

        $this->_metadata->{$name} = $value;

//        $this->Information = $this->_metadata->getString();
    }

    public function setMetadataValues($values)
    {
        if (is_array($values) && count($values)) {
            foreach($values as $name => $value) {
                $this->setMetadataValue($name, $value);
            }
        }
    }

    public function getMetadataValues()
    {
        $ret = array();
        if (null != $this->_metadata)
        {
            $ret = $this->_metadata->getValues();
        }
//         эти проверки должны быть в контроллере и использовать ($isModerator || !$this->getService()->getService('Option')->getOption('disable_personal_info'))
//         if (!in_array(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(),array(HM_Role_Abstract_RoleModel::ROLE_ADMIN,HM_Role_Abstract_RoleModel::ROLE_DEAN)))
//         {
//             $this->_metadata->setValue('tel','');
//         }
        return $ret;
    }

    public function getMetadataValue($key)
    {
        if (($key == 'tel') && !empty($this->Phone)) {
            return $this->Phone; // плавная миграция Phone из метаданных в поле Phone
        }
        if (null != $this->_metadata)
        {
            return $this->_metadata->{$key};
        }

        return false;
    }

    public function getName($brief = false)
    {
        if ($this->getService()->getService('Lang')->countLanguages() > 1) {
            if ($this->getService()->getCurrentLangId() != HM_User_UserService::DEFAULT_LANG) {
                if ($nameLat = $this->getNameLat()) {
                    return $nameLat;
                }
            }
        }
        return $this->getNameCyr($brief);
    }

    public function getFirstName()
    {
        if ($this->getService()->getService('Lang')->countLanguages() > 1) {
            if ($this->getService()->getCurrentLangId() != HM_User_UserService::DEFAULT_LANG) {
                return $this->FirstNameLat;
            }
        }
        return $this->FirstName;
    }

    public function getNameCyr($brief = false)
    {
        if(empty($this->LastName) && empty($this->FirstName)) return $this->Login;

        $name = array();
        if(!empty($this->LastName)) $name[] = $this->LastName;
        if(!empty($this->FirstName)) $name[] = $brief ? substr($this->FirstName, 0, 1) . '.' : $this->FirstName;
        if(!empty($this->Patronymic)) $name[] = $brief ? substr($this->Patronymic, 0, 1) . '.' : $this->Patronymic;

        return trim(implode(' ', $name));
    }

    public function getNameLat()
    {
        if(empty($this->LastNameLat) && empty($this->FirstNameLat)) return false;

        $name = array();
        if(!empty($this->LastNameLat)) $name[] = $this->LastNameLat;
        if(!empty($this->FirstNameLat)) $name[] = $this->FirstNameLat;

        return implode(' ', $name);
    }

    public function getRoles()
    {
        $result = array();

        if (isset($this->roles)) {
            $result = $this->getValue('roles');
        }

        return $result;
    }

    public function getGroups()
    {
        $result = array();
        if (isset($this->groups)) {
            $result = $this->getValue('groups');
        }

        return $result;
    }

    public function isStudent()
    {
        $roles = $this->getRoles();
        if (count($roles)) {
            foreach($roles as $role) {
                if ($role instanceof HM_Role_StudentModel) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isGroupUser($groupId)
    {
        $groups = $this->getGroups();
        if (count($groups)) {
            foreach($groups as $group) {
                if ($group->gid == $groupId) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getPath($part = 'photo')
    {
        return $this->getService()->getPhotoPath($this->MID);
    }

    public function getPhoto()
    {
        return $this->getService()->getPhoto($this->MID);
    }

    public function getDefaultPhoto()
    {
        return '/'.Zend_Registry::get('config')->src->default->photo;
    }

    public function getRealPhoto()
    {
        $config = Zend_Registry::get('config');
        $path = $this->getPath();
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($path . $this->MID .'.*');
        $view = Zend_Registry::get('view');
        foreach($glob as $value) {
            $fn = $config->src->upload->photo.floor($this->MID / $maxFilesCount) . '/' . basename($value);
            return ($fn ? $view->baseUrl($fn) : $fn) . '?_=' . @filemtime(PUBLIC_PATH . $fn);
        }
        return false;
    }

    public function setPhoto($binary)
    {
        $config = Zend_Registry::get('config');
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        try {
            $fn = $config->src->upload->photo . floor($this->MID / $maxFilesCount) . '/' . $this->MID . '.jpg';
            $f = fopen($fn, 'w');
            fwrite($f, $binary);
            fclose($f);
        } catch (Exception $e) {
            //
        }
        return false;
    }

    public function getResume()
    {
        $config = Zend_Registry::get('config');
        $path = $this->getPath('resume');
        $maxFilesCount = (int) $config->path->upload->maxfilescount;
        $glob = glob($path . $this->MID .'.*');
        foreach($glob as $value) {
            return floor($this->MID / $maxFilesCount) . '/' . basename($value);
        }
        return false;
    }

    public function getPhotoWithoutDefault()
    {
        $photo = $this->getPhoto();
        $config = Zend_Registry::get('config');
        return ($photo != $config->src->default->photo) ? $photo : null;
    }

    public function generateKey()
    {
        return md5(md5(sprintf('%s|%s', $this->MID, $this->Login)).'salt');
    }

    public function getServiceName()
    {
        return 'User';
    }

    public function isImportedFromAD()
    {
        return $this->isAD;
    }

    public function getLdapDisabledFormFields()
    {
        $fields = array();
        $config = Zend_Registry::get('config');
        if (isset($config->ldap->mapping->user)) {
            foreach($config->ldap->mapping->user->toArray() as $ldapName => $fieldName) {
                switch($fieldName) {
                    case 'LastName':
                    case 'FirstName':
                    case 'Patronymic':
                        $fields[] = strtolower($fieldName);
                        break;
                    case 'LastNameLat':
                        $fields[] = 'lastnameLat';
                        break;
                    case 'FirstNameLat':
                        $fields[] = 'firstnameLat';
                        break;
                    case 'Login':
                        $fields[] = 'userlogin';
                        break;
                    default:
                        $fields[] = $fieldName;
                }
            }
        }
        return $fields;
    }

    public function prepareFormLdap(Zend_Form $form, HM_Controller_Action $controller)
    {
        //$form->removeElement('userlogin');
        $form->removeElement('userpassword');
        $form->removeElement('userpasswordrepeat');
        $form->removeElement('generatepassword');

        //$form->removeDisplayGroup('Users1');

        $disabledFields = $this->getLdapDisabledFormFields();
        if (count($disabledFields)) {
            foreach($disabledFields as $disabledField) {
                if ($element = $form->getElement($disabledField)) {
                    /*if ($controller->getRequest()->isPost()) {
                        $form->removeElement($disabledField);
                    } else {*/
                        $element->setAttrib('disabled', true);
                        $element->setRequired(false);
                    //}
                }
            }
        }

    }

    /**
     * Получить время последнего логина в формате timestamp
     *
     * Существование метода связанно с некорректным хранением данных о последнем логине в базе:
     * хранится целочисленное значение приведённое к таковому из
     * строкового представления timestamp с вырезанными символами
     * отличными от числовых.
     * Т.е. вместо: timestamp "2000-12-31 23:59:59", хранится: int "20001231235959"
     *
     * @return string
     */
    public function getLastLoginTimestamp()
    {
        return preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5:$6', $this->last);
    }

    public function getUniqueLdapId()
    {
        return $this->mid_external;
//        return self::plainify($this->LastName, $this->FirstName/*, $this->BirthDate*/); // нету в лдапе др
    }

    static public function plainify()
    {
        $params = func_get_args();
        return strtolower(str_replace(array(' ', '.'), '', implode($params)));
    }

    public function getRestDefinition()
    {
        return [
            'id' => (int)$this->MID,
            'externalId' => (string)$this->mid_external,
            'firstname' => (string)$this->FirstName,
            'patronymic' => (string)$this->Patronymic,
            'lastname' => (string)$this->LastName,
            'email' => (string)$this->EMail,
            'phone' => (string)$this->Phone,
        ];

    }

    public function getAge()
    {
        if(!$this->BirthDate)
            return false;

        $nowDate = new HM_Date(strtotime('now'));
        $birthDate   = new HM_Date(strtotime($this->BirthDate));

        // Проверяем на ДР в этом году
        $birthDateCurrentYear = new HM_Date($nowDate->get(HM_Date::YEAR) . '-' . $birthDate->get(HM_Date::MONTH) . '-' . $birthDate->get(HM_Date::DAY));

        $age = $nowDate->get(HM_Date::YEAR) - $birthDate->get(HM_Date::YEAR);

        if (!$birthDateCurrentYear->isLater($nowDate)) {
            $age--;
        }

        return $age;
    }
}
