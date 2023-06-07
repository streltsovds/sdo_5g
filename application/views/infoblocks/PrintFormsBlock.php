<?php

// DEPRECATED!!!

class HM_View_Infoblock_PrintFormsBlock extends HM_View_Infoblock_Abstract
{
    // Универсальная гармонь с печатными формами, реализация зависит от $options['type']
    public function printFormsBlock($param = null)
    {
        $type = $options['type'];
        $object = $options['object'];

        switch($type) {
            case 'adaptation':
                $links = array(
                    array(
                        'title'=>_('Шаблон плана адаптации'),
                        'url'=> $this->view->url(array(
                            'module' => 'newcomer',
                            'controller' => 'list',
                            'action' => 'print-forms',
                            'newcomer_id' => $object->newcomer_id,
                            'baseUrl' => 'recruit',
                ))));      
            break;
            case 'rotation':
                if ($options['object']->state_id < 3) {
                    $links = array(
                        array(
                            'title'=>_('Шаблон индивидуальной программы ротации'),
                            'url'=> $this->view->url(
                                array(
                                    'module' => 'rotation',
                                    'controller' => 'list',
                                    'action' => 'print-forms',
                                    'rotation_id' => $object->rotation_id,
                                    'type' => 'plan',
                                    'baseUrl' => 'hr',
                                )
                            )
                        ),
                    );
                } else {
                    $links = array(
                        array(
                            'title'=>_('Шаблон отчёта о ротации'),
                            'url'=> $this->view->url(
                                array(
                                    'module' => 'rotation',
                                    'controller' => 'list',
                                    'action' => 'print-forms',
                                    'rotation_id' => $object->rotation_id,
                                    'type' => 'report',
                                    'baseUrl' => 'hr',
                                )
                            )
                        )
                    );
                }
                break;
            case 'reserve':
                if ($options['object']->state_id < 3) {
                    $links = array(
                        array(
                            'title'=>_('Индивидуальная программа развития'),
                            'url'=> $this->view->url(
                                array(
                                    'module' => 'reserve',
                                    'controller' => 'list',
                                    'action' => 'print-forms',
                                    'reserve_id' => $object->reserve_id,
                                    'type' => 'plan',
                                    'baseUrl' => 'hr',
                                )
                            )
                        ),
                    );
                } else {
                    $links = array(
                        array(
                            'title'=>_('Отчёт о прохождении ИПР'),
                            'url'=> $this->view->url(
                                array(
                                    'module' => 'reserve',
                                    'controller' => 'list',
                                    'action' => 'print-forms',
                                    'reserve_id' => $object->reserve_id,
                                    'type' => 'report',
                                    'baseUrl' => 'hr',
                                )
                            )
                        )
                    );
                }
                break;
            case 'session':
                $links = array(
                    array(
                        'body'=>"План обучения, повышения квалификации и обязательной аттестации пользователей<br><br>
                            <a href='".$this->view->url(array(
                                'module' => 'session',
                                'controller' => 'list',
                                'action' => 'plan',
                                'type' => 'word',
                                'session_id' => $object->session_id,
                                'baseUrl' => 'tc',
                            ))."'><img src='/images/icons/word.gif' border=0> в формате MS Word</a><br> 
                            <a href='".$this->view->url(array(
                                'module' => 'session',
                                'controller' => 'list',
                                'action' => 'plan',
                                'type' => 'excel',
                                'session_id' => $object->session_id,
                                'baseUrl' => 'tc',
                            ))."'><img src='/images/icons/excel.gif' border=0> в формате MS Excel</a>",
                    ),
                );
            break;
            case 'session-quarter':
                $links = array(
                    array(
                        'body'=>"План-отчет об обучении, повышении квалификации и обязательной аттестации пользователей<br><br>
                            <a href='".$this->view->url(array(
                                'module' => 'session-quarter',
                                'controller' => 'list',
                                'action' => 'plan-report',
                                'type' => 'word',
                                'session_id' => $object->session_id,
                                'baseUrl' => 'tc',
                            ))."'><img src='/images/icons/word.gif' border=0> в формате MS Word</a><br>
                            <a href='".$this->view->url(array(
                                'module' => 'session-quarter',
                                'controller' => 'list',
                                'action' => 'plan-report',
                                'type' => 'excel',
                                'session_id' => $object->session_id,
                                'baseUrl' => 'tc',
                            ))."'><img src='/images/icons/excel.gif' border=0> в формате MS Excel</a>",
                    ),
                );
            break;
            case 'subject':
                if (in_array($this->getService('User')->getCurrentUserRole(), array(
                    HM_Role_Abstract_RoleModel::ROLE_DEAN,
                    HM_Role_Abstract_RoleModel::ROLE_DEAN_LOCAL,
                    HM_Role_Abstract_RoleModel::ROLE_HR,
                    HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL
                ))) {
                    $links = array(
                        array(
                            'title'=>'Журнал учёта посещаемости и проведения производственной подготовки',
                            'url'=> $this->view->url(array(
                                'module' => 'subject',
                                'controller' => 'fulltime',
                                'action' => 'journal',
                                'session_id' => $object->subject_id,
                                'baseUrl' => 'tc',
                            ))
                        ),
                    );
                } elseif (
                    in_array($this->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY, HM_Role_Abstract_RoleModel::ROLE_LABOR_SAFETY_LOCAL)) &&
                    (in_array($object->subid, HM_Subject_SubjectModel::getBuiltInCourses()) || in_array($object->base_id, HM_Subject_SubjectModel::getBuiltInCourses()))
                ) {
                    $links = array(
                        array(
                            'title'=>'Протокол',
                            'url'=> $this->view->url(array(
                                'module' => 'subject',
                                'controller' => 'fulltime',
                                'action' => 'protocol',
                                'session_id' => $object->subject_id,
                                'baseUrl' => 'tc',
                            ))
                        ),
                    );
                } else {
                    return null;
                }
            break;
        }

        $content = array();
        foreach($links as $link) {
            $body = isset($link['body']) ? $link['body'] : "<a href=\"{$link['url']}\"'>{$link['title']}</a>";
            $content[] = "<div style='padding: 20px;'>{$body}</div>";        
        }
        $content = implode("\n", $content);

        //???

        return $this->render($content);

    }
}