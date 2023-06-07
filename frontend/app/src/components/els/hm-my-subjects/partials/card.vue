<template>
  <v-card
    class="hm-my-subject-card "
    v-if="(subject.showAll === 'true') || !subject.isUnaccessible"
    :title="subject.isUnaccessible"
    style="position: relative;"
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
    <div
      v-if="subject.teachers && subject.teachers.length > 0"
      class="course-information-block"
      @mouseover="openListTutors"
      @mouseout="closeListTutors"
    >
      <v-list-group
        :append-icon="subject.teachers[0].photo"
        class="course-information-block__open-list"
        active-class="course-information-block__open-list_active"
        no-action
        @touchstart.stop="toggleListTutors"
        :value="showListTutors"
      >
        <template v-slot:activator style="width: 36px; height: 36px;">
          <img
            class="course-information-block__button-image"
            :src="subject.teachers[0].photo"
            alt="фото"
          >
        </template>
        <div class="course-information-block__list">
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
                <span class="course-information-block__item">{{ teacherUser.name }}</span>
              </v-list-item-title>
            </hm-card-link>
          </v-list-item>
        </div>

      </v-list-group>
    </div>
    <v-img
      class="default-subject-icon"
      v-if="subject.subjectIcon.indexOf('svg') !== -1"
      :src="subject.subjectIcon"

    >
    </v-img>
    <v-img
      class="nodefault-subject-icon"
      v-else
      :src="subject.subjectIcon"
    />
    <v-card-text
      :class="{
        'hm-my-subject-card__inner-data': true,
      }"
    >
      <v-container
        :class="{ 'pa-1': $vuetify.breakpoint.xsOnly }"
        fluid
        grid-list-xs
        style="padding: 0!important; height: 100%; display: flex; flex-direction: column;"
      >
        <v-col
          class="pa-0"
          cols="12"
          style="padding: 0!important;width: 100%; max-height: 24px;"
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
          class="hm-my-subject-card__text"
          style="padding: 0 0 0 0 !important;width: 100%; max-height: 48px !important; min-height: 48px !important; overflow: hidden; margin-top: 12px; margin-bottom: 12px;"
          cols="12"
        >
          <v-card-text style="padding: 0 0 0 0!important;">
            <span
              style="font-weight: normal; font-size: 16px; line-height: 24px; letter-spacing: 0.15px; color: #1E1E1E;padding-bottom: 8px;"
              v-html="cutDescription(subject.subjectDescription, 70)"
            ></span>
          </v-card-text>
        </v-col>
        <!-- <div class="card-subject-title__separator" /> -->
        <div class="hm-my-subject-card__img-info">
          <span v-if="subject.subjectProgramm" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;display:block">{{ cutDescription(subject.subjectProgramm, 70) }}</span>
          <span v-else style="display:block;height: 22px;"></span>
        </div>
        <div class="dates-subjects-my-course">
          <div style="display: flex; overflow: auto; width: calc(100% - 150px);">
            <div
              class="dates-subjects-my-course__start"
              v-if="subject.subjectDates.begin && subject.subjectDates.begin !== ''"
            >
              <div><span style="white-space: nowrap;">Начало курса</span></div>
              <div class="dates-info">
                <svg-icon name="timeStart" />
                <span class="dates-info__text">{{ subject.subjectDates.begin }}</span>
              </div>
            </div>
            <div
              class="dates-subjects-my-course__end"
              v-if="subject.subjectDates.end && subject.subjectDates.end !== ''"
            >
              <div><span style="white-space: nowrap;">Окончание курса</span></div>
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
              <div><span style="white-space: nowrap;">Время обучения</span></div>
              <div class="dates-info">
                <svg-icon name="timeEnd" />
                <span style="white-space: nowrap;" class="dates-info__text">Не ограничено</span>
              </div>
            </div>
          </div>
          <div
            @mouseover="openListLessons"
            @mouseout="closeListLessons"
          >
            <v-list-group
              v-if="subject.lessons.length > 0"
              class="button-open-list-group"
              active-class="button-open-list-group_active"
              @touchstart.stop="toggleListLessons"
              :value="showListLessons"
            >
              <template v-slot:activator>
                <v-list-item-title>Занятия <span>({{subject.lessons.length}})</span></v-list-item-title>
              </template>

              <div class="list-activities">

                <v-list-item
                  v-for="(plan, i) in subject.lessons"
                  :key="`key-${i}`"
                >

                  <v-list-item-content
                    class="course-plan-block__occupation course-plan-block__occupation_background-white"
                    style="height: 48px;"
                  >
                    <a :href="plan.url" style="text-decoration: none;">
                      <div class="course-plan-block__occupation-icon" :style="setBackgroundImage(plan.iconUrl)" />
                      <div
                        class="course-plan-block__occupation-title"
                        style="width: auto;"
                      >
                        <span style="width: 215px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ plan.title }}</span>
                      </div>
                    </a>
                  </v-list-item-content>

                </v-list-item>

              </div>
            </v-list-group>
          </div>
        </div>
      </v-container>
    </v-card-text>
    <div class="course-result-block" v-if="subject.status !== 0">
      <div class="course-result-block__title">
        <span
          v-if="typeof subject.progress !== 'boolean' && subject.progress !== null"
        >
          <span
            v-if="subject.progress.indexOf('%') !== -1 && subject.mark.score == '-1'"
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
        :progress="typeof subject.progress === 'boolean' ? '0' : subject.progress"
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
      showActual: this.$root.view.showAll === 'true',
      showListTutors: false,
      showListLessons: false
    }
  },
  methods: {
    setBackgroundImage(url) {
      return `background-image: url(${url})`
    },
    openListTutors() {
      this.showListTutors = true
    },
    closeListTutors() {
      this.showListTutors = false
    },
    toggleListTutors(e) {
      e.preventDefault();
      if(this.showListTutors) this.showListTutors = false
      else this.showListTutors = true
    },
    openListLessons() {
      this.showListLessons = true
    },
    closeListLessons() {
      console.log('closeListLessons')
      this.showListLessons = false
    },
    toggleListLessons(e) {
      e.preventDefault();
      if(this.showListLessons) this.showListLessons = false
      else this.showListLessons = true
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
  .list-activities {
    z-index: 100;
    background: #FFFFFF;
    box-shadow: 0px 6px 10px rgba(101, 101, 101, 0.25);
    border-radius: 4px;
    max-height: 200px;
    width: 280px;
    overflow: auto;
    position: absolute;
    top: 40px;
    left: -5px;
  }
  .button-open-list-group {
    position: relative;
    &_active {
      background: rgba(31, 142, 250, 0.04);
    }
  }
  .button-open-list-group .v-list-group__header {
    border: 0.7px solid #DADADA;
    box-sizing: border-box;
    border-radius: 4px;
  }
  .button-open-list-group .v-list-group__header .v-list-item__title {
    margin-left: 10px;

    font-style: normal;
    font-weight: normal;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
  }
  .button-open-list-group .v-list-group__header .v-list-item__icon {
    margin-right: 10px !important;
    min-width: auto !important;
  }
  .button-open-list-group .v-list-item {
    min-height: 29px;
  }
  .button-open-list-group .v-list-item::before {
    background: none !important;
  }
  .course-plan-block__occupation:hover {
    background: #D1DBF1;
  }
  .course-plan-block__occupation_background-white {
    background-color: #FFFFFF;
    border-radius: 0;
  }
  .course-information-block {
    width: 36px;
    height: 36px;
    margin-left: auto;
    margin-right: 10px;
    margin-top: 10px;
    position: absolute;
    z-index: 1;
    left: 240px;
    top: 20px;

    & .v-list-group__header .v-list-item__icon {
      display: none;
    }

    &__open-list .v-list-item::before {
      background: none !important;
    }

    &__button-image {
      border-radius: 50%;
      position:relative;
      width: 36px;
      height: 36px;
      box-sizing: border-box;
      border: 1px solid #FFFFFF;
    }

    & .v-list-item {
      min-height: auto !important;
      position: relative;
      margin-bottom: 12px;

      &:last-child {
        margin-bottom: 0;
      }
    }

    &__list {
      position: absolute;
      top: 46px;
      left: -10px;
      z-index: 100;
      padding: 16px;
      background: #26293F;
      box-shadow: 0px 6px 10px rgba(101, 101, 101, 0.25);
      border-radius: 12px;

      &::before {
        content: "";
        width: 16px;
        height: 16px;
        position: absolute;
        top: -5px;
        left: 20px;
        transform: rotate(45deg);
        background: #26293F;
      }
    }

    &__item {
      color: #FFFFFF;
      font-weight: 500;
      font-size: 14px;
      line-height: 24px;
      letter-spacing: 0.15px;
      margin-bottom: 12px;
    }
  }
  .course-information-block__open-list_active .course-information-block__button-image {
    border: 1px solid #2574CF;
  }
  .dates-subjects-my-course {
    width: 100%;
    position: static !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .hm-my-subject-card {
    height: 200px !important;
    min-width: 860px;
    margin: 0 0 26px 0 !important;
    background: #ffffff !important;
    box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5) !important;
    border-radius: 4px;
    &__img-info > span {
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
    }

    .header-card-title {
      padding: 0 0 16px 0!important;
    }

    .default-subject-icon {
      max-width: 300px;
      width: 100%;
      border-radius: 4px 0 0 4px !important;
      background-color: rgba(74, 144, 226, 0.5);
    }
    .nodefault-subject-icon {
      max-width: 300px;
      width: 100%;
      border-radius: 4px 0 0 4px !important;
      background-color: rgba(74, 144, 226, 0.5);
    }
    &__inner-data {
      max-width: calc(100% - 400px);
      position: relative;
      padding: 26px 26px 16px 26px !important;
    }
  }
  @media (max-width: 1000px) {
    .dates-subjects-my-course__start {
      margin-right: 26px;
    }

    .hm-my-subject-card {
      min-width: auto;
    }
  }
  @media(max-width: 1024px) {
    .hm-my-subject-card {
      flex-direction: column;
      min-height: 330px;
      height: auto !important;
      width: calc(50% - 13px);
      max-width: calc(50% - 13px);
      min-width: auto;
      margin-right: 26px !important;
      &:nth-child(2n) {
        margin-right: 0 !important;
      }
      & .default-subject-icon,
      & .nodefault-subject-icon {
        border-radius: 0 !important;
        max-width: 100%;
        max-height: 160px;
      }
      & .course-information-block {
        top: 26px;
        right: 26px;
        left: auto;
        margin: 0;
      }
      & .course-result-block {
        display: none;
      }
      &__inner-data {
        padding: 12px 16px 24px 16px !important;
        max-width: 100%;
        min-height: calc(100% - 160px);
        & > div {
          min-height: 100%;
          display: flex;
          flex-direction: column;
        }
      }
      & .header-card-title {
        padding: 0px 0px 8px !important;
        & > a > span {
          font-size: 16px !important;
          line-height: 20px !important;
        }
      }
      &__text {
        margin: 0 !important;
        height: auto !important;
        overflow: hidden !important;
        & > div > span {
          font-size: 12px !important;
          line-height: 15px !important;
          font-weight: 300 !important;
        }
      }
      & .dates-subjects-my-course {
        margin-top: auto;
        & > div:first-child {
          width: calc(100% - 130px) !important;
        }
        &__start > div:first-child > span,
        &__end > div:first-child > span {
          font-size: 9px;
          line-height: 14px;
        }
        &__start .dates-info__text,
        &__end .dates-info__text {
          font-size: 11px;
          line-height: 16px;
        }
        &__start .dates-info > svg,
        &__end .dates-info > svg {
          width: 12px !important;
          height: 12px !important;
        }
      }
      & .button-open-list-group .v-list-group__header .v-list-item__title {
        font-size: 11px;
        line-height: 16px;
      }
      & .list-activities {
        left: auto;
        right: 0;
      }
      & .course-information-block__list {
          left: auto;
          right: -10px;
          &::before {
            left: auto;
            right: 20px
          }
      }
    }
  }
  @media(max-width: 820px) {
    .hm-my-subject-card {
      max-width: 100%;
      width: 100%;
    }
  }
  @media(max-width: 440px) {
    .hm-my-subject-card {
      width: calc(100% + 32px);
      max-width: calc(100% + 32px);
      margin: 0 -16px !important;
      margin-bottom: 26px !important;
    }
  }
</style>
