<div class="at-form-report user-report">
    <v-card class="report-summary user-report__wrap">
        <v-layout row wrap>
            <v-flex xs8 sm8 md8 class="pa-0">
                <v-card-text class="pa-0">
                    <h2><?php echo $this->user->getName();?></h2>
                    <?php echo $this->reportList($this->lists['general']);?>
                </v-card-text>
            </v-flex>
            <v-flex xs4 sm4 md4 class="mt-10 pa-0">
                <v-card-text class="pa-0">
                    <?php if (!$this->print && !empty($this->resume)):?>
                        <hm-download-btn
                                text='<?php echo _("Скачать резюме")?>'
                                url='<?php echo $this->url(array("controller" => "index", "action" => "resume-download", "user_id" => $this->user->MID))?>'
                                name='resume'
                        ></hm-download-btn>
                    <?php endif;?>
                </v-card-text>
            </v-flex>
        </v-layout>
    </v-card>
    <div class="pagebreak"></div>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_HR, HM_Role_Abstract_RoleModel::ROLE_HR_LOCAL))) :?>

        <?php if ($this->tables['resumes']): ?>
            <v-card class="report-summary">
                <v-card-title class="headline"><?php echo _('История подбора');?></v-card-title>
                <v-card-text><?php echo $this->reportTable($this->tables['resumes']);?></v-card-text>
            </v-card>
            <?php if ($this->fromEstaff): ?>
                <v-card class="report-summary">
                    <v-card-title class="headline"><?php echo _('Данные из E-Staff');?></v-card-title>
                    <v-card-text>
                        <table>
                            <thead>
                            <tr>
                                <th>Название вакансии</th>
                                <th>Статус вакансии</th>
                                <th>Дата создания</th>
                                <th>Дата статуса</th>
                                <th>Резюме</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="5" bgcolor="#faebd7" >Данные E-Staff</td>
                            </tr>
                            <?php foreach ($this->userSpots as $spot): ?>
                            <tr>
                                <td><?= $spot->vacancy_name; ?></td>
                                <td><?= $spot->state_id; ?></td>
                                <td><?= $spot->start_date; ?></td>
                                <td><?= $spot->state_date; ?></td>
                                <td>
                                    <a href="#" onclick="window.open('<?php echo $this->url(array('module' => 'user', 'controller' => 'report','action' => 'resume', 'user_id' => $spot->user_id, 'spot_id' => $spot->spot_id, 'ajax' => 1));?>','new','width=800,height=600,left=350,top=150,toolbar=1')">
                                        Резюме
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </v-card-text>
                </v-card>
            <?php endif;?>
        <?php endif;?>
    <?php endif;?>

    <?php if ($this->position && (count($this->tables['absences']) > 1) /* Нет даных - не выводим блок */): ?>
        <div class="pagebreak"></div>
        <v-card class="report-summary">
            <v-card-title class="headline"><?php echo _('Периоды отсутствия на рабочем месте');?></v-card-title>
            <v-card-text>
                <?php echo $this->reportTable($this->tables['absences']);?>
            </v-card-text>
        </v-card>
    <?php endif;?>
    <?php echo $this->reportTable($this->tables['resumes']);?>
    <?php if (Zend_Registry::get('serviceContainer')->getService('Acl')->inheritsRole(Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserRole(), array(HM_Role_Abstract_RoleModel::ROLE_ATMANAGER, HM_Role_Abstract_RoleModel::ROLE_ATMANAGER_LOCAL))) :?>
        <v-card class="report-summary">
            <v-card-title class="headline"><?php echo _('История оценки');?></v-card-title>
            <v-card-text><?php echo $this->reportTable($this->tables['sessionResults']);?></v-card-text>
        </v-card>
    <?php endif;?>
</div>

