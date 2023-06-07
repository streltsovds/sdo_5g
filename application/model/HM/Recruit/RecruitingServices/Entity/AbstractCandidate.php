<?php
/**
 * Description of AbstractCandidate
 *
 * @author tutrinov
 */
abstract class HM_Recruit_RecruitingServices_Entity_AbstractCandidate {
    
    protected $fullName = null;
    protected $firstName = null;
    protected $lastName = null;
    protected $patronymic = null;
    protected $birthDate = null;
    protected $email = null;
    protected $phone = null;
    protected $htmlRaw = null;
    protected $url = null;
    
    public function getFullName() {
        return $this->fullName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getPatronymic() {
        return $this->patronymic;
    }

    public function getBirthDate() {
        return $this->birthDate;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function setPatronymic($patronymic) {
        $this->patronymic = $patronymic;
    }

    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }
    
    public function getHtmlRaw() {
        return $this->htmlRaw;
    }

    public function setHtmlRaw($htmlRaw) {
        $this->htmlRaw = $htmlRaw;
    }
    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }
    
}
