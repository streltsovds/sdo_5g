<?php
class HM_Test_Abstract_AbstractService extends HM_Service_Abstract
{
    public function delete($id)
    {
        //удаляем метки
        $this->getService('TagRef')->deleteBy($this->quoteInto(array('item_id=?',' AND item_type=?'),
                                                               array($id,HM_Tag_Ref_RefModel::TYPE_TEST)));
        $resources = $this->getService('Resource')->fetchAll(
            $this->quoteInto('test_id = ?', $id)
        );

        if (count($resources)) {
            foreach($resources as $resource) {
                $this->getService('Resource')->delete($resource->resource_id);
            }
        }

        $this->getService('TestQuestion')->deleteBy(
            $this->quoteInto('test_id = ?', $id)
        );

        return parent::delete($id);
    }

    public function insert($data, $unsetNull = true)
    {
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        $data['created'] = $data['updated'] = $this->getDateTime();
        $test = parent::insert($data);

        if ($test && $test->test_id) {
            $this->getService('TestQuestion')->processTest($test);
        }

        return $test;
    }

    public function update($data, $unsetNull = true)
    {
        $data['updated'] = $this->getDateTime();

        $test = parent::update($data);

        if ($test && $test->test_id) {
            $this->getService('TestQuestion')->processTest($test);
            if (isset($data['data'])) {
                $this->getService('Test')->updateWhere(array('data' => $data['data']), $this->quoteInto('test_id = ?', $test->test_id));
            }
        }

        return $test;
    }

    public function publish($id)
    {
        $this->update(array(
            'test_id' => $id,
            'status' => HM_Test_TestModel::STATUS_STUDYONLY,
        ));
    }
    
    public function unpublish($id)
    {
        $this->update(array(
            'test_id' => $id,
            'status' => HM_Test_TestModel::STATUS_UNPUBLISHED,
        ));
    }


    public function isEditable($subjectIdFromResource, $subjectId, $status)
    {

//         $all = array(HM_Role_Abstract_RoleModel::ROLE_DEVELOPER, HM_Role_Abstract_RoleModel::ROLE_MANAGER);
//         $role = $this->getService('User')->getCurrentUserRole();
//         if(in_array($role, $all)){
        if(Zend_Registry::get('serviceContainer')->getService('Acl')->isCurrentAllowed('mca:test:abstract:new')){
            return true;
        }
        if($subjectId == 0){
            return false;
        }



        if($this->getService('Acl')->inheritsRole($role, array(HM_Role_Abstract_RoleModel::ROLE_TEACHER))
            //$role == HM_Role_Abstract_RoleModel::ROLE_TEACHER
            && $status == HM_Resource_ResourceModel::LOCALE_TYPE_LOCAL && $subjectIdFromResource == $subjectId){
            return true;
        }

        return false;
    }


    public function createLesson($subjectId, $testId)
    {
         $lessons = $this->getService('Lesson')->fetchAll(
            $this->getService('Lesson')->quoteInto(
                array('typeID = ?', " AND params LIKE ?", ' AND CID = ?'),
                array(HM_Event_EventModel::TYPE_TEST, '%module_id='.$testId.';%', $subjectId)
            )
        );

        if (!count($lessons)) {

            if ($test = $this->getOne($this->getService('TestAbstract')->find($testId))) {

            $simpleTest = $this->getOne($this->getService('Test')->fetchAll(array('test_id = ?' => $test->test_id)));


            if(!$simpleTest){

               $simpleTest = $this->getService('Test')->insert(
                   array(
                       'title'          => $test->title,
                       'comments'       => $test->comments,
                       'startlimit'     => 0,
                       'timelimit'      => 0,
                       'endres'         => 0,
                       'data'           => $test->data,
                       'allow_view_log' => 1,
                       'test_id'        => $test->test_id,
                       'datatype'       => 1,
                       'status'         => 1,
                       'cid'	    	=> $subjectId
                   )
               );

            } else {
                $arrTest = $simpleTest->getData();

                $arrTest['startlimit']     = 0;
                $arrTest['timelimit']      = 0;
                $arrTest['endres']         = 0;
                $arrTest['allow_view_log'] = 1;
                $arrTest['test_id']        = $test->test_id;
                $arrTest['cid']            = $subjectId;
                unset($arrTest['tid']);
                $simpleTest = $this->getService('Test')->insert($arrTest);
            }


// запретим тесты в свободном доступе - сложная логика получается..
            /*  if ($simpleTest) {
                $values = array(
                    'title' => $test->title,
                    'descript' => $test->comments ? $test->comments : '',
                    'begin' => date('Y-m-d 00:00:00'),
                    'end' => date('Y-m-d 23:59:00'),
                    'createID' => 1,
                    'typeID' => HM_Event_EventModel::TYPE_TEST,
                    'vedomost' => 1,
                    'CID' => $subjectId,
                    'startday' => 0,
                    'stopday' => 0,
                    'timetype' => 2,
                    'isgroup' => 0,
                    'teacher' => 0,
                    'params' => 'module_id=' . (int) $simpleTest->tid . ';',
                    'all' => 1,
                    'cond_sheid' => '',
                    'cond_mark' => '',
                    'cond_progress' => 0,
                    'cond_avgbal' => 0,
                    'cond_sumbal' => 0,
                    'cond_operation' => 0,
                    'isfree' => HM_Lesson_LessonModel::MODE_PLAN,
                );
                $lesson = $this->getService('Lesson')->insert($values);

                $simpleTest->lesson_id = $lesson->SHEID;

                $this->getService('Test')->update($simpleTest->getData());

                $students = $lesson->getService()->getAvailableStudents($subjectId);
                if (is_array($students) && count($students)) {
                    $this->getService('Lesson')->assignStudents($lesson->SHEID, $students);
                }
            }*/
            // if test
        }

        } // if count lessons

    }

/*    public function deleteLesson($subject, $testId){
        $Tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $testId));

        $testList = array_keys($Tests->getList('tid', 'title'));
        $lessons = $this->getService('Lesson')->fetchAll(
            array(
                'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                'CID = ?' => $subject->subid
            )
        );

        if (count($lessons)) {
            foreach($lessons as $lesson) {
                if(in_array($lesson->getModuleId(), $testList)){
                    $this->getService('Lesson')->delete($lesson->SHEID);
                }
            }
        }

    }*/

