<div class="at-form-report els-quest-reports">
    <v-card class="report-summary user-report__wrap ">
        <hm-report-chart
            v-for='(quest, id) in <?=json_encode($this->stat['questions'], JSON_HEX_APOS);?>'
            :quest ='quest'
            :id='id'
        />
        <h3 v-if='!!<?php echo json_encode($this->stat['questions'], JSON_HEX_APOS);?>.length'>
          {{ _('Нет данных для отображения') }}>
        </h3>
    </v-card>
</div>