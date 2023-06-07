<template>
  <v-card
    class="hm-my-subject-card "
    v-if="showActual || !subject.isUnaccessible"
    :title="subject.isUnaccessible"
    :class="{
      'd-flex': true,
      layout: true,
      'fill-height': true,
      'mt-1': true,
      wrap: $vuetify.breakpoint.xsOnly,
      'hm-my-subject-card-request': subject.status === 0,
      'hm-my-subject-card-completed': subject.status === 2,
      'no-active-card': subject.isUnaccessible,
      'hm-my-subject-card-new': subject.isNew,
    }"
    tile
  >
    <v-img
      class="default-subject-icon"
      v-if="subject.subjectIcon.indexOf('svg') !== -1"
      :src="subject.subjectIcon"
      :max-width="$vuetify.breakpoint.smAndUp ? '30%' : '100%'"
      :height="$vuetify.breakpoint.smAndUp ? '100%' : '150'"
    />
    <v-img
      class="nodefault-subject-icon"
      v-else
      :src="subject.subjectIcon"
      :max-width="$vuetify.breakpoint.smAndUp ? '30%' : '100%'"
      :height="$vuetify.breakpoint.smAndUp ? '100%' : '150'"
      cover
    />
    <v-card-text
      :class="{
        'pa-0': $vuetify.breakpoint.xsOnly,
        'hm-my-subject-card__inner-data': true,
      }"
    >
      <v-container
        :class="{ 'pa-1': $vuetify.breakpoint.xsOnly }"
        fluid
        grid-list-xs
        style="padding: 0!important;"
      >
        <v-col
          class="pa-0"
          cols="12"
          style="padding: 0!important;width: 100%; height: 24px;"
        >
          <!--                                :TODO перенести стили в scss шапка информационного блока карточки-->
          <v-card-title
            class="header-card-title"
            :class="{
              headline: $vuetify.breakpoint.smAndUp,
              title: $vuetify.breakpoint.xsOnly,
              'pr-5': $vuetify.breakpoint.smAndUp,
              'pt-4': $vuetify.breakpoint.xsOnly,
            }"
            style="padding: 0 0 16px 0!important;"
          >
            <a
              v-if="!subject.isUnaccessible"
              :href="subject.subjectUrl"
              style="text-decoration: none"
            >
              <span
                style="font-weight: 500; font-size: 20px; line-height: 23px; letter-spacing: 0.15px; color: #1E1E1E;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 1;-webkit-box-orient: vertical;"
              >
                {{ subject.subjectTitle }}
              </span>
            </a>
            <span
              v-else
              :title="subject.isUnaccessible"
              style="font-weight: 500; font-size: 20px; line-height: 23px; letter-spacing: 0.15px; color: #1E1E1E;"
            >{{ subject.subjectTitle }}</span>
          </v-card-title>
        </v-col>
        <v-col
          v-if="subject.subjectDescription"
          style="padding: 12px 0 0 0 !important;width: 100%; height: 24px;margin-bottom: 22px;"
          cols="12"
        >
          <v-card-text style="padding: 0 0 0 0!important; height: 24px; overflow: hidden">
            <span
              style="font-weight: normal; font-size: 16px; line-height: 24px; letter-spacing: 0.15px; color: #1E1E1E;padding-bottom: 8px;"
            >{{ cutDescription(subject.subjectDescription, 70) }}</span>
          </v-card-text>
        </v-col>
        <div class="card-subject-title__separator" />
        <div class="hm-my-subject-card__img-info" v-if="subject.subjectProgramm">
          <span>{{ cutDescription(subject.subjectProgramm, 70) }}</span>
        </div>
        <div class="dates-subjects-my-course">
          <div
            class="dates-subjects-my-course__start"
            v-if="
              subject.subjectDates.begin && subject.subjectDates.begin !== ''
            "
          >
            <div><span>Начало курса</span></div>
            <div class="dates-info">
              <svg-icon name="timeStart" />
              <span class="dates-info__text">{{ subject.subjectDates.begin }}</span>
            </div>
          </div>
          <div
            class="dates-subjects-my-course__end"
            v-if="subject.subjectDates.end && subject.subjectDates.end !== ''"
          >
            <div><span>Окончание курса</span></div>
            <div class="dates-info">
              <svg-icon name="timeEnd" />
              <span class="dates-info__text">{{
                subject.subjectDates.end
              }}</span>
            </div>
          </div>
          <div
            class="dates-subjects-my-course__end"
            v-if="subject.subjectDates.notLimited"
          >
            <div><span>Время обучения</span></div>
            <div class="dates-info">
              <svg-icon name="timeEnd" />
              <span class="dates-info__text">Не ограничено</span>
            </div>
          </div>
        </div>
        <v-col class="pa-0 grow" cols="12">
          <v-col class="pa-0">
            <v-list dense style="display: flex;padding: 0 !important;">
              <div
                class="course-information-block"
                style="width: 42%;height: 157px; position: relative"
              >
                <div v-if="subject.teachers" style="margin-bottom: 17px">
                  <span
                    v-if="subject.teachers.length > 0"
                    style="font-weight: 500; font-size: 14px; line-height: 24px; letter-spacing: 0.15px; color: #000000; padding: 5px 0"
                  >{{ _("Тьюторы:") }}</span>
                </div>
                <v-menu
                  v-if="subject.teachers && subject.teachers.length > 1"
                  offset-y
                  style="z-index: 8!important"
                >
                  <template #activator="{ on }">
                    <span
                      v-on="on"
                      style="margin-top: 4px; line-height: 24px; margin-right: 16px; cursor: pointer"
                    >
                      <v-list-item :avatar="subject.teachers[0].photo">
                        <v-list-item-avatar>
                          <img
                            v-if="
                              subject.teachers[0].photo &&
                                subject.teachers[0].photo !== '/'
                            "
                            :src="subject.teachers[0].photo"
                            alt="фото"
                            width="26"
                            height="26"
                            style="border-radius: 50%; position:relative; width: 26px; height: 26px;"
                          >
                          <svg-icon v-else name="userCourse" />
                        </v-list-item-avatar>
                        <span style="color: #1e1e1e;font-weight: 500;font-size: 14px;line-height: 24px;letter-spacing: 0.15px;margin-left: 8px ">{{ subject.teachers[0].name }}</span>
                        <span style="position: relative;left: 7px;top: 4px;border: 5px solid transparent;border-top: 5px solid rgba(0, 0, 0, 0.54) ;font-size: 0;" />
                      </v-list-item>
                    </span>
                  </template>
                  <v-list class="hm-mysubject-list">
                    <v-list-item
                      v-for="(teacherUser, index) in subject.teachers"
                      :key="`${index}-${teacherUser.name}`"
                    >
                      <hm-card-link
                        :url="teacherUser.url"
                        title="карточка пользователя"
                        rel="pcard"
                        float=" "
                      >
                        <v-list-item-title>
                          <v-list-item-avatar>
                            <img
                              v-if="teacherUser.photo && teacherUser.photo !== '/'"
                              :src="teacherUser.photo"
                              alt="фото"
                              width="26"
                              height="26"
                              style="border-radius: 50%; position:relative; width: 26px; height: 26px;"
                            >
                            <svg-icon v-else name="userCourse" />
                          </v-list-item-avatar>
                          <span style="color: #1e1e1e;font-weight: 500;font-size: 14px;line-height: 24px;letter-spacing: 0.15px;margin-left: 8px ">{{ teacherUser.name }}</span>
                        </v-list-item-title>
                      </hm-card-link>
                    </v-list-item>
                  </v-list>
                </v-menu>
                <span
                  v-else
                  v-for="(teacherUser, index) in subject.teachers"
                  :key="`${index}-${teacherUser.name}`"
                  style="margin-top: 4px; line-height: 24px; margin-right: 16px"
                >
                  <hm-card-link
                    :url="teacherUser.url"
                    title="карточка пользователя"
                    rel="pcard"
                    float=" "
                  >
                    <v-list-item :avatar="subject.teachers[0].photo">
                      <v-list-item-avatar>
                        <img
                          v-if="teacherUser.photo && teacherUser.photo !== '/'"
                          :src="teacherUser.photo"
                          alt="фото"
                          width="26"
                          height="26"
                          style="border-radius: 50%; position:relative;"
                        >
                        <svg-icon v-else name="userCourse" />
                      </v-list-item-avatar>
                      <span
                        style="color: #1e1e1e;font-weight: 500;font-size: 14px;line-height: 24px;letter-spacing: 0.15px; margin-left: 8px "
                      >{{ subject.teachers[0].name }}</span>
                    </v-list-item>
                  </hm-card-link>
                </span>
              </div>
              <div class="course-plan-block">
                <div
                  class="course-plan-block__occupation"
                  v-for="(plan, i) in subject.lessons"
                  :key="`key-${i}`"
                >
                  <a :href="plan.url" style="text-decoration: none">
                    <div class="course-plan-block__occupation-icon" :style="setBackgroundImage(plan.iconUrl)" />
                    <div class="course-plan-block__occupation-title">
                      <span>{{ plan.title }}</span>
                    </div>
                  </a>
                </div>
              </div>
            </v-list>
          </v-col>
        </v-col>
      </v-container>
    </v-card-text>
    <div class="course-result-block" v-if="subject.status !== 0">
      <div class="course-result-block__title">
        <span
          v-if="
            typeof subject.progress !== 'boolean' && subject.progress !== null
          "
        >
          <span
            v-if="
              subject.progress.indexOf('%') !== -1 &&
                subject.mark.score == '-1'
            "
          >Прогресс</span>
          <span v-else>Результат</span>
        </span>
        <span v-else>
          <span>Результат</span>
        </span>
      </div>
      <hm-results
        :score="+subject.mark.score"
        :scale-id="subject.mark.scale_id"
        :progress="
          typeof subject.progress === 'boolean' ? '0' : subject.progress
        "
      />
    </div>
  </v-card>
