<?php
/** @var HM_ACl $acl */
$acl = Zend_Registry::get('serviceContainer')->getService('Acl'); ?>

    <script>
        function updateAdaptationTime() {
            $.ajax({
                type: 'post',
                url: '<?php echo $this->url(array('module' => 'profile', 'controller' => 'criterion', 'action' => 'update-adaptation-time'))?>',
            });
        }
    </script>

    <div class="at-form-report">
        <v-card class="report-summary profile-report__wrap">

            <v-layout row wrap>
                <v-flex xs8 sm8 md8 class="pa-0">
                    <v-card-text class="pa-0">
                        <h2>
                            <?php echo _('Общая информация') ?>
                            <!-- <?php // if ($this->editable): ?>в)
                <a  href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'index', 'action' => 'edit')) ?>">
<svg-icon name="edit" width="18" height="18">
                                        <?php echo $this->svgIcon('edit'); ?>
</a>
            <?php // endif; ?> -->
                        </h2>
                        <?php echo $this->reportList($this->lists['general']); ?>
                    </v-card-text>
                </v-flex>

                <v-flex xs4 sm4 md4 class="mt-10 pa-0">
                    <v-card-text class="pa-0">
                        <?php if ($this->print): ?>
                            <h1><?php // Сейчас print вообще не работает, разметка здесь может поползти
                                echo $this->profile->name; ?>
                            </h1>
                        <?php endif; ?>
                    </v-card-text>
                </v-flex>
            </v-layout>

            <v-layout row wrap>
                <v-flex xs6 sm6 md6 class="pa-0">
                    <v-card-text class="pa-0">
                        <h2>
                            <?php echo _('Формальные требования') ?>

                            <?php if ($this->editable): ?>
                                <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'index', 'action' => 'requirements')) ?>">
                                    <svg-icon name="edit" width="18" height="18">
                                </a>

                            <?php endif; ?>
                        </h2>

                        <?php echo $this->reportList($this->lists['requirements-1']); ?>
                    </v-card-text>
                </v-flex>

                <v-flex xs6 sm6 md6 class="mt-10 pa-0">
                    <v-card-text class="pa-0">
                        <?php echo $this->reportList($this->lists['requirements-2']); ?>
                    </v-card-text>
                </v-flex>
            </v-layout>

            <!--v-layout row wrap>
                <v-flex xs12 sm12 md12 class="pa-0">
                    <v-card-text class="pa-0">
                        <h2>
                            <?php echo _('Дополнительные требования'); ?>

                            <?php if ($this->editable): ?>
                                <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'index', 'action' => 'requirements')) ?>#misc">
                                    <svg-icon name="edit" width="18" height="18">
                                </a>
                            <?php endif; ?>
                        </h2>

                        <?php echo $this->reportList($this->lists['requirements-misc']); ?>
                    </v-card-text>
                </v-flex>
            </v-layout-->

            <br /><br />

            <!--v-layout row wrap>
                <v-flex xs12 sm12 md12 class="pa-0">
                    <v-card-text class="pa-0">
                        <h2>
                            <?php echo _('Требования по профстандартам'); ?>

                            <?php if ($this->editable): ?>
                                <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'index', 'action' => 'skills')) ?>">
                                    <svg-icon name="edit" width="18" height="18">
                                </a>
                            <?php endif; ?>
                        </h2>

                        <?php echo $this->reportTable($this->tables['skills']); ?>
                    </v-card-text>
                </v-flex>
            </v-layout-->

            <v-layout row wrap>
                <v-flex xs12 sm12 md12 class="pa-0">
                    <v-card-text class="pa-0">
                        <h2>
                            <?php echo _('Компетенции'); ?>

                            <?php if ($acl->isCurrentAllowed('mca:profile:criterion:corporate') && $this->editable): ?>
                                <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'criterion', 'action' => 'corporate')) ?>">
                                    <svg-icon name="edit" width="18" height="18">
                                </a>
                            <?php endif; ?>
                        </h2>

                        <?php echo $this->reportTable($this->tables['criteria']); ?>
                    </v-card-text>
                </v-flex>
            </v-layout>

            <?php if (!$this->isRemoved['At']):?>
                <?php if (!$this->isRemoved['AtTest']):?>
                    <v-layout row wrap>
                        <v-flex xs12 sm12 md12 class="pa-0">
                            <v-card-text class="pa-0">
                                <h2>
                                    <?php echo _('Квалификации'); ?>

                                    <?php if ($acl->isCurrentAllowed('mca:profile:criterion:professional') && $this->editable): ?>
                                        <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'criterion', 'action' => 'professional')) ?>">
                                            <svg-icon name="edit" width="18" height="18">
                                        </a>
                                    <?php endif; ?>
                                </h2>

                                <?php echo $this->reportTable($this->tables['criteria-test']); ?>
                            </v-card-text>
                        </v-flex>
                    </v-layout>
                <?php endif;?>

                <?php if (!$this->isRemoved['Recruit']):?>
                    <v-layout row wrap>
                        <v-flex xs12 sm12 md12 class="pa-0">
                            <v-card-text class="pa-0">
                                <h2>
                                    <?php echo _('Личностные характеристики'); ?>

                                    <?php if ($acl->isCurrentAllowed('mca:profile:criterion:personal') && $this->editable): ?>
                                        <a href="<?php echo $this->url(array('module' => 'profile', 'controller' => 'criterion', 'action' => 'personal')) ?>">
                                            <svg-icon name="edit" width="18" height="18">
                                        </a>
                                    <?php endif; ?>
                                </h2>

                                <?php echo $this->reportTable($this->tables['criteria-personal']); ?>
                            </v-card-text>
                        </v-flex>
                    </v-layout>
                <?php endif;?>

            <?php endif;?>

        </v-card>
    </div>

<?php if (!$this->editable): ?>
    <?php $this->inlineScript()->captureStart(); ?>
    $('.at-form-report .edit').css('display', 'none');
    <?php $this->inlineScript()->captureEnd(); ?>
<?php endif; ?>