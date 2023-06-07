<?php
/*
 * 5G
 *
 */
class HM_Form_LessonAssign extends HM_Form
{
	public function init()
	{
        $subjectId = $this->getParam('subject_id', 0);
        $lessonId = $this->getParam('lesson_id', 0);
        /** @var HM_Lesson_LessonService $lessonService */
        $lessonService = $this->getService('Lesson');
        $lesson = $lessonService->findOne($lessonId);

        $this->setMethod(Zend_Form::METHOD_POST);

        $this->addElement('hidden', 'subject_id', array(
            'Required' => false,
            'Value' => $subjectId
        ));

        $collection = $this->getService('Teacher')->fetchAllDependence(
            'User',
            $this->getService('Teacher')->quoteInto('CID = ?', $subjectId)
        );

        $teachers = [];
        if (count($collection)) {
            foreach($collection as $item) {
                $teacher = $item->getUser();
                if ($teacher) {
                    $teachers[$teacher->MID] = $teacher->getName();
                }
            }
        }

        $this->addElement($this->getDefaultSelectElementName(), 'teacher', array(
            'Label' => _('Тьютор'),
            'Required' => false,
            'Validators' => array(
                'Int',
            ),
            'Filters' => array(
                'Int'
            ),
            'MultiOptions' => $teachers
        ));

        $this->addElement('RadioGroup', 'switch', array(
            'Value' => 0,
            'MultiOptions' => array(
                0 => _('Назначить всем слушателям курса'),
                1 => _('Выбрать из списка слушателей'),
                2 => _('Выбрать из списка групп/подгрупп')),
            'form' => $this,
            'dependences' => array(
                1 => array('students'),
                2 => array('subgroups')
            )
        ));

        $this->addElement($this->getDefaultCheckboxElementName(), 'all', array(
            'Label' => _('Автоматически назначать всем новым слушателям курса'),
            'Required' => false,
            'Value' => 1,
        ));

        $students = array();
        $this->addElement($this->getDefaultMultiSelectElementName(), 'students',
            array(
                'Label' => '',
                'Required' => false,
                'Validators' => array(
                    'Int'
                ),
                'Filters' => array(
                    'Int'
                ),
                'remoteUrl' => $this->getView()->url(array('module' => 'lesson', 'controller' => 'ajax', 'action' => 'students-list')),
                'multiOptions' => $students
            )
        );

        $groups = $this->getService('Group')->fetchAll(array('cid = ?' => $this->getParam('subject_id', 0)));
        $studygroups = $this->getService('StudyGroupCourse')->getCourseGroups($this->getParam('subject_id', 0));
        $groupsSelect = array();

        if ($studygroups) {
            $groupsSelect[] =  _('---выберите группу---');
            foreach ($studygroups as $studygroup) {
                $groupsSelect['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }

        if (count($groups)) {
            $groupsSelect[] =  _('---выберите подгруппу---');
            foreach ($groups as $item) {
                $groupsSelect['s_'.$item->gid] = $item->name;
            }
        }

        $this->addElement($this->getDefaultSelectElementName(), 'subgroups',
            array(
                'Label' => '',
                'multiOptions' => $groupsSelect
            )
        );


        $this->addDisplayGroup(
            array(
                'switch',
                'students',
                'subgroups',
                'all',
                'teacher',
            ),
            'lesson-assign',
            array('legend' => '')
        );

        if($lesson) {
            $currentBaseType = $lessonService->getLessonTool($lesson->typeID);
            if(HM_Event_EventModel::TYPE_TASK == $currentBaseType) {
                if(HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL == $lesson->getAssignType()) {
                    $variants = $this->getService('TaskVariant')->fetchAll(['task_id=?' => $lesson->material_id])->getList('variant_id', 'name');
                    $studentsRows = $this->getService('User')->fetchAllDependenceJoinInner('Lesson_Assign', ['Lesson_Assign.SHEID = ?' => $lesson->SHEID]);
                    $students = [];
                    foreach ($studentsRows as $studentRow) {
                        $students[$studentRow->MID] = $studentRow->getName();
                    }

                    $this->addElement($this->getDefaultMultiSetElementName(), 'variants', [
                        'Required' => false,
                        'dependences' => array(
                            new HM_Form_Element_Vue_Select(
                                'student',
                                array(
                                    'Label' => _('Слушатель'),
                                    'multiOptions' => $students,
                                )
                            ),
                            new HM_Form_Element_Vue_Select(
                                'variant',
                                array(
                                    'Label' => _('Вариант'),
                                    'multiOptions' => $variants,
                                )
                            ),
                        )
                    ]);

                    $this->addDisplayGroup(
                        array(
                            'variants',
                        ),
                        'lesson-variants',
                        array('legend' => 'Назначение вариантов слушателям')
                    );
                }
            }
        }




        $this->addElement($this->getDefaultSubmitElementName(), 'submit',[
            'label' => _('Сохранить'),
        ]);

        $this->addElement($this->getDefaultSubmitElementName(), 'submit_and_redirect', [
            'label' => _('Сохранить и перейти...'),
            'redirectUrls' => [
                [
                    'label' => _('к настройкам занятия'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'lesson',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ]),
                ],
                [
                    'label' => _('к редактированию материала'),
                    'url' => $this->getView()->url([
                        'module' => 'subject',
                        'controller' => 'material',
                        'action' => 'edit',
                        'subject_id' => $subjectId,
                    ]). '?returnUrl='. urlencode($this->getRequest()->getServer('REQUEST_URI')),
                ],
            ]
        ]);

        $this->addElement($this->getDefaultSubmitLinkElementName(), 'cancel', array(
            'Label' => _('Отмена'),
            'url' => $this->getView()->url(array(
                'module' => 'subject',
                'controller' => 'lessons',
                'action' => 'edit',
                'subject_id' => $subjectId,
            ))
        ));

        parent::init(); // required!
	}

}
