<?php
class HM_Tc_CorporateLearning_Participant_ParticipantModel extends HM_Model_Abstract
{
    protected $_primaryName = 'participant_id';

    public function getServiceName()
    {
        return 'TcCorporateLearningParticipant';
    }
}