</template>

<script>
import Vue from 'vue'
import SvgIcon from "@/components/icons/svgIcon";
import hmResults from "@/components/els/hm-results";
import hmCardLink from "@/components/els/hm-card-link";

export default {
  name: "HmMySubjectsCard",
  components: { SvgIcon, hmResults, hmCardLink },
  props: {
    subject: {
      type: Object,
      required: true,
    }
  },
  data(){
    return {
      showActual: this.$root.view.showAll == 'true',
    }
  },
  methods: {
    setBackgroundImage(url) {
      return `background-image: url(${url})`
    },
    cutDescription(text, length){
      let sliced = text.slice(0,length);
      if (sliced.length < text.length) {
        sliced += '...';
      }
      return sliced;
    }
  }
}
</script>

<style lang="scss">
  .hm-my-subject-card {
    height: 288px;
    margin: 0 0 26px 0 !important;
    background: #ffffff !important;
    box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5) !important;
    border-radius: 4px;


    .default-subject-icon {
      width: 340px;
      border-radius: 4px 0 0 4px !important;
      background-color: rgba(74, 144, 226, 0.5);
    }
    .nodefault-subject-icon {
      width: 340px;
      border-radius: 4px 0 0 4px;
      background-color: rgba(74, 144, 226, 0.5);
    }
    &__inner-data {
      max-width: 55%;
      position: relative;
      z-index: 1;
      padding: 26px 26px 16px 26px !important;
    }
  }
</style>
