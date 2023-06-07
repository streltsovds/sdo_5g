<v-card style="position: relative;">
    <div style="padding: 20px;">

        <?php echo $this->model->getIconHtml();?>
        <a href="<?php echo $this->url(['baseUrl' => 'recruit', 'module' => 'vacancy', 'controller' => 'list', 'action' => 'edit', 'vacancy_id' => $this->model->vacancy_id]);?>">редактировать</a><br>
        <a href="<?php echo $this->url(['baseUrl' => 'recruit', 'module' => 'vacancy', 'controller' => 'index', 'action' => 'programm', 'vacancy_id' => $this->model->vacancy_id]);?>">редактировать программу</a>

        <h3><?php echo _('Вакансии на внешних ресурсах');?></h3>
        <ul>
            <?php if (Zend_Registry::get('config')->vacancy->hh->enabled):?>
                <?php if (!$this->model->hh_vacancy_id) : ?>
                    <li>
                        <a href="<?php echo $this->url(['module' => 'vacancy', 'controller' => 'hh', 'action' => 'index', 'vacancy_id' => $this->model->vacancy_id])?>"
                            <?php if($this->model->status==HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED) echo 'disabled';?>>
                            <?php echo _('Опубликовать на HeadHunter')?>
                        </a>
                    </li>
                <?php else:?>
                    <li>
                        <a href="http://hh.ru/vacancy/<?php echo $this->model->hh_vacancy_id ?>" target="_blank">
                            <?php echo _('Просмотреть на HeadHunter')?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo $this->url(['module' => 'vacancy', 'controller' => 'list', 'action' => 'load-new-resumes-by', 'vacancy_id' => $this->model->vacancy_id])?>">
                            <?php echo _('Загрузить отклики');?>
                        </a>
                    </li>
                    <li>
                        <a href="#" id="archive_vacancy_hh">
                            <?php echo _('Архивировать');?>
                        </a>
                    </li>
                <?php endif;?>
            <?php endif;?>
            <?php if (Zend_Registry::get('config')->vacancy->superjob->enabled):?>
                <?php if (!$this->model->superjob_vacancy_id) : ?>
                    <li>
                        <a href="<?php echo $this->url(['module' => 'vacancy', 'controller' => 'superjob', 'action' => 'index', 'vacancy_id' => $this->model->vacancy_id])?>"
                            <?php if($this->model->status==HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED) echo 'disabled';?>>
                            <?php echo _('Опубликовать на SuperJob')?>
                        </a>
                    </li>
                <?php else:?>
                    <li>
                        <a href="https://www.superjob.ru/vakansii/vacancy-<?php echo $this->model->superjob_vacancy_id ?>.html">
                            <?php echo _('Просмотреть на SuperJob')?>
                        </a>
                    </li>
                <?php endif;?>
            <?php endif;?>

            <?php if (
                (Zend_Registry::get('config')->vacancy->hh->enabled || Zend_Registry::get('config')->vacancy->superjob->enabled) &&
                ($this->model->hh_vacancy_id || $this->model->superjob_vacancy_id)
            ): ?>
                <li>
                    <a href="<?php echo $this->url(['module' => 'vacancy', 'controller' => 'all', 'action' => 'responses', 'vacancy_id' => $this->model->vacancy_id])?>">
                        <?php echo _('Отклики on-line');?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <?php
        echo $this->calendar(
            $this->url(array('module'=>'candidate', 'controller'=>'calendar', 'action'=>'all', 'no_user_events' => 'y')),
            array(
                'abstract'                => false,
                'editable'                => false,
            ));
        ?>

        <?php echo $this->workflow($this->model);?>

    </div>
</v-card>


<?php $this->inlineScript()->captureStart(); ?>

    $('#archive_vacancy_hh').on('click', function() {
        elsHelpers.confirm(HM._('Уверены, что хотите архивировать вакансию на hh.ru?'), HM._('Подтверждение')).done(function () {
            $.ajax({
                type: 'post',
                url: '/recruit/vacancy/hh/archive-vacancy/vacancy_id/<?php echo $this->model->vacancy_id; ?>',
                success: function(data) {
                    if (data === '1') {
                        elsHelpers.alert(HM._('Вакансия успешно отправлена в архив'), '').always(function() {
                            window.location = window.location; // перезагружаем страницу
                        });
                    } else {
                        elsHelpers.alert(HM._('Произошла непредвиденная ошибка!'), '');
                    }
                }
            });
        });
    });

<?php $this->inlineScript()->captureEnd(); ?>
