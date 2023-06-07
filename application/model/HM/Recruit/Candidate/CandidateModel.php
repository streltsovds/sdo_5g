<?php
class HM_Recruit_Candidate_CandidateModel extends HM_Model_Abstract
{

    const SOURCE_EXTERNAL_ESTAFF = 4;

    protected $_primaryName = 'candidate_id';

    /**
     * Проверка, есть ли у этого кандидата прикрепленное резюме
     * @return bool
     */
    public function hasResumeJson()
    {
        $result = false;

        if ( in_array($this->source, array(HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER, HM_Recruit_Provider_ProviderModel::ID_SUPERJOB))) {
            try {
                $resumeData = json_decode($this->resume_json);
                $result = true;
            } catch (Exception $e) {
                $result = false;
            }
        } elseif($this->source == HM_Recruit_Provider_ProviderModel::ID_ESTAFF) {
            $result = false;
        }

        return $result;
    }

    public function hasResumeFile()
    {
        $result = false;
        $path = Zend_Registry::get('serviceContainer')->getService('User')->getPath(Zend_Registry::get('config')->path->upload->resume, $this->candidate_id);
        $filePath = $path. $this->candidate_id . '.docx';
        if (file_exists($filePath) && is_file($filePath)) $result = true;
        return $result;
    }

    public function hasResumeHtml()
    {
        $result = false;
        if (!empty($this->resume_html)) $result = true;
        return $result;
    }

    public function isJsonResume()
    {
        return $this->source == HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER
            || $this->source == HM_Recruit_Provider_ProviderModel::ID_SUPERJOB
            && $this->hasResumeJson();
    }

    public function isFileResume()
    {
        return !($this->source == HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER
            || $this->source == HM_Recruit_Provider_ProviderModel::ID_SUPERJOB)
            && $this->hasResumeFile();
    }

    public function isHtmlResume()
    {
        return !($this->source == HM_Recruit_Provider_ProviderModel::ID_HEADHUNTER
            || $this->source == HM_Recruit_Provider_ProviderModel::ID_SUPERJOB)
            && $this->hasResumeHtml();
    }

    // в перерывах между сессиями подбора внешние кандидаты должны быть заблокированы
    public function setAutoBlocked($blocked = HM_User_UserModel::BLOCKED_ON)
    {
//        if ($this->source != HM_Recruit_Provider_ProviderModel::ID_PERSONAL) {
//            Zend_Registry::get('serviceContainer')->getService('User')->updateWhere(array('blocked' => $blocked), array('MID = ?' => $this->user_id));
//            // этот $candidate->user используется дальше
//            if (count($this->user)) {
//                $this->user->current()->blocked = $blocked;
//            }
//        }        
    }    
    
    // внешним кандидатам нельзя одновременно отбираться на несколько вакансий
    // теперь можно :)
    public function isAllowed()
    {
//        if ($this->source != HM_Recruit_Provider_ProviderModel::ID_PERSONAL) {
//            if (count($this->vacancies)) {
//                $vacancyStatus = $this->vacancies->getList('status');
//                if (isset($vacancyStatus[HM_Recruit_Vacancy_VacancyModel::STATE_ACTUAL]) || isset($vacancyStatus[HM_Recruit_Vacancy_VacancyModel::STATE_PENDING])) {
//                    return false;
//                }
//            }
//        }
        return true;
    }
}