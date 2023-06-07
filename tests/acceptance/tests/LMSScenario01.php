<?php
class LMSScenario01 extends Codeception_Test_Abstract
{
    public function init()
    {
        $this->I->wantTo('Prepare distant education process in LMS');
        
        $this->addActor('admin')
            ->addController('index')
            ->addController('contract/index')
            ->addController('notice/index')
            ->addController('user/list')
            ->addController('subject/list')
            ->addController('template/certificate')
            ->addController('report/list')
            ->addController('assign/teacher')
            ->addController('scale/list')
            ->addController('subject/index')
            ->addController('resource/list')
            ->addController('quest/subject')
            ->addController('task/list')
            ->addController('formula/list')
            ->addController('lesson/list')
        ;
    }
    
    public function run() 
    {
        $this->index->login($this->admin);

        // #1
        $this->contractIndex->setSettings(array(
            'regDeny' => true,
            'regRequireAgreement' => true,
            'regUseCaptcha' => false,
            'regValidateEmail' => true,
            'regAutoBlock' => false,
            'contractOfferText' => '...',
            'contractPersonalDataText' => '...',
        ));

        // #2
        $notifications = array(
            $this->addData('edit/notification1'),
            $this->addData('edit/notification2'),
        );
        $this->noticeIndex->setNotifications($notifications);

        // #2.5
        $teacherUser = $this->addData('new/user1');
        $teacherUser->id = $this->userList->create($teacherUser);
        $teacherUser->role = HM_Role_Abstract_RoleModel::ROLE_TEACHER;
        $this->setRequisite('teacherUser', $teacherUser);

        // #3
        $this->index->switchRole(HM_Role_Abstract_RoleModel::ROLE_DEAN);
        $subject = $this->addData('new/subject');
        $subject->id = $this->subjectList->create($subject);
        $this->setRequisite('subject', $subject);

        // #4
        $template = $this->addData('edit/certificate');
        $this->templateCertificate->setTemplate($template);

        // #5 пропускаем пока

        // #6

        // #7
        $this->assignTeacher->assign($teacherUser, $subject);

        // #8

        // #14.5 
        $scale = $this->addData('new/scale');
        $scale->id = $this->scaleList->createScale($scale);
        $this->setRequisite('scale', $scale);
 
        
        // #15
        $poll = $this->addData('new/poll');
        $poll->scale = $scale;
        $poll->id = $this->questSubject->createPoll($poll, $subject);
        $this->setRequisite('poll', $poll);
        
        // #9
        $this->index->logout();
        $this->index->login($teacherUser);

        $this->subjectIndex->open($subject);
        
        $module = $this->addData('new/module');
        $module->id = $this->subjectIndex->importModule($module, $subject, false);
        $this->setRequisite('module', $module);
        
        $resource1 = $this->addData('new/resource1');
        $resource1->id = $this->resourceList->createResource($resource1, $subject, false);
        $this->setRequisite('resource1', $resource1);

        $resource2 = $this->addData('new/resource2');
        $resource2->id = $this->resourceList->createResource($resource2, $subject, false);
        $this->setRequisite('resource2', $resource2);


        // #10
        $test = $this->addData('new/test');
        $test->id = $this->questSubject->createTest($test, $subject, false);
        $this->setRequisite('test', $test);

        // #11
        $formula = $this->addData('new/formula');
        $formula->id = $this->formulaList->create($formula, $subject, false);
        $this->setRequisite('formula', $formula);

        // #11.5
        $task = $this->addData('new/task');
        $task->id = $this->taskList->createTask($task, $subject, false);
        $this->setRequisite('task', $task);
         
        // #12
        $lesson1 = $this->addData('new/lesson1');
        $lesson1->teacher = $teacherUser;
        $lesson1->module = $module;
        $lesson1->id = $this->lessonList->create($lesson1, $subject, false);
        $this->setRequisite('lesson1', $lesson1);
        
        // #13        
        $lesson2 = $this->addData('new/lesson2');
        $lesson2->teacher = $teacherUser;
        $lesson2->module = $test;
        $lesson2->formula = $formula;
        $lesson2->conditionLesson = $lesson1;
        $lesson2->id = $this->lessonList->create($lesson2, $subject, false);
        $this->setRequisite('lesson2', $lesson2);

        // #14
        $lesson3 = $this->addData('new/lesson3');
        $lesson3->teacher = $teacherUser;
        $lesson3->module = $task;
        $lesson3->conditionLesson = $lesson2;
        $lesson3->id = $this->lessonList->create($lesson3, $subject, false);
        $this->setRequisite('lesson3', $lesson3);
       
    }
}