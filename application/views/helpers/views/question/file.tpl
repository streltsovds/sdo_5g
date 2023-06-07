<h4><?php echo sprintf(_('Вопрос №%s'), $this->params['number']);?></h4>
<h2><?php echo $this->question->question?></h2>
<?php // этот hidden нужно для обработки free_variant?>
<input type="hidden" name="results[<?php echo $this->question->question_id;?>]" value="<?php echo HM_Quest_Question_Variant_VariantModel::FREE_VARIANT;?>">
<table>
<tr class="<?= $this->cycle(array('odd', 'even'))->next() ?>">
    <td class="quest-file-variant"><?php $element = new HM_Form_Element_Html5File(
            $id = "results_{$this->question->question_id}", 
            array(
                'Label' => _('Файл'),
                'Destination' => Zend_Registry::get('config')->path->upload->tmp,
                'Description' => _('Для загрузки использовать файлы форматов: jpg, jpeg, png, gif. Максимальный размер файла &ndash; 10 Mb'),
                'Filters' => array('StripTags'),
                'file_size_limit' => 10485760,
                'file_types' => '*.jpg;*.png;*.gif;*.jpeg;*.mp3;*.wav;*.aac;*.ogg',
                'file_upload_limit' => 1,
            ));?>
            <?php $element->setDecorators(array ( 
                array('ViewHelper'),
                array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class'  => 'element')),
                array('Label', array('tag' => 'dt'))

        ));?>
        <?php echo $element;?>

        <?php if ($this->result): ?>
            <?php $url = Zend_Registry::get('view')->url(array('module' => 'file', 'controller' => 'get', 'action' => 'quest', 'attempt_id' => $this->model['attempt']['attempt_id'],  'question_id' => $this->question->question_id, 'baseUrl' => '')); ?>
             Загружен файл: <a href="<?php echo $url; ?>" target="_blank"><?php echo $this->result; ?></a>
        <?php endif; ?>
    </td>
</tr>
</table>