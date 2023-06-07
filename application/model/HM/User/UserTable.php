<?php

class HM_User_UserTable extends HM_Db_Table
{
    protected $_name = "People";
    protected $_primary = "MID";
    protected $_sequence = 'S_45_1_PEOPLE';

    protected $_dependentTables = array(
        "HM_Role_AdminTable",
        "HM_Role_SimpleAdminTable",
        "HM_Role_DeanTable",
    	"HM_Role_TeacherTable",
        "HM_Role_StudentTable",
//        "HM_Role_ModeratorTable",
        'HM_Course_Item_History_HistoryTable',
        'HM_Course_Item_Current_CurrentTable',
        'HM_Subject_Mark_MarkTable',
        'HM_Webinar_User_UserTable',
        'HM_Wiki_WikiArticlesTable',
        'HM_StudyGroup_Users_UsersTable'
    );

    protected $_referenceMap = array(
        'Role' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_Abstract_RoleTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'roles'
        ),
        'SubjectUser' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Subject_User_UserTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'subjectUser'
        ),
        'Claimant' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_ClaimantTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Graduated' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_GraduatedTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Admin' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_AdminTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'SimpleAdmin' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_SimpleAdminTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Dean' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_DeanTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles'
        ),
        'Teacher' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_TeacherTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles'
        ),
        'Developer' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_DeveloperTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles'
        ),
        'Student' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles'
        ),
        'LaborSafety' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_LaborSafetyTable',
            'refColumns'    => 'user_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'labor_safety_specs'
        ),
