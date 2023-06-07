<template>
  <div class="infoblock-schedule-daily_subject">
    <v-divider />
    <v-list v-if="subject.lessonsCount > 0">
      <lesson
        v-for="(lesson, i) in subject.lessons"
        :key="i"
        :lesson="lesson"
      />
    </v-list>
    <v-card-text v-else>
      <v-alert type="info" value="true" outlined>
        <span>Отсутствуют данные для отображения</span>
      </v-alert>
    </v-card-text>
  </div>
</template>
<script>
import lesson from "./lesson";

export default {
  components: { lesson },
  props: {
    subject: {
      type: Object,
      default: () => ({})
    }
  }
};
</script>
