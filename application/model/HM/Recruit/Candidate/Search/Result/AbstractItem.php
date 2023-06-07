<?php

/**
 * Generic class for found candidates
 *
 * @author tutrinov
 */
abstract class HM_Recruit_Candidate_Search_Result_AbstractItem extends HM_Service_Primitive
{
    
    const ITEM_TYPE_DEFAULT = 'default';
    const ITEM_TYPE_EXTERNAL = 'external';

    /**
     * Candidate identifier
     * @var int
     */
    protected $candidateId = null;

    /**
     * Candidate name
     * @var string
     */
    protected $candidateFirstName = null;

    /**
     * Candidate second name
     * @var string
     */
    protected $candidateLastName = null;

    /**
     * Candidate birth date
     * @var string|Zend_Date
     */
    protected $candidateBirthDate = null;
    
    public function getCandidateId() {
        return $this->candidateId;
    }

    public function getCandidateBirthDate() {
        return $this->candidateBirthDate;
    }

    public function setCandidateId($candidateId) {
        $this->candidateId = $candidateId;
    }
    
    public function getCandidateFirstName() {
        return $this->candidateFirstName;
    }

    public function getCandidateLastName() {
        return $this->candidateLastName;
    }

    public function setCandidateFirstName($candidateFirstName) {
        $this->candidateFirstName = $candidateFirstName;
    }

    public function setCandidateLastName($candidateLastName) {
        $this->candidateLastName = $candidateLastName;
    }

    public function setCandidateBirthDate(Zend_Date $candidateBirthDate) {
        $this->candidateBirthDate = $candidateBirthDate;
    }
    
    public function addition() {
    }
    
    public function getAdditionalData() {
    }

}
