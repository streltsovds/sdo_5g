<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<div class="tests_body">
   <div class="eLS-sortable-container">
        <div id="eLS-sortable-<?=$this->question->question_id;?>" class="eLS-sortable eLS-sortable-<?=$this->question->question_id;?>" data-questionid="<?=$this->question->question_id;?>" data-type="sorting">
            <?php $variants = $this->question->variants;
            $nvariants = array();
            if (is_array($this->result)) {
                foreach($this->result as $key => $val) {
                    foreach($variants as $variant) {
                        if (md5($variant->question_variant_id.HM_Quest_Question_Type_SortingModel::SALT) == $key) {
                            $nvariants[] = $variant;
                            break;
                        }
                    }
                }
                $variants = $nvariants;
            } else {
                shuffle($variants);
            }
            ?>
            <?php $i=1; ?>
            <?php foreach($variants as $variant):?>
            <div class="eLS-sortable-item" id="<?=md5($variant->question_variant_id.HM_Quest_Question_Type_SortingModel::SALT)?>"><input type="hidden" name="results[<?=$this->question->question_id;?>][<?=md5($variant->question_variant_id.HM_Quest_Question_Type_SortingModel::SALT)?>]" value="<?php echo $i++; ?>">
                <div class="eLS-sortable-item-container" style="zoom: 1;">
                    <table cellpadding="0" cellspacing="0" border="0" class="eLS-drag-handler">
                        <tr class="eLS-drag-handler_wrapper">
                            <td class="drag-handler_left"></td>
                            <td class="eLS-answer-variant"><?=$variant->variant?></td>
                            <td class="drag-handler_right"></td>
                        </tr>
                    </table>
                </div>
            </div>
              <?php endforeach; ?>
        </div>
    </div>
</div>
