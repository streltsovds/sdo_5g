<?php
class HM_Form_Respondents extends HM_Form {

    public function init() 
    {
        $this->addElement('hidden', 'soid', array(
            'Required' => true,
            'Filters' => array('Int'),
            'Value' => 0
        ));
        
        foreach (array_keys(HM_At_Evaluation_Method_CompetenceModel::getRelationTypes()) as $relationType) {
            if ($relationType == HM_At_Evaluation_Method_CompetenceModel::RELATION_TYPE_SELF) continue;
            
            $this->addElement($this->getDefaultMultiCheckboxElementName(), 'respondents_' . $relationType, array(
                'Label' => _('Основной список'),
                'Required' => false,
                'MultiOptions' => array(),
            ));

            $this->addElement($this->getDefaultTagsElementName(), 'respondents_custom_' . $relationType, array(
                'required' => false,
                'Label' => _('Дополнительный список'),
                'json_url' => $this->getView()->url(array('module' => 'user', 'controller' => 'ajax', 'action' => 'users-list'), null, true),
                'newel' => false,
                'maxitems' => 10
            ));

            
            $this->addDisplayGroup(array(
                 'cancelUrl',
                 'respondents_' . $relationType,
                 'respondents_custom_' . $relationType,
            ),
                'group_' . $relationType,
                array('legend' => sprintf(_('Категория респондентов: %s'), HM_At_Evaluation_Method_CompetenceModel::getRelationTypeTitleShort($relationType)))
            );        
        }        
        
        $this->addElement($this->getDefaultSubmitElementName(), 'submit', array('Label' => _('Сохранить')));
        parent::init(); // required!
    }
}