    public function clearLessons($subject, $testId){
        $Tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $testId));

        $testList = array_keys($Tests->getList('tid', 'title'));
        $lessons = $this->getService('Lesson')->fetchAll(
            array(
                'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                'CID = ?' => $subject->subid
            )
        );

        if (count($lessons)) {
            foreach($lessons as $lesson) {
                if(in_array($lesson->getModuleId(), $testList)){
//                     $this->getService('Lesson')->deleteBy(array('SHEID = ?' => $lesson->SHEID, 'isfree IN (?)' => new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)))));
                    $this->getService('Lesson')->updateWhere(array('params' => ''), array('SHEID = ?' => $lesson->SHEID, 'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN));
                }
            }
        }

    }



    public function clearLesson($subject, $testId)
    {

        $Tests = $this->getService('Test')->fetchAll(array('test_id = ?' => $testId));
        $testList = array_keys($Tests->getList('tid', 'title'));

        if($subject == null){
            $lessons = $this->getService('Lesson')->fetchAll(
                array(
                    'typeID = ?' => HM_Event_EventModel::TYPE_TEST
                )
            );
        }else{
            $lessons = $this->getService('Lesson')->fetchAll(
                array(
                    'typeID = ?' => HM_Event_EventModel::TYPE_TEST,
                    'CID = ?' => $subject->subid
                )
            );
        }

        if (count($lessons)) {
            $subjectNew = null;
            foreach($lessons as $lesson) {
                if(in_array($lesson->getModuleId(), $testList)){
                    $subjectNew = $this->getService('Subject')->getOne($this->getService('Subject')->find($lesson->CID));
//                     $this->getService('Lesson')->deleteBy(array('SHEID = ?' => $lesson->SHEID, 'isfree IN (?)' => new Zend_Db_Expr(implode(',', array(HM_Lesson_LessonModel::MODE_FREE, HM_Lesson_LessonModel::MODE_FREE_BLOCKED)))));
                    $this->getService('Lesson')->updateWhere(array('params' => ''), array('SHEID = ?' => $lesson->SHEID, 'isfree = ?' => HM_Lesson_LessonModel::MODE_PLAN));
                }
            }
        }

    }

    public function getDefaults()
    {
        $user = $this->getService('User')->getCurrentUser();
        return array(
            'created' => $this->getDateTime(),
            'updated' => $this->getDateTime(),
            'created_by' => $user->MID,
            'status' => 0, //public
        );
    }

    public function copy($test, $subjectId = null)
    {
        if ($test) {
            if (null !== $subjectId) {
                $test->subject_id = $subjectId;
            }

            $questions = $test->getQuestionsIds();

            if (count($questions)) {

                $test->data = '';
                $newQuestions = array();

                foreach($questions as $questionId) {
                    $newQuestion = $this->getService('Question')->copy($questionId);
                    if ($newQuestion) {
                        $newQuestions[] = $newQuestion->kod;
                    }
                }

                $test->addQuestionsIds($newQuestions);
            }

            $newTest = $this->insert($test->getValues(null, array('test_id', 'task_id', 'quiz_id')));

            return $newTest;
        }

        return false;
    }

    /**
     * Функция принимает коллекцию из id тестов, и место хранения
     * Возвращает отфильтрованную коллекцию из id тестов у которых место хранения соответствует указанному
     *
     * @param     $ids   Коллекция идентификаторов тестов
     * @param int $location место хранения
     *
     * @return array
     */
    public function filterByLocation($ids,$location = HM_Test_Abstract_AbstractModel::LOCALE_TYPE_GLOBAL)
    {
        $globalTests = array();
        if (count($ids)) {
            $temporaryTests = $this->getSelect()->from("test_abstract","test_id")
                ->where("test_id IN ('".implode("','",$ids)."')")
                ->where("location = ".$location);

            $temporaryTests = $temporaryTests->query()->fetchAll();
            if($temporaryTests)
            foreach($temporaryTests as $test){
                $globalTests[] = $test['test_id'];
            }
        }
        return $globalTests;
    }
}