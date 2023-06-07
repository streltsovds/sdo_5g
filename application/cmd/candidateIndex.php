<?php
//function shut() {
//    var_dump(error_get_last());
//}
//register_shutdown_function('shut');

include "cmdBootstraping.php";

$services = Zend_Registry::get('serviceContainer');
$config = Zend_Registry::get('config');
$encoding = $config->charset;

$resumeJsonCache = [];
$collection = $services->getService('RecruitCandidate')->fetchAll();
$resumeJsonCache = $collection->getList('candidate_id', 'resume_json');

$select = $services->getService('RecruitCandidate')->getSelect();
$select->from(['p' => 'People'], [
    'user_id' => 'p.MID',
    'gender' => 'p.Gender',
    'birthdate' => 'p.BirthDate',
    'fio' => new Zend_Db_Expr("CONCAT(CONCAT(CONCAT(CONCAT(p.LastName, ' ') , p.FirstName), ' '), p.Patronymic)")
])
    ->joinLeft(['rc' => 'recruit_candidates'], 'p.MID = rc.user_id', [
        'candidate_id',
        'resume_external_url',
        'hh_area',
        'hh_metro',
        'hh_salary',
        'hh_total_experience',
        'hh_education',
        'hh_citizenship',
        'hh_age',
        'hh_gender',
    ])
    ->joinLeft(['rvc' => 'recruit_vacancy_candidates'], 'rc.candidate_id = rvc.candidate_id', [])
    ->joinLeft(['rv' => 'recruit_vacancies'], 'rvc.vacancy_id = rv.vacancy_id', [])
    ->joinLeft(['rap' => 'at_profiles'], 'rap.profile_id = rv.profile_id', ['vacancy_profile' => new Zend_Db_Expr('GROUP_CONCAT(rap.name)')])
    ->joinLeft(['rso' => 'structure_of_organ'], 'rso.soid = rv.position_id', ['vacancy_position' => new Zend_Db_Expr('GROUP_CONCAT(rso.name)')])
    ->joinLeft(['rso2' => 'structure_of_organ'], 'rso2.soid = rso.owner_soid', ['vacancy_department' => new Zend_Db_Expr('GROUP_CONCAT(rso2.name)')])
    ->joinLeft(['so' => 'structure_of_organ'], 'so.mid = p.MID', ['position' => new Zend_Db_Expr('GROUP_CONCAT(so.name)'), 'current_position_id' => new Zend_Db_Expr('MAX(so.soid)')])
    ->joinLeft(['ap' => 'at_profiles'], 'so.profile_id = ap.profile_id', ['profile' => new Zend_Db_Expr('GROUP_CONCAT(ap.name)')])
    ->joinLeft(['so2' => 'structure_of_organ'], 'so2.soid = so.owner_soid', ['department' => new Zend_Db_Expr('GROUP_CONCAT(so2.name)')])
    ->where('(rvc.result IS NULL OR rvc.result != ?)', HM_Recruit_Vacancy_Assign_AssignModel::RESULT_FAIL_BLACKLIST)
    ->group([
        'p.MID',
        'p.Gender',
        'p.BirthDate',
        'p.LastName',
        'p.FirstName',
        'p.Patronymic',
        'rc.candidate_id',
        'rc.resume_external_url',
        'hh_area',
        'hh_metro',
        'hh_salary',
        'hh_total_experience',
        'hh_education',
        'hh_citizenship',
        'hh_age',
        'hh_gender',
    ]);
//     ->join(array('asu' => 'at_session_users'), 'asu.vacancy_candidate_id = rvc.vacancy_candidate_id', array())
//     ->join(array('asucv' => 'at_session_user_criterion_values'), 'asucv.session_user_id = asu.session_user_id', array())
;
//echo $select;
$stmt = $select->query();
$stmt->execute();

echo '<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
<sphinx:field name="fio"/> 
<sphinx:field name="profile"/>
<sphinx:field name="position"/>
<sphinx:field name="department"/>
<sphinx:field name="resume"/>
<sphinx:attr name="user_id" type="int" bits="32" default="0"/>
<sphinx:attr name="current_position_id" type="int" bits="32" default="0"/>
<sphinx:attr name="candidate_id" type="int" bits="32" default="0"/>
<sphinx:attr name="gender" type="int" bits="32" default="0"/>
<sphinx:attr name="age" type="int" bits="32" default="0"/>

<sphinx:attr name="hh_area" type="int" bits="32" default="0"/>
<sphinx:field name="hh_metro"/>
<sphinx:attr name="hh_salary" type="int" bits="32" default="0"/>
<sphinx:attr name="hh_total_experience" type="int" bits="32" default="0"/>
<sphinx:field name="hh_education" />
<sphinx:field name="hh_citizenship"/>
<sphinx:attr name="hh_age" type="int" bits="32" default="0"/>
<sphinx:field name="hh_gender"/>

</sphinx:schema>';

foreach ($stmt->fetchAll() as $row) {

    $resume = '';
    if (!empty($resumeJsonCache[$row['candidate_id']])) {
        $resumeData = json_decode($resumeJsonCache[$row['candidate_id']], true);
        array_walk_recursive($resumeData, function ($item2, $key, $resume) {
            $resume .= $item2 . ' ';
        }, $resume);
    } else {
        ob_start();
        $services->getService('RecruitVacancyAssign')->printResume($row['candidate_id']); // @todo: неплохо бы хранить данные о том что резюме загружено и не сканировать каждый раз файловую систему
        $resume = ob_get_clean();
    }

    // todo: нужен либо humanFormat без деления далее, либо не-humanformat и деление
    $diff = HM_Date::getPeriodSinceDate($row['birthdate'], false);
    $age = floor($diff / 31536000); // фактически это не текущий возраст, а "сколько лет исполнилось/исполнится в текущем году"

    echo '<sphinx:document id="' . $row['user_id'] . '">
<fio><![CDATA[ ' . HM_Index_Abstract::convertAndFilter($row['fio']) . ' ]]></fio>
<profile><![CDATA[ ' . HM_Index_Abstract::convertAndFilter(implode(',', [$row['profile'], $row['vacancy_profile']])) . ' ]]></profile>
<position><![CDATA[ ' . HM_Index_Abstract::convertAndFilter(implode(',', [$row['position'], $row['vacancy_position']])) . ' ]]></position>
<department><![CDATA[ ' . HM_Index_Abstract::convertAndFilter(implode(',', [$row['department'], $row['vacancy_department']])) . ' ]]></department>
<resume><![CDATA[ ' . $resume . ' ]]></resume>
<user_id>' . $row['user_id'] . '</user_id>
<candidate_id>' . $row['candidate_id'] . '</candidate_id>
<current_position_id>' . $row['current_position_id'] . '</current_position_id>
<gender>' . $row['gender'] . '</gender>
<age>' . $age . '</age>
    
<hh_area>' . $row['hh_area'] . '</hh_area>
<hh_metro>' . $row['hh_metro'] . '</hh_metro>
<hh_salary>' . $row['hh_salary'] . '</hh_salary>
<hh_total_experience>' . $row['hh_total_experience'] . '</hh_total_experience>
<hh_education>' . $row['hh_education'] . '</hh_education>
<hh_citizenship>' . $row['hh_citizenship'] . '</hh_citizenship>
<hh_age>' . $row['hh_age'] . '</hh_age>
<hh_gender>' . $row['hh_gender'] . '</hh_gender>
    

</sphinx:document>';
}
echo '</sphinx:docset>';