<?php
class HM_Option_OptionService extends HM_Service_Abstract
{
    private $_options = array();

    public function getOption($name, $modifier = HM_Option_OptionModel::MODIFIER_AT)
    {
        $name .= $modifier;
        
        if (!isset($this->_options[$name])) {
            $this->_options[$name] = $this->getOne($this->fetchAll($this->quoteInto('name LIKE ?', $name)));
        }
        if ($this->_options[$name]) {
            return $this->_options[$name]->value;
        }
        return false;
    }

    public function setOption($name, $value){
        
        $this->_options[$name] = $value;
        $option = $this->getOne($this->fetchAll($this->quoteInto('name LIKE ?', $name)));

        if ($option) {
            $option->name = $name;
            $option->value = $value;
            $this->update($option->getValues());
        } else {
            $this->insert(array(
                'name' => $name,
            	'value' => $value,
            ));
        }
    }
    
    public function getOptions($scope, $modifier = HM_Option_OptionModel::MODIFIER_AT)
    {
        // default values
        $options = array();
        foreach (self::getDefaultOptions($scope) as $key => $value) {
            $key .= $modifier;
            $options[$key] = $value;
        }

        $res = $this->fetchAll(array('name IN (?)' => array_keys($options)));
        foreach ($res as $option) {
            $options[str_replace($modifier, '', $option->name)] = $option->value;
        }
        return $options;
    }
    
    public function getDefaultOptions($scope)
    {
        switch ($scope) {
            case HM_Option_OptionModel::SCOPE_PASSWORDS:
                return array(
                	'passwordMinLength'       => 7,
                	'passwordMinNoneRepeated' => 0,
                    'passwordCheckDifficult'  => 0,
                	'passwordMaxPeriod'       => 0,
                	'passwordMinPeriod'       => 0, 
                	'passwordRestriction'     => 0,
                    'passwordMaxFailedTry'    => 3,
                	'passwordFailedActions'   => 0
                );
            break;
            case HM_Option_OptionModel::SCOPE_CONTRACT:
                return array(
//                	'regAllow' => 1,
                    'regDeny' => 0,
                    'loginStart' => 1,
                	'regRequireAgreement' => 1,
                	'regUseCaptcha' => 0,
                	'regValidateEmail' => 0,
                	'regAutoBlock' => 0,
                    'codeword' => '',
                	'contractOfferText' => '',
                	'contractPersonalDataText' => '',
                    'userFields' => ''
                );
            break;
            case HM_Option_OptionModel::SCOPE_EVALUATION_METHODS:
                return array(
                    'competenceScaleId' => 1,
                    'competenceUseClusters' => 0,
                    'competenceUseScaleValues' => 0,
                    'competenceUseIndicators' => 0,
                    'competenceUseIndicatorsDescriptions' => 0,
                    'competenceUseIndicatorsReversive' => 0,
                    'competenceUseIndicatorsScaleValues' => 0,
                    //'competenceUseRandom' => 0,
                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_CHILDREN => '',
                    'competenceRandom' . HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SIBLINGS => '',
                    'competenceEmployedBeforeDays' => 0,
                    'competenceDisableStop' => 0,
                    'competenceComment' => '',
                    'competenceReportComment' => '',
                    'kpiUseClusters' => 0,
                    'kpiUseCriteria' => '',
                    'kpiScaleId' => '',
                    'kpiComment' => '',
                    'kpiReportComment' => '',
                    'ratingComment' => '',
                    'ratingReportComment' => '',
                    'sessionComment' => '',
                );
            break;
            case HM_Option_OptionModel::SCOPE_RECRUIT_PUBLICATIONS:
                return array(
                    'publicationCompanyName' => '',
                    'publicationCompanyDescription' => '',
                    'publicationCompanyConditions' => ''
                );
                break;
            case HM_Option_OptionModel::SCOPE_BASE:
                return array(
	                'enable_email' => '',
	                'dekanEMail' => '',
	                'dekanName' => '',
	                'windowTitle' => '',
	                'grid_rows_per_page' => Bvb_Grid::ROWS_PER_PAGE,
                    'images_allowed_domains' => '*',
                    'ext_pages_videos_allowed_domains' => '*',
                    'maxUserEvents' => '',
	                
	                'use_techsupport' => '',

	                'use_holidays' => '',
	                'generateCertificateFiles' => '',
	                'managers_notify_session_quarter_days_before' => '',
	                'disable_personal_info' => '',
	                'disable_messages' => '',
	                'security_logger' => '',
	                'scorm_tracklog' => '',
	                'disable_multiple_authentication' => '',
                    'message_signature' => 'С уважением, администрация портала',
                );
                break;
            case HM_Option_OptionModel::SCOPE_DESIGN:
                return array(
                    'logo' => '',
                    'index_description' => '',
                    'loginBg1' => '',
                    'loginBg2' => '',
                    'loginBg3' => '',
                    'loginBg4' => '',
                    'loginBg5' => '',
                    'skin' => 'blue',
                    'windowTitle' => '',
                    'themeColors' => '',
					'logoBack' => '',
                );
                break;
            case HM_Option_OptionModel::SCOPE_SOCIAL:
                return [
                    'vk' => '',
//                    'facebook' => '',
                    'telegram' => '',
                    'youtube' => '',
//                    'instagram' => '',
                ];
                break;
            case HM_Option_OptionModel::SCOPE_AD:
                return array(
                    'adSsoEnable' => 1,
                    'adSyncEnable' => 1,
                    'adHost' => '',
                    'adPort' => '',
                    'adUseStartTls' => 0,
                    'adUsername' => '',
                    'adPassword' => '',
                    'adAccountDomainName' => 'company.local',
                    'adAccountDomainNameShort' => 'company',
                    'adBaseDn' => 'DC=company,DC=local',
                    'adQuery' => '',
                );
            break;
            default:
                return array();
            break;
        }        
    }
    
