<?php if (is_array($this->data)):?>    <table class="resume-report-list resume-report-list-<?php echo $this->class?>">        <?php foreach ($this->data as $key => $value):?>            <tr>                <td class="resume-report-list-key"><?php echo $key?></td>                <td class="resume-report-list-value"><?php echo $value?></td>            </tr>        <?php endforeach;?>    </table><?php endif;?>