<template>
    <v-stepper :style="{background: themeColors.contentColor}" value="<?php echo $this->step;?>" class="elevation-0">
        <v-stepper-header>
            <v-stepper-step
                step="1"
                title="<?php echo _('На этом шаге создаётся или выбирается электронный учебный материал, используемый в занятии');?>"
            >
                <?php echo _('Материал')?>
            </v-stepper-step>

            <v-divider></v-divider>

            <v-stepper-step
                step="2"
                title="<?php echo _('На этом шаге настраиваются различные свойства занятия, напр. ограничение по времени, условия доступности и т.п.');?>"
            >
                <?php echo _('Настройки')?>
            </v-stepper-step>

            <v-divider></v-divider>

            <v-stepper-step
                step="3"
                title="<?php echo _('На этом шаге выполняются индивидуальные настройки назначения слушателей на занятие (если необходимо)');?>"
            >
                <?php echo _('Назначения')?>
            </v-stepper-step>
        </v-stepper-header>
    </v-stepper>
</template>