    public function setOptions($options, $modifier = HM_Option_OptionModel::MODIFIER_AT)
    {
        foreach($options as $key => $value) {

            $key .= $modifier;
            
            $count = $this->countAll($this->getSelect()->getAdapter()->quoteInto('name = ?', $key));
            if($count > 0){
                $this->updateWhere(array('value' => $value), array('name = ?' => $key));
            }else{
                $this->insert(
                    array(
                    	'name' => $key,
                        'value' => $value
                    )
                );
            }
        }
        return true;
    }
    
    public function getContractTexts()
    {
        // default values
        $result = array(
            'contractOfferText'    => "",
            'contractPersonalDataText' => ""
        );

        $res = $this->fetchAll(
            array('name IN (?)' =>
                array(
                    'contractOfferText',
                    'contractPersonalDataText'
                )
            )
        );

        foreach($res as $option){
            $result[$option->name] = $option->value;
        }
        return $result;
    }

    public function setContractTexts($contracts)
    {
        foreach($contracts as $key => $value){
            $count = $this->countAll($this->getSelect()->getAdapter()->quoteInto('name = ?', $key));
            if($count > 0){
                $this->updateWhere(array('value' => $value), array('name = ?' => $key));
            }else{
                $this->insert(
                    array(
                        'name' => $key,
                        'value' => $value
                    )
                );
            }
        }
        return true;
    }
    
    public function getDefaultCurrency()
    {
        // default values
        $result = 'RUB';
        
        $res = $this->fetchAll(
            array('name = ?' => 'default_currency' )
        );
        
        foreach($res as $option){
            $result = $option->value;
        }
        return $result;
    }

    public function processUserFields($multiSetArray)
    {
        $result = [];
        $maxId  = 0;

        $oldValues = $this->getService('Option')->getOption('userFields');
        $oldValues = $oldValues? json_decode($oldValues) : [];

        $deletedIds = [];
        foreach ($oldValues as $oldValue) {
            $deletedIds[$oldValue->field_id] = $oldValue->field_id;
        }

        foreach($multiSetArray as $key => $fieldData) {
            if ($key === 'new') {
                foreach ($fieldData['field_name'] as $fKey => $name) {
                    if (!$name) {
                        continue;
                    }
                    $result[] = [
                        'field_id'   => 0,
                        'field_name' => $fieldData['field_name'][$fKey],
                        'field_required' => $fieldData['field_required'][$fKey],
                    ];
                }
            } else {
                unset($deletedIds[$fieldData['field_id']]);

                $result[] = $fieldData;
                if ($maxId < $fieldData['field_id']) {
                    $maxId = $fieldData['field_id'];
                }
            }
        }

        if ($deletedIds) {
            $this->getService('UserAdditionalFields')->deleteBy($this->quoteInto(
                'field_id IN (?)', $deletedIds));
        }

        foreach ($result as $nn => $field) {
            if (!$field['field_id']) {
                $maxId++;
                $result[$nn]['field_id'] = $maxId;
            }
        }

        return $result;
    }

    public function getAvailableSkins()
    {
        return [
            'standard' => _('Стандартная'),
            // 'dark' => _('Темная'),
            'red' => _('Красная'),
            'yellow' => _('Желтая'),
            'green' => _('Зеленая'),
            'purple' => _('Фиолетовая'),
            'grey' => _('Серая'),
            'blue' => _('Синяя'),
        ];
    }

    public function deleteOptions($options)
    {
        return $this->deleteBy($this->quoteInto(
            'name IN (?)', $options));
    }

    public function getDesignSettingAuthForm()
    {
        $designOptions = $this->getOptions(HM_Option_OptionModel::SCOPE_DESIGN);

        $hmLoginFormData = HM_Json::encodeErrorSkip([
            "logo" => $designOptions['logo'],
            "index_description" => $designOptions['index_description']
        ]);
        return $hmLoginFormData;
    }
    
    protected function _applyModifier(&$value, &$key, $modifier)
    {
        $key .= $modifier;
    }
}