<div class="hm-alert">
    <v-alert
            type="<?php echo $this->errorType == 500 ? 'error' : 'warning'; ?>"
            value="true"
            class="hm-alert-<?php echo $this->env ?>"
    >
        <?php
        if ('development' != APPLICATION_ENV || Zend_Registry::get('config')->debug == 0):?>

            <?php if ($this->errorType == 403): ?>
                <h2 class="headline">
                    <?php echo _('Недостаточно прав'); ?>
                </h2>
                <p class="body-2">
                    <?php echo _('У Вас недостаточно прав для просмотра данной страницы. Это могло произойти по следующим причинам:'); ?>
                </p>

                <ul class="body-2">
                    <li class="body-2">
                        <?php echo _('Вы долгое время не совершали никаких действий в системе и время сессии закончилось;'); ?>
                    </li>
                    <li class="body-2">
                        <?php echo _('Вы перешли по прямой ссылке (например, сохраненной в закладках) и еще не авторизовались;'); ?>
                    </li>
                </ul>
                <br>
                <p class="body-2">
                    <?php echo _('Вы можете авторизоваться заново и продолжить работу с системой.'); ?>
                </p>
                <v-btn color="secondary" dark @click="window.location.href='<?php echo Zend_Registry::get('config')->url->base; ?>'">
                    <?php echo _('Продолжить'); ?>
                </v-btn>

            <?php elseif ($this->errorType == 404): ?>
                <h2 class="headline">
                    <?php echo _('Страница не найдена'); ?>
                </h2>
                <p class="body-2">
                    <?php echo _('Запрашиваемая Вами страница не найдена. Это могло произойти по следующим причинам:'); ?>
                </p>

                <ul class="body-2">
                    <li class="body-2">
                        <?php echo _('Вы перешли по ссылке на учебный материал, который был физически удалён на сервере;'); ?>
                    </li>
                    <li class="body-2">
                        <?php echo _('неверно настроены права доступа к файлам на файловой системе сервера;'); ?>
                    </li>
                    <li class="body-2">
                        <?php echo _('неверно установлены ссылки в содержимом учебного модуля (информационного ресурса), либо он вообще не рассчитан на работу с данным типом файловой системы;'); ?>
                    </li>
                </ul>
                <br>
                <p class="body-2">
                    <?php echo _('Вы можете сообщить об этой проблеме системному администратору и продолжить работу с системой.'); ?>
                </p>
                <v-btn color="secondary" dark href="<?php echo Zend_Registry::get('config')->url->base; ?>">
                    <?php echo _('Продолжить'); ?>
                </v-btn>

            <?php elseif ($this->errorType == 500): ?>
                <h2 class="headline">
                    <?php echo _('Нештатная ситуация'); ?>
                </h2>
                <p class="body-2">
                    <?php echo _('В ходе работы программы произошла нештатная ситуация. '); ?>
                </p>
                <?php if (Zend_Registry::get('serviceContainer')->getService('User')->isRoleExists(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId(), HM_Role_Abstract_RoleModel::ROLE_ADMIN)): ?>
                    <p class="body-2">
                        <?php echo sprintf(_('Вы можете отправить<br>%s о случившемся в службу технической поддержки и продолжить работу с системой.'), '<v-btn class="hm-alert-report-link" href="' . $this->url(array('module' => 'file', 'controller' => 'get', 'action' => 'log')) . '" target="_blank">' . _('отчет') . '</v-a>'); ?>
                    </p>
                <?php endif; ?>
                <v-btn class="hm-alert-continue-link" href="<?php echo Zend_Registry::get('config')->url->base; ?>"><?php echo _('Продолжить'); ?></v-btn>
            <?php endif; ?>

        <?php endif; ?>

        <?php if ('development' == APPLICATION_ENV || Zend_Registry::get('config')->debug): ?>
            <v-layout column>
                <v-flex>
                    <h3 class="headline">
                        <?= _('Информация об ошибке') ?>:
                    </h3>
                    <h4 class="subheading">
                        <?= _('Текст ошибки') ?>:
                    </h4>
                    <code>
                        <?= $this->exception->getMessage() ?>
                    </code>
                </v-flex>
                <v-flex>
                    <h4 class="subheading">
                        <?= _('Подробнее') ?>:
                    </h4>
                    <code>
                        <?= $this->exception->getTraceAsString() ?>
                    </code>
                </v-flex>
                <v-flex>
                    <h4 class="subheading">
                        <?= _('Параметры запроса') ?>:
                    </h4>
                    <code>
                        <?php var_dump($this->request->getParams()) ?>
                    </code>
                </v-flex>
            </v-layout>
        <?php endif; ?>
    </v-alert>
</div>