//        'Moderator' => array(
//            'columns'       => 'MID',
//            'refTableClass' => 'HM_Role_ModeratorTable',
//            'refColumns'    => 'user_id',
//            'onDelete'      => self::CASCADE,
//            'propertyName'  => 'moderators'
//        ),
        'Participant' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_ParticipantTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'participants'
        ),
        'Group_Assign' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Group_Assign_AssignTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'groups'
        ),
        'Lesson_Assign' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Lesson_Assign_AssignTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'lessons'
        ),
        'Lesson' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'teacher',
            'propertyName'  => 'lessons'
        ),
        'Meeting' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Meeting_MeetingTable',
            'refColumns'    => 'moderator',
            'propertyName'  => 'meetings'
        ),
        'Speciality_Assign' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Speciality_Assign_AssignTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'specialities'
        ),
        'Session' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Session_SessionTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'sessions'
        ),
        'Role_Custom_Assign' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Role_Custom_Assign_AssignTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'roles'
        ),
        'itemCurrent' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Course_Item_Current_CurrentTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemCurrent'
        ),
        'itemHistory' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Course_Item_History_HistoryTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'itemHistory'
        ),
        'Mark' =>array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Subject_Mark_MarkTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'mark'
        ),
        'MarkProject' =>array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Project_Mark_MarkTable',
            'refColumns'    => 'mid',
        	'onDelete'      => self::CASCADE,
            'propertyName'  => 'mark'
        ),
        'ChatHistory' =>array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Chat_ChatHistoryTable',
            'refColumns'    => 'sender',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'chathistory'
        ),
        'ChatRefUsers' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Chat_ChatRefUsersTable',
            'refColumns'    => 'user_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'chatrefusers'
        ),
        'Webinar_User' =>array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Webinar_User_UserTable',
            'refColumns'    => 'userId',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'webinars'
        ),
        'StudyGroup_Users' =>array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_StudyGroup_Users_UsersTable',
            'refColumns'    => 'user_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'studyGroup'
        ),
        'Position' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'mid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'positions'
        ),
        'Certificates' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Certificates_CertificatesTable',
            'refColumns'    => 'user_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'certificates'
        ),
        'Interview' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Interview_InterviewTable',
            'refColumns' => 'user_id',
            'propertyName' => 'interview'
        ),
        'ScheduleMarkHistory' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Lesson_Assign_MarkHistory_MarkHistoryTable',
            'refColumns' => 'MID',
            'propertyName' => 'markHistory'
        ),
        'Holidays' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Holiday_HolidayTable',
            'refColumns' => 'user_id',
            'propertyName' => 'holidays'
        ),
        'CreatedTasks' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Task_TaskTable',
            'refColumns' => 'created_by',
            'propertyName' => 'tasks'
        ),
        'Comments' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Comment_CommentTable',
            'refColumns' => 'user_id',
            'propertyName' => 'comments'
        ),
        'Blog' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Blog_BlogTable',
            'refColumns' => 'created_by',
            'propertyName' => 'blogs'
        ),
        'SessionEventUser' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns' => 'user_id',
            'propertyName' => 'sessionEventUser'
        ),
        'ChatMessage' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_ChatMessage_ChatMessageTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'chatMessage'
        ),
        'SessionEventRespondent' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Event_EventTable',
            'refColumns' => 'respondent_id',
            'propertyName' => 'sessionEventRespondent'
        ),
        'SessionUser' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_User_UserTable',
            'refColumns' => 'user_id',
            'propertyName' => 'session_user'
        ),
        'SessionRespondent' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Respondent_RespondentTable',
            'refColumns' => 'user_id',
            'propertyName' => 'session_respondent'
        ),
        'SessionPairFirst' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Event_Pair_PairTable',
            'refColumns' => 'first_user_id',
            'propertyName' => 'pairFirst'
        ),
        'SessionPairSecond' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Event_Pair_PairTable',
            'refColumns' => 'second_user_id',
            'propertyName' => 'pairSecond'
        ),
        'SessionPairSelected' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Session_Event_Pair_PairTable',
            'refColumns' => 'user_id',
            'propertyName' => 'pairSelected'
        ),
        'UserKpi' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Kpi_User_UserTable',
            'refColumns' => 'user_id',
            'propertyName' => 'userKpis'
        ),
        'Profile' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_At_Profile_ProfileTable',
            'refColumns' => 'user_id',
            'propertyName' => 'profile'
        ),            
        'Candidate' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Recruit_Candidate_CandidateTable',
            'refColumns' => 'user_id',
            'propertyName' => 'candidate'
        ),
        'VacancyCandidate' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Recruit_Vacancy_Assign_AssignTable',
            'refColumns' => 'user_id',
            'propertyName' => 'vacancyCandidate'
        ),
        'Newcomer' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns' => 'user_id',
            'propertyName' => 'newcomer'
        ),
        'ManagedNewcomer' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns' => 'manager_id',
            'propertyName' => 'managedNewcomer'
        ),
        'CuratedNewcomer' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Recruit_Newcomer_NewcomerTable',
            'refColumns' => 'evaluation_user_id',
            'propertyName' => 'curatedNewcomer'
        ),
        'Rotation' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Hr_Rotation_RotationTable',
            'refColumns' => 'user_id',
            'propertyName' => 'rotation'
        ),
        'Recruiter' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Role_RecruiterTable',
            'refColumns' => 'user_id',
            'propertyName' => 'recruiter'
        ),
       'User' => array(
           'columns'       => 'MID',
           'refTableClass' => 'HM_At_Session_Pair_Rating_RatingTable',
           'refColumns'    => 'user_id',
           'propertyName'  => 'rating'
       ),
       'QuestAttempt' => array(
           'columns'       => 'MID',
           'refTableClass' => 'HM_Quest_Attempt_AttemptTable',
           'refColumns'    => 'user_id',
           'propertyName'  => 'attempts'
       ),
        'Recruit_Application' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Recruit_Application_ApplicationTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'recruitApplication'
        ),
        'TcTeacher' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Tc_Provider_Teacher_TeacherTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'tcteachers'
        ),
        'CuratorResponsibility' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Role_Curator_Responsibility_ResponsibilityTable',
            'refColumns' => 'user_id',
            'propertyName' => 'curatorResponsibilities',
                ),
        'PollResult' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Poll_Result_ResultTable',
            'refColumns'    => 'user_id',
            'propertyName'=>'poolResult'
        ),
       'Absence' => array(
           'columns'       => 'MID',
           'refTableClass' => 'HM_Absence_AbsenceTable',
           'refColumns'    => 'user_id',
           'propertyName'  => 'absences'
       ),
        'TcApplication' => array(
            'columns' => 'MID',
            'refTableClass' => 'HM_Tc_Application_ApplicationTable',
            'refColumns' => 'user_id',
            'propertyName' => 'tcApplications'
        ),
        'DeputyMain' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_Deputy_DeputyTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'deputyMain'
        ),
        'Deputy' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_Deputy_DeputyTable',
            'refColumns'    => 'deputy_user_id',
            'propertyName'  => 'deputy'
        ),
        'Reserve' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Hr_Reserve_ReserveTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'reserve'
        ),
        'ReserveRequest' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Hr_Reserve_Request_RequestTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'reserveRequest'
        ),
        'Timesheet' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Timesheet_TimesheetTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'timesheet'
        ),
        'HoldMail' => array(
            'columns'       => 'receiver_MID',
            'refTableClass' => 'HM_Mail_Hold_HoldTable',
            'refColumns'    => 'receiver_MID',
            'propertyName'  => 'holdmail'
        ),
        'ForumSection' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Forum_Section_SectionTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'forumSections'
        ),
        'ForumMessage' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_Forum_Message_MessageTable',
            'refColumns'    => 'user_id',
            'propertyName'  => 'forumMessages'
        ),
    );

    public function getDefaultOrder()
    {
        return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC', 'People.Login ASC');
    }
    
    public function findDublicated($lastname,$firstname,$patronymic)
    {
        $select1 = $this->select()->where('LastName = ?',$lastname)
                                 ->where('FirstName = ?',$firstname)
                                 ->where('Patronymic = ?',$patronymic)
                                 ->where('dublicate = ?',0);
        
        $row1 = $this->fetchRow($select1);
        
        $select2 = $this->select()->where('LastName = ?',$lastname)
                                 ->where('FirstName = ?',$firstname)
                                 ->where('Patronymic = ?',$patronymic)
                                 ->where('dublicate = MID');
        
        $row2 = $this->fetchRow($select2);
        
            
        if(null!==$row1)
        {    
            $res = $row1->MID;
            $update = $this->update(array(
                        'dublicate' => $res), "MID=$res");            
            return $res;
        }
        if(null!==$row2)
        {    
            $res = $row2->MID;
                 
            return $res;
        }
        else 
            return;
       
    }
    
    public function queryDublicate($dublicate)     
    {    
        if($dublicate!==null)
        {        
            $mass = array();
            $select1 = $this->select()->where('MID = ?',$dublicate);
            $select2 = $this->select()->where('dublicate = ?',$dublicate)->where('MID != ?',$dublicate);
            $mass[0] = $this->fetchRow($select1);
            $mass[1] = $this->fetchRow($select2);
        
            return $mass;
        } 
        else
            return null;
        
    }    
 /*   public function UpdateDublicated($dublicated)
    {
       (int)$lastid = $this->getAdapter()->lastInsertId();
      
       if(null !== $dublicated)
           $dublicate = $dublicated;
       else 
           $dublicate = "";
      
       $data = array(
         'dublicate'        =>$dublicated,  
         'key_dublicate'    =>'dublicate'
           
       );
       $update = $this->update($data,"MID=$lastid");
       
    }
  */
    public function deleteDublicate($mid_dub,$mid_unic)
    {
        if($mid_dub!=null and $mid_unic!=null)
        {    
            $this->delete("dublicate = $mid_unic AND MID=$mid_dub");
            $select = $this->select()->where('dublicate = ?',$mid_unic)
                                     ->where('MID!=?',$mid_unic);
            $rows = $this->fetchRow($select);
            if($rows==null)
            {    
               $data = array(
                'dublicate'        =>"");
                $update = $this->update($data,"MID=$mid_unic"); 
            }
            return true;
        } 
        else
            return false;
    }
    
}
