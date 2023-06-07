<?php

class HM_View_Sidebar_Course extends HM_View_Sidebar_Abstract
{
    function getIcon()
    {
        return 'Material'; // @todo
    }

    public function getTitle()
    {
        return 'Материалы';
    }

    function getContent()
    {
        $data = [];
        $course = $this->getModel();

        /** @var HM_Acl $aclService */
        $aclService = $this->getService('Acl');
        $isTeacherOrDean = $aclService->checkRoles([
            HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            HM_Role_Abstract_RoleModel::ROLE_DEAN,
            HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
        ]);

        if ($isTeacherOrDean) {
            $currentUserRole = $this->getService('User')->getCurrentUserRole();
            if ($aclService->isAllowed($currentUserRole, sprintf('mca:%s:%s:%s', 'kbase', 'course', 'edit-card'))) {

                $data['editUrl'] = $this->view->url([
                    'module' => 'kbase',
                    'controller' => 'course',
                    'action' => 'edit-card',
                    'CID' => $course->CID
                ], null, true);
            }

            if (0 && $course->format == HM_Course_CourseModel::FORMAT_FREE) {
                if ($aclService->isAllowed($currentUserRole, sprintf('mca:%s:%s:%s', 'course', 'constructor', 'index'))) {

                    $data['editUrl'] = $this->view->url([
                        'module' => 'course',
                        'controller' => 'constructor',
                        'action' => 'index',
                        'CID' => $course->CID
                    ], null, true);
                }
            } else {
                if ($aclService->isAllowed($currentUserRole, sprintf('mca:%s:%s:%s', 'kbase', 'course', 'import'))) {

                    $data['editUrl'] = $this->view->url([
                        'module' => 'kbase',
                        'controller' => 'course',
                        'action' => 'import',
                        'CID' => $course->CID
                    ], null, true);
                }
            }

            $data['editMaterialUrl'] = $this->view->url([
                'module' => 'kbase',
                'controller' => 'course',
                'action' => 'import',
                'edition' => 1,
                'course_id' => $course->CID,
            ], null, true);
        }

        $data['courseTags'] = $this->getService('Tag')->getTags($course->CID, HM_Tag_Ref_RefModel::TYPE_COURSE);
        $data['courseClassifiers'] = $this->getService('Classifier')->getItemClassifiers($course->CID, HM_Classifier_Link_LinkModel::TYPE_COURSE)->asArrayOfArrays();
        $course->description = $course->Description;
        unset($course->Description);
        $data['course'] = $course;

        $data = HM_Json::encodeErrorSkip($data);

        return $this->view->partial('course.tpl', ['data' => $data]);
    }
}