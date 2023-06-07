<?php
$out = '
<div id="schedule-daily">
<div id="schedule-daily-wrapper">';

//Набор статусов задания (определён в HM_Task_Conversation_ConversationModel)
$formTitles = HM_Task_Conversation_ConversationModel::getTypes();

if ($this->empty) {
    $out .= _('Отсутствуют данные для отображения');
} else {

    $tasks = $questions = $answers = $tests = $conditions = $balls = $totalforall = 0;

    $out .= '
        <table width="100%" class="main" cellspacing="0">
            <tr>
                <th>' . _('Название') . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TASK] . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_QUESTION] . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ANSWER] . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_TO_PROVE] . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_REQUIREMENTS] . '</th>
                <th>' . $formTitles[HM_Task_Conversation_ConversationModel::MESSAGE_TYPE_ASSESSMENT] . '</th>
                <th>' . _('Всего') . '</th>
            </tr>';

    foreach ($this->subjects as $s) {

        // временные переменные для подсчета количества вариантов в данном статусе
        $task = $question = $answer = $test = $condition = $ball =
        $totalforcourse = //сумма  по строкам для каждого учебного курса
        $totalforlesson = 0; //сумма  по строкам для каждого занятия
        $lessons = '';

        foreach ($s['lessons'] as $l) {

            $totalforlesson = array_sum(array($l['task'], $l['question'], $l['answer'], $l['test'], $l['condition'], $l['ball']));
            $totalforcourse += $totalforlesson;
            $task += $l['task'];
            $question += $l['question'];
            $answer += $l['answer'];
            $test += $l['test'];
            $condition += $l['condition'];
            $ball += $l['ball'];

            $lessons .= '
                <tr>
                    <td class="task_lesson">' . $l['schetitle'] . '</td>
                    <td>' . $l['task'] . '</td>
                    <td>' . $l['question'] . '</td>
                    <td>' . $l['answer'] . '</td>
                    <td>' . $l['test'] . '</td>
                    <td>' . $l['condition'] . '</td>
                    <td>' . $l['ball'] . '</td>
                    <td><strong><a href="' . $l['url'] . '">' . $totalforlesson . '</a></strong></td>
                </tr>';
        }

        $courses = '
            <tr class="task_course">
                <td class="task_course">' . $s['subname'] . '</td>
                <td>' . $task . '</td>
                <td>' . $question . '</td>
                <td>' . $answer . '</td>
                <td>' . $test . '</td>
                <td>' . $condition . '</td>
                <td>' . $ball . '</td>
                <td>' . $totalforcourse . '</td>
            </tr>';

        $tasks += $task;
        $questions += $question;
        $answers += $answer;
        $tests += $test;
        $conditions += $condition;
        $balls += $ball;
        $totalforall += $totalforcourse; // сумма по столбцам.
        $out .= $courses . $lessons;
    }

    $out .= '
        <tr>
            <td><strong>' . _('Всего') . '</strong></td>
            <td><strong>' . $tasks . '</strong></td>
            <td><strong>' . $questions . '</strong></td>
            <td><strong>' . $answers . '</strong></td>
            <td><strong>' . $tests . '</strong></td>
            <td><strong>' . $conditions . '</strong></td>
            <td><strong>' . $balls . '</strong></td>
            <td><strong>' . $totalforall . '</strong></td>
        </tr>';
    $out .= '</table>';

}
$out .= '
</div>
</div>';
echo $out;
?>