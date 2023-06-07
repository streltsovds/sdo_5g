<?php
if ( count($this->links) > 0 ):
?>
    <table>
        <tr>
            <th><?php echo _('Наименование объекта');?></th>
            <?php
                foreach($this->questionList as $question):
            ?>
            <th><?php echo $question;?></th>
            <?php
                endforeach;
            ?>
            <th><?php echo _('Итоговое значение оценки');?></th>
        </tr>
        <?php
            foreach ($this->links as $link):
        ?>
        <tr>
            <td>
                <?php
                    if ($this->canViewDetail){
                        $url = $this->url(array(
                               'module'      => 'poll',
                               'controller'  => 'result',
                               'action'      => 'detail-rank',
                               'link_id'     => $link['link_id']),null,true);
                        echo '<a href="'.$url.'">'.$link['title'].'</a>';
                    }
                    else
                        echo $link['title'];
                ?>
            </td>
            <?php
                foreach($this->questionList as $questionId => $question):
            ?>
            <td><?php
               if ( count($this->questionUsers[$link['link_id']][$questionId]) && intval($this->answerWeight[$questionId]['balmax'])) {
                    $avg    = $this->questionBall[$link['link_id']][$questionId] / count($this->questionUsers[$link['link_id']][$questionId]);
                    $normal = $avg / intval($this->answerWeight[$questionId]['balmax']);
                    echo round($normal*100);
                }
            ?></td>
            <?php
                endforeach;
            ?>
            <td><?php echo $link['rank'];?></td>

        </tr>
        <?php
            endforeach;
        ?>
    </table>
<?php
else :
    echo _('Нет данных для отображения');
endif;
?>
