<?php
class Candidate_EstaffController extends HM_Controller_Action {

    public function importAction()
    {
        $path = implode(DIRECTORY_SEPARATOR, array('', '..', 'data', 'temp', 'estaff', ''));
        $xmlDir = APPLICATION_PATH.$path;
        $count = 0;

        $usersCache = $vacanciesCache = $candidatesCache = array();

        $collection = $this->getService('RecruitCandidate')->fetchAllHybrid('User', 'Vacancy', 'VacancyAssign', array('source = ?' => HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL_ESTAFF));
        foreach ($collection as $item) {

            if (count($item->user)) {
                $user = $item->user->current();
                $usersCache[$item->candidate_external_id] = $user;
            }

            if (count($item->vacancy)) {
                $vacancy = $item->vacancy->current();
                if (!empty($vacancy->vacancy_external_id)) {
                    $candidatesCache[implode('-', array($item->candidate_external_id, $vacancy->vacancy_external_id))] = $item;
                }
            }
        }

        $collection = $this->getService('RecruitVacancy')->fetchAll('vacancy_external_id IS NOT NULL');
        foreach ($collection as $item) {
            $vacanciesCache[$item->vacancy_external_id] = $item;
        }

        $allVacancyFiles = $allVacancies = array();
        foreach (array_diff(scandir($xmlDir), array('.', '..')) as $file) {
            if (strpos($file, 'vacancy-') === 0) {

                $allVacancyFiles[str_replace('.xml', '', str_replace('vacancy-0x', '', $file))] = $file;

                $xml = simplexml_load_file($xmlDir.$file);
                $vacancyXml = $this->_xml2array($xml);

                $vacancyXml['id'] = $this->_prepareId($vacancyXml['id']);
                $allVacancies[$vacancyXml['id']] = $vacancyXml;
            }
        }

        foreach (array_diff(scandir($xmlDir), array('.', '..')) as $file) {
            if (strpos($file, 'candidate') === 0) {
                $xml = simplexml_load_file($xmlDir.$file);
                // Если файл не прочитался, вырезаем из него левые <attachments> и пробуем прочитать
                if (!$xml) {
                    $correctContent = $this->_replaceAllByTags(file_get_contents($xmlDir.$file));
                    $xml = simplexml_load_string($correctContent);
                    if (!$xml) continue;
                }

                $candidateXml = $this->_xml2array($xml);
                $candidateXml['id'] = $this->_prepareId($candidateXml['id']);

                if ((!is_null($candidateXml['id'])) && (!array_key_exists($candidateXml['id'], $usersCache))) {

                    $dataUser = array(
                        'mid_external'   => $candidateXml['id'],
                        'Login'          => $candidateXml['id'],
                        'LastName'       => $candidateXml['lastname'],
                        'FirstName'      => $candidateXml['firstname'],
                        'Patronymic'     => $candidateXml['middlename'],
                        'EMail'          => $candidateXml['email'],
                        'Phone'          => $candidateXml['home_phone'],
                        'CellularNumber' => $candidateXml['mobile_phone'],
                        'BirthDate'      => $candidateXml['birth_date'],
                        'Gender'         => $candidateXml['gender_id'],
                        'Age'            => $candidateXml['age'],
                        'Registered'     => date('Y-m-d H:i:s'),
                    );

                    $user = $this->getService('User')->insert($dataUser);
                    $usersCache[$candidateXml['id']] = $user;

                } else {
                    $user = $usersCache[$candidateXml['id']];
                }

                if ($user) {
                    if (isset($candidateXml['spots']) && count($candidateXml['spots'])) {
                        foreach ($candidateXml['spots'] as $spots) {

                            if (isset($spots['vacancy_id'])) $spots = array($spots);

                            foreach ($spots as $spot) {

                                $spot['vacancy_id'] = $this->_prepareId($spot['vacancy_id']);
                                if (isset($spot['vacancy_id']) && isset($allVacancies[$spot['vacancy_id']])) {
                                    $vacancyXml = $allVacancies[$spot['vacancy_id']];
                                    if (!array_key_exists($spot['vacancy_id'], $vacanciesCache)) {


                                        $date = new HM_Date($vacancyXml['start_date']);
                                        $dataVacancy = array(
                                            'justAdd' => 1, // ужосс
                                            'vacancy_external_id' => $vacancyXml['id'],
                                            'name' => $this->_prepareString($vacancyXml['name']),
                                            'create_date' => $date->toString('Y-MM-dd'),
                                            'open_date' => $date->toString('Y-MM-dd'),
                                            'status' => HM_Recruit_Vacancy_VacancyModel::STATE_EXTERNAL,
                                        );
                                        $vacancy = $this->getService('RecruitVacancy')->insert($dataVacancy);
                                        $vacanciesCache[$vacancyXml['id']] = $vacancy;
                                    } else {
                                        $vacancy = $vacanciesCache[$vacancyXml['id']];
                                    }

                                    if ($vacancy) {

                                        $key = implode('-', array($user->mid_external, $vacancy->vacancy_external_id));
                                        if (!array_key_exists($key, $candidatesCache)) {

                                            $dataCandidate = array(
                                                'user_id' => $user->MID,
                                                'candidate_external_id' => $user->mid_external, // он же $candidate['id'],
                                                'source' => HM_Recruit_Candidate_CandidateModel::SOURCE_EXTERNAL_ESTAFF,
                                            );

                                            if (count($candidateXml['attachments'])) {
                                                foreach ($candidateXml['attachments'] as $attachments) {

                                                    if (isset($attachments['text'])) $attachments = array($attachments);

                                                    foreach ($attachments as $attachment) {
                                                        if ($this->_in_interval($spot, $attachment, 60)) {
                                                            $dataCandidate['resume_html'] = $this->_getResumeText($xmlDir.$file);
                                                            break;
                                                        }
                                                    }
                                                }
                                            }

                                            $candidate = $this->getService('RecruitCandidate')->insert($dataCandidate);
                                            $result = $this->_mapResults($spot['state_id']);

                                            $this->getService('RecruitVacancyAssign')->insert(array(
                                                'candidate_id' => $candidate->candidate_id,
                                                'user_id' => $candidate->user_id,
                                                'vacancy_id' => $vacancy->vacancy_id,
                                                'status' => $spot['is_active'] ? HM_Recruit_Vacancy_Assign_AssignModel::STATUS_ACTIVE : HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED,
                                                'result' => $result,
                                                'external_status' => $spot['state_id'],
                                            ));

                                            $candidatesCache[$key] = $candidate;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $count += 1;
            }
        }

        $this->_flashMessenger->addMessage(_('Импорт успешно завершён; обработано записей: ').$count);
        $this->_redirector->gotoUrl('/');
    }

    private function _xml2array($xmlObject, $out = array())
    {
        $arr = (array) $xmlObject;
        foreach ($arr  as $index => $node) {
            if (is_object ($node)) {
                $out[$index] = $this->_xml2array($node);
            } elseif (is_array($node)) {
                $out[$index] = array();
                foreach ($node as $item) {
                    $out[$index][] = $this->_xml2array($item);
                }
            } else {
                $out[$index] = $node;
            }
        }

        return $out;
    }

    private function _in_interval($spot, $attachment, $interval)
    {
        $spot = strtotime($spot['start_date']);
        $date = strtotime($attachment['date']);
        return (($date - $spot) <= $interval) ? true : false;
    }

    private function _getResumeText($file)
    {
        preg_match_all("'<text .*>(.*?)</text>'si", file_get_contents($file), $text);

        $result = iconv('windows-1251', 'utf-8', $text[1][0]);
        $result = html_entity_decode($result);

        return $this->_prepareString($result);
    }

    private function _replaceAllByTags($text, $tag='text')
    {
        $TAG_LEN = strlen($tag)+2;
        $curPosition = 0;
        while(true)
        {
            $firstCharPosition = mb_strpos($text, "<{$tag} ", $curPosition, 'windows-1251');

            if(!$firstCharPosition) return $text;
            $lastCharPosition  = mb_strpos($text, "</{$tag}>", $firstCharPosition, 'windows-1251');

            $a = mb_substr($text, $firstCharPosition, ($lastCharPosition - $firstCharPosition)+$TAG_LEN+1, 'windows-1251');

            $curLen = mb_strlen($text, 'windows-1251');
            $p1 = mb_substr($text, 0, $firstCharPosition, 'windows-1251');
            $p2 = mb_substr($text, $lastCharPosition+$TAG_LEN+1, $curLen-($lastCharPosition+$TAG_LEN+1), 'windows-1251');

            $a1 = iconv('UTF-8', 'windows-1251', $a);
            if((mb_strlen($a, 'windows-1251')-mb_strlen($a1, 'windows-1251'))<mb_strlen($a, 'windows-1251')/2.3) {
                $a = $a1;
            }
            $text = $p1.$a.$p2;

            $curPosition = $lastCharPosition-$curLen+mb_strlen($text, 'windows-1251');
        }
        return $text;
    }

    protected function _prepareId($str)
    {
        return str_replace('0x', '', $str);
    }

    protected function _prepareString($str)
    {
//        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
//        $str = htmlspecialchars_decode(htmlspecialchars(trim($str), ENT_SUBSTITUTE, 'UTF-8'));

        return (string)$str;
    }

    protected function _mapResults($stateEstaff)
    {
        switch ($stateEstaff) {
            case "hire":
            case "hire_paperwork":
            case "job_offer:succeeded":
                $result = HM_Recruit_Vacancy_Assign_AssignModel::RESULT_SUCCESS;
                break;
            case "new":
            case "invitation":
            case "phone_interview":
            case "rr_interview:scheduled":
            case "event_type_1":
            case "event_type_2":
            case "event_type_3":
            case "event_type_4":
            case "vacancy_response":
            case "rr_resume_review":
                $result = false;
                break;
            default:
                $status = HM_Recruit_Vacancy_Assign_AssignModel::STATUS_PASSED;
                $result = HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_DEFAULT;
                break;
        }
        return $result;
    }

    // ??
    public function notImportedAction()
    {
        $path = implode(DIRECTORY_SEPARATOR, array('', '..', 'data', 'temp', 'estaff', ''));
        $notImportedPath = implode(DIRECTORY_SEPARATOR, array('', '..', 'data', 'temp', '_estaff', ''));
        $xmlDir = APPLICATION_PATH . $path;
        $copyDir = APPLICATION_PATH . $notImportedPath;
        $notEmptyMidExternalPeople = $this->getService('User')->fetchAll('mid_external != \'\'');
        $midExternals =
        $candidates   = array();

        foreach ($notEmptyMidExternalPeople as $person) {
            $midExternals[] = $person->mid_external;
        }

        foreach (array_diff(scandir($xmlDir), array('.', '..')) as $file) {
            if (strpos($file, 'candidate-0x') === 0) {
                $candidate = str_replace('.xml', '', str_replace('candidate-0x', '', $file));
                if (!in_array($candidate, $candidates)) $candidates[] = $candidate;
            }
        }
        $notImportedFiles = array();
        foreach (array_diff($candidates, $midExternals) as $diff) {
            $fileName = 'candidate-0x'.$diff.'.xml';
            $notImportedFiles[] = $fileName;
            copy($xmlDir.DIRECTORY_SEPARATOR.$fileName, $copyDir.DIRECTORY_SEPARATOR.$fileName);
        }

        pr($notImportedFiles);die();
    }

}