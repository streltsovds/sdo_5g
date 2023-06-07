<template>
  <v-list-item
    :key="lesson.title"
    :href="!lesson.isEmpty ? lesson.url : null"
    :target="lesson.target"
    avatar
    ripple
    class="infoblock-schedule-daily_lesson"
  >
      <v-list-item-avatar
        v-if="lesson.icon"
        size="24"
        tile
        class="pr-3"
        style="
             justify-content: flex-end;
        "
      >
        <img
          :src="lesson.icon.src"
          :alt="lesson.icon.alt"
          :title="lesson.icon.title"
        />
      </v-list-item-avatar>

    <v-list-item-content>
      <v-list-item-title class="mb-2">
        <span v-text="lesson.title" />
      </v-list-item-title>
      <dl>
        <dd
          class="grey--text"
          v-text="
            lesson.recommend
              ? 'Рекомендуемое время выполнения: '
              : 'Время выполнения: '
          "
        />
        <dt v-text="lesson.datetime" />
      </dl>
      <ul v-if="lesson.students">
        <li v-for="(student, i) in lesson.students" :key="i">
          <p v-text="student.fio" />
          <span v-if="student.begin_personal" v-text="student.begin_personal" />
          <span
            v-if="student.end_personal"
            v-text="' - ' + student.end_personal"
          />
        </li>
      </ul>
    </v-list-item-content>
  </v-list-item>
</template>
<script>
export default {
  props: {
    lesson: {
      type: Object,
      default: () => {
        return {};
      }
    }
  }
};
</script>
<style lang="scss">
.infoblock-schedule-daily_lesson {
  height: auto !important;
  padding-top: 15px;
  padding-bottom: 15px;
  a {
    margin-top: -15px;
    margin-bottom: -15px;
    padding-top: 15px;
    padding-bottom: 15px;
  }
  dl {
    font-size: 14px;
  }
}
</style>
