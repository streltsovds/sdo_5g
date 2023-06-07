    <div class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" role="tabpanel" style="display: block; overflow: auto; padding-top: 0px; padding-bottom: 0px;">
        <div class="ui-accordion-content-wrapper">
            <?php if (Zend_Registry::get('config')->vacancy->hh->enabled):?>
                <ul class="navigation">
                    <?php if (!$this->vacancy->hh_vacancy_id) : ?>

                        <li>
                            <ul>
                                <li>
                                    <a href="#" id="publish_vacancy_hh" <?php if($this->vacancy->status==HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED) echo 'disabled';?>><?php echo _('Публикация на HeadHunter')?></a>
                                </li>
                            </ul>
                        </li>

                    <?php else:?>
                        <li>
                            <ul>
                                <li>
                                    <a href="#" id="show_vacancy_hh"><?php echo _('Просмотр на HeadHunter')?></a>
                                </li>
                                <li>
                                    <a href="#" id="archive_vacancy_hh"><?php echo _('Архивация вакансии');?></a>
                                </li>
                            </ul>
                        </li>

                    <?php endif;?>
                </ul>
            <?php endif;?>
            <?php if (Zend_Registry::get('config')->vacancy->superjob->enabled):?>
                <ul class="navigation">
                    <?php if (!$this->vacancy->superjob_vacancy_id) : ?>

                        <li>
                            <ul>
                                <li>
                                    <a href="#" id="publish_vacancy_sj" <?php if($this->vacancy->status==HM_Recruit_Vacancy_VacancyModel::STATE_CLOSED) echo 'disabled';?>><?php echo _('Публикация на SuperJob')?></a>
                                </li>
                            </ul>
                        </li>

                    <?php else:?>
                        <li>
                            <ul>
                                <li>
                                    <a href="#" id="show_vacancy_sj"><?php echo _('Просмотр на SuperJob')?></a>
                                </li>
                            </ul>
                        </li>

                    <?php endif;?>
                </ul>
            <?php endif;?>

            <?php if (
                (Zend_Registry::get('config')->vacancy->hh->enabled || Zend_Registry::get('config')->vacancy->superjob->enabled) &&
                ($this->vacancy->hh_vacancy_id || $this->vacancy->superjob_vacancy_id)
            ): ?>
                <ul class="navigation">
                    <!--
                    <li>
                        <ul>
                            <li>
                                <a href="/recruit/vacancy/all/loaded-responses/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>">Загруженные отклики</a>
                            </li>
                        </ul>
                    </li>
                    -->
                    <li>
                        <ul>
                            <li>
                                <a href="/recruit/vacancy/all/responses/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>">Отклики on-line</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
<!--</div>-->

<?php $this->inlineScript()->captureStart(); ?>
    /*
    $('#show_response_hh').on('click', function() {
        document.location.href = '/recruit/vacancy/hh/responses/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>';
    });
    */  
    $('#publish_vacancy_hh').on('click', function() {
        document.location.href = '/recruit/vacancy/hh/index/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>';
    });
    
    $('#show_vacancy_hh').on('click', function() {
        window.open('http://hh.ru/vacancy/<?php echo $this->vacancy->hh_vacancy_id ?>');
    });
    
    $('#archive_vacancy_hh').on('click', function() {
        elsHelpers.confirm(HM._('Уверены, что хотите архивировать вакансию на hh.ru?'), HM._('Подтверждение')).done(function () {
            $.ajax({
                type: 'post',
                url: '/recruit/vacancy/hh/archive-vacancy/vacancy_id/<?php echo $this->vacancy->vacancy_id; ?>',
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
    
    
    /*
    $('#show_response_sj').on('click', function() {
        document.location.href = '/recruit/vacancy/superjob/responses/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>';
    });
    */
    $('#publish_vacancy_sj').on('click', function() {
        document.location.href = '/recruit/vacancy/superjob/index/vacancy_id/<?php echo $this->vacancy->vacancy_id ?>';
    });
    
    $('#show_vacancy_sj').on('click', function() {
        window.open('https://www.superjob.ru/vakansii/vacancy-<?php echo $this->vacancy->superjob_vacancy_id ?>.html');
    });
    
    /*
    $('#archive_vacancy_sj').on('click', function() {

        elsHelpers.confirm(HM._('Уверены, что хотите архивировать вакансию на hh.ru?'), HM._('Подтверждение')).done(function () {
            $.ajax({
                type: 'post',
                url: '/recruit/vacancy/hh/archive-vacancy/vacancy_id/<?php echo $this->vacancy->vacancy_id; ?>',
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
    */
    
    
<?php $this->inlineScript()->captureEnd(); ?>    