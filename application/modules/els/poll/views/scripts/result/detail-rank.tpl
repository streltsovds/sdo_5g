<?php
if ( count($this->results) > 0 ):
?>
<table>
    <tr>
        <th><?php echo _('Пользователь');?></th>
        <?php
                foreach($this->questions as $question):
        ?>
        <th><?php echo $question['Title']?></th>
        <?php
                endforeach;
            ?>
    </tr>
    <?php
            foreach ($this->results as $key=>$resultItem):

    ?>
    <tr>
        <td>
            <?php echo $key; ?>
        </td>
        <?php
                foreach($this->questions as $questionId => $question):
        ?>
        <td><?php
                if (array_key_exists($questionId,$resultItem)){
                    if (is_array($resultItem[$questionId])){
                        foreach ($resultItem[$questionId] as $answer){
                                echo $question['answers'][$answer].'<br>';
                        }
                    }
                    else{
                        echo $question['answers'][$resultItem[$questionId]];
                    }
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