<?php
// @todo: autoload it
require_once APPLICATION_PATH . '/views/helpers/Score.php';

/** @var HM_Lesson_LessonModel_Interface $lesson */
/** @var HM_Lesson_Assign_AssignModel $lessonAssign */
?>
<?php // см. frontend/app/src/scss/user-lessons-plan.sass ?>

<div class="user-lessons-plan"
     :class="{
       'user-lessons-plan--breakpoint-sm-and-down': $vuetify.breakpoint.smAndDown
     }"
>
    <div v-if="view.lessonAssigns.length || view.sections.length">
        <div class="user-lessons-plan__progress">
            <span class="user-lessons-plan__progress-title">Прогресс прохождения</span>
            <div class="user-lessons-plan__progress-scale">
                <div class="user-lessons-plan__progress-scale-block">
                    <?php if ($this->subjectRoughProgress >= 55): ?>
                        <span style="color: #ffffff"><?php  echo $this->subjectRoughProgress ?>%</span>
                    <?php else :?>
                        <span style="color: #000000;"><?php  echo $this->subjectRoughProgress ?>%</span>
                    <?php endif; ?>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <div class="user-lessons-plan__progress-scale-block__progress progress-rought" v-on="on" :style="{width:<?php  echo $this->subjectRoughProgress ?>+'%'}"></div>
                        </template>
                        <span><?php echo _('Прогресс выполнения')?></span>
                    </v-tooltip>
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <div class="user-lessons-plan__progress-scale-block__progress progress-norought" v-on="on" :style="{width:<?php  echo $this->subjectProgress ?>+'%'}"></div>
                        </template>
                        <span><?php echo _('Прогресс выполнения с учетом SCORM')?></span>
                    </v-tooltip>
                </div>
            </div>
        </div>


        <hm-user-lessons-plan-wrapper :data="view.lessonAssigns" :sections="view.sections"
        />
    </div>

    <div v-else />
        <hm-empty empty-type="full" sub-label="<?php echo _('План занятий в курсе еще не создан менеджером по обучению');?>"/>
    </div>

</div>
