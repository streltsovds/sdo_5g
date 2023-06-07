<?php
class HM_Crontask_Task_Feedback extends HM_Crontask_Task_TaskModel  implements HM_Crontask_Task_Interface
{
    public function getTaskId()
    {
        return 'sendFeedbackMessage';
    }

    public function run()
    {
        $feedbackUsersService = Zend_Registry::get('serviceContainer')->getService('FeedbackUsers');
        $select = $feedbackUsersService->getSelect();
        $select->from(array('fu' => 'feedback_users'), array('fu.feedback_user_id', 'g.created', 'f.assign_days'))
            ->join(array('f' => 'feedback'), 'fu.feedback_id = f.feedback_id',  array())
            ->join(array('g' => 'graduated'), '(fu.user_id = g.MID AND f.respondent_type = 0) OR (fu.subordinate_id = g.MID AND f.respondent_type = 1)',  array())
            ->where('f.assign_type = ?', HM_Feedback_FeedbackModel::ASSIGN_AFTER_DAYS)
        ;

        // не хватает универсальных функций для работы с датами..
        // приходится средствами PHP
        $now = new HM_Date();
        if ($rowset = $select->query()->fetchAll()) {
            foreach ($rowset as $row) {
                if ($feedbackUser = $feedbackUsersService->getOne($feedbackUsersService->find($row['feedback_user_id']))) {

                    $date = new HM_Date($row['created']);
                    $date->add($row['assign_days'], Zend_Date::DAY);

                    // предполагаем, что крон не чаще, чем раз в день
                    // иначе будет много писем
                    if ($date->getDate()->get('YYYY-MM-dd') == $now->getDate()->get('YYYY-MM-dd')) {
                        $feedbackUsersService->notify($feedbackUser);
                    }
                }
            }
        }
    }
}
