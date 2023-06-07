<?php
class HM_Extension_Remover_LearningRemover extends HM_Extension_Remover_Abstract
{
    public function init()
    {
        $this->setItemsToHide([
            'roles' => [
                HM_Role_Abstract_RoleModel::ROLE_DEAN,
                HM_Role_Abstract_RoleModel::ROLE_TEACHER,
            ],
            'classifierTypes' => [
                HM_Classifier_Link_LinkModel::TYPE_SUBJECT
            ],
            'columns' => [
                'courses',
            ],
            'elements' => [
                'regDeny',
                'contractOfferText',
            ],
            'domains' => [
                'StudyGeneral',
                'StudyDetailed',
                'StudyTests',
            ],
            'infoblocks' => [
                'courses',
                'claimsBlock',
                'topSubjectsBlock',
                'progressBlock',
                'ResourcesRatingBlock',
                'CoursesRatingBlock',
                'feedback',
            ],
            'massActions' => [
                [
                    'module' => 'orgstructure',
                    'controller' => 'list',
                    'action' => 'assign-programm'
                ],
                [
                    'module' => 'assign',
                    'controller' => 'student',
                    'action' => 'do-soids'
                ],
            ],
            'menu' => [
                'id' => [
                    'mca:assign:dean:index',
                ],
            ],
        ]);
    }
}