<template>
  <div class="hm-my-program">
    <div class="hm-my-program__header">
      <div class="hm-my-program__is-new" v-if="isNew">
        NEW
      </div>
      <div class="hm-my-program__image-wrapper"
          :class="{'hm-my-program__image-wrapper_border': isShowCourses}"
           v-if="!iconIsSet"
           :style="{
             backgroundImage: `url(${program.icon})`
           }"
      />
      <div class="hm-my-program__image-wrapper" :class="{'hm-my-program__image-wrapper_border': isShowCourses}" v-else>
        <img class="hm-my-program__image"
             :class="{'hm-my-program__image_border': isShowCourses}"
             :src="program.icon"
             alt=""
             srcset=""
        >
      </div>
      <div class="hm-my-program__info" :class="{'hm-my-program__info_border': !isShowCourses}">
        <h2 class="hm-my-program__title">
          {{ program.name }}
        </h2>
        <div class="hm-my-program__progress">
          <div class="hm-my-program__progress-bar"
               :style="{
                 width: progressBarPercent + '%'
               }"
          />
          <p class="hm-my-program__progress-text">
            Пройдено {{ program.graduatedCount }} из {{ (program.subjects.subjectUsers || []).length }}
          </p>
        </div>
      </div>
      <div class="hm-my-program__toggler"
           @click="isShowCourses = !isShowCourses"
      >
        <img v-if="isShowCourses"
             src="/images/icons/circle-arrow-down.svg"
             alt=""
             srcset=""
        >
        <img v-else
             src="/images/icons/circle-arrow-up.svg"
             alt=""
             srcset=""
        >
      </div>
    </div>
    <v-slide-y-transition>
      <div class="hm-my-program__courses" v-if="isShowCourses">
          <div class="hm-my-program__course"
            v-for="(course, index) in courses" :key="index"
            :class="{'no-active-card': course.isUnaccessible}"
            :title="course.isUnaccessible">

            <div class="hm-my-program__course-type-icon">
              <img src="/images/icons/academic-hat.svg" alt="">
            </div>
            <div class="hm-my-program__course-wrapper">
              <div class="hm-my-program__course-data">
                <h3 class="hm-my-program__course-title">
                  <a v-if="!course.isUnaccessible" :href="course.subjectUrl">
                    <span>{{ course.subjectTitle }}</span>
                  </a>
                  <span v-else>{{ course.subjectTitle }}</span>
                </h3>
                <p v-if="course.subjectDescription" class="hm-my-program__course-desc">
                  {{ course.subjectDescription }}
                </p>
                <!-- <div class="hm-my-program__btn" v-if="course.regStatus && course.regStatus.isButton">
                  <a :href="course.regStatus.href">{{ course.regStatus.text }}</a>
                </div> -->
                <!-- <span v-else>{{ course.regStatus.text }}</span> -->
              </div>
              <div class="hm-my-program__course-results">
                <div ref="progress" class="course-result-block" v-if="course.status !== 0">
                  <!-- <div class="course-result-block__title">
                  <span v-if="typeof course.progress !== 'boolean' && course.progress !== null">
                    <span v-if="course.progress.indexOf('%') !== -1 && course.mark.score == '-1'">Прогресс</span>
                    <span v-else>Результат</span>
                  </span>
                    <span v-else>
                    <span>Результат</span>
                  </span>
                  </div> -->
                  <div class="hm-my-program__btn" v-if="course.regStatus && course.regStatus.isButton">
                    <a :href="course.regStatus.href">{{ course.regStatus.text }}</a>
                  </div>
                  <v-tooltip
                    v-else
                    bottom
                  >
                    <template v-slot:activator="{ on, attrs }">
                      <div
                        v-bind="attrs"
                        v-on="on"
                      >
                        <hm-results
                          :size="[50, 50]"
                          :score="+course.mark.score"
                          :scale-id="course.mark.scale_id"
                          :progress="typeof course.progress === 'boolean' ? '0' : course.progress"
                        />
                      </div>
                    </template>
                    <span>{{ getTooltipText(course) }}</span>
                  </v-tooltip>
                </div>
              </div>
            </div>
          </div>
        </div>
    </v-slide-y-transition>
  </div>
</template>

<script>
import HmResults from '@/components/els/hm-results/index.vue';

export default {
  components: {
    HmResults
  },
  props:{
    program: {
      type: Object,
      default: () => {}
    },
    isShowCourses: {
      type: Boolean,
      default: false
    }
  },
  data: () => ({
    isNew: false,
    courses: []
  }),
  computed:{
    progressBarPercent(){
      return ((this.program.graduatedCount)/((this.program.subjects.subjectUsers || []).length ))*100
    },
    iconIsSet(){
      return this.program.icon === "/images/icons/library.svg";
    }
  },
  mounted(){
    this.isNew = this.program.isNew;
    this.courses = this.program.subjects.subjectUsers;
  },
  methods: {
    getTooltipText(course) {
      if(typeof course.progress !== 'boolean' && course.progress !== null) {
        if(course.progress.indexOf('%') !== -1 && course.mark.score == '-1') return "Прогресс"
        else return "Результат"
      } else return "Результат"
    }
  }
}
</script>

<style lang="scss">
    .hm-my-program{
        & .no-active-card::after {
          left: -20px;
          width: calc(100% + 40px);
          height: calc(100% + 1px);
          border-radius: 0;
        }

        position: relative;
        border-radius: 4px;
        width: 100%;
        margin-top: 26px;
        box-shadow: 0px 10px 30px rgba(209, 213, 223, 0.5);
        &:first-child {
          margin-top: 0;
        }
        &__btn{
            background-color: #2574CF;
            padding: 6px 16px;
            border-radius: 4px;
            display: inline-block;
            cursor: pointer;
            > a {
              color: #fff;
              text-decoration: none;
            }

            &:hover {
              border: 1px solid #2574CF;
              background: #FFFFFF;
              > a {
                color: #000000;
              }
            }
        }
        &__is-new{
            background: #05C985;
            border-radius: 0 16px 16px 0;
            width: 78.57px;
            height: 32.01px;
            position: absolute;
            left: -5px;
            top: 20px;
            color: #fff;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        &__header{
            position: relative;
            width: 100%;
            height: 240px;
        }
        &__image{
          width: 86px;
          height: 86px;
          margin-top: 47px;
          background: #a4c7ef;
          border-radius: 4px;
          &_border {
            border-radius: 4px 4px 0 0;
          }
        }
        &__image-wrapper{
            display: flex;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-size: cover;
            border-radius: 4px;
            background: #a4c7ef;
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            &_border {
              border-radius: 4px 4px 0 0;
            }
        }
        &__toggler{
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
        }
        &__info{
            padding: 18px 20px;
            position: absolute;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 60px;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #1e1e1e70;
            &_border {
              border-radius: 0 0 4px 4px;
            }
        }
        &__title{
            font-size: 20px;
            margin-bottom: 0;
            color: #fff;
            font-weight: 500;
        }
        &__progress{
            position: relative;
            display: flex;
            justify-content: center;
            width: 160px;
            height: 36px;
            padding: 6px 16px;
            border-radius: 4px;
            background: rgba(45, 167, 113, 0.2);
            border: 0.729554px solid rgba(45, 167, 113, 0.4);
            &-text{
                position: relative;
                margin-bottom: 0!important;
                font-size: 16px;
                color: #99D9BD;
                z-index: 2;
            }
            &-bar{
                background: #2DA771;
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1;
                border-radius: 4px;
            }
        }
        &__courses{
            //padding: 0 25px;
            background-color: #fff;
            box-shadow: 0px 10px 30px rgba(209, 213, 223, 0.5);
            border-radius: 0 0 4px 4px;
        }
        &__results-text{
            color: #7C7C7C;
            font-size: 12.3243px;
            margin-bottom: 5px!important;
        }
        &__course{

            &:last-child.no-active-card::after {
              left: -20px;
              width: calc(100% + 40px);
              height: 100%;
              border-radius: 0 0 4px 4px;
            }
            position: relative;
            padding: 20px 25px;
            padding-right: 10px;
            margin: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(184, 197, 211, 0.5);
            box-shadow: none !important;
            &:last-child {
              border-bottom: 0;
            }
            &-desc {
              font-size: 14px;
              margin-bottom: 0 !important;
            }
            &-wrapper {
              width: 100%;
              display: flex;
              align-items: center;
              justify-content: space-between;
            }
            &-title{
              > a {
                text-decoration: none;
                color: #000;
                &:hover {
                  color: #2574CF;
                }
              }
            }
            &-type-icon{
              width: 5%;
                margin-right: 40px;
                //flex: 1;
            }
            &-data{
              //flex: 18;
              max-width: calc(100% - 180px);
              // margin-right: 30px;
                // height: 120px;
            }
            &-results{
              // width: 75px;
              //flex: 1;
            }
        }
    }
    @media(max-width: 768px) {
      .hm-my-program {
        &__image-wrapper {
          border-radius: 0;
        }
        &__image {
          border-radius: 0;
        }
        &__btn {
          padding: 4px 12px;
          font-size: 12px;
          line-height: 12px;
        }
        &__header {
          height: 260px
        }
        &__info {
          min-height: 100px;
          height: auto;
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          padding: 12px 16px;
          &_border {
            border-radius: 0;
          }
        }
        &__title {
          font-weight: 500;
          font-size: 14px;
          line-height: 18px;
          letter-spacing: 0.02em;
          margin-bottom: 13px;
        }
        &__course-desc {
          font-weight: 300;
          font-size: 11px;
          line-height: 15px;
          letter-spacing: 0.02em;
          color: #000000;

        }
        &__progress {
          min-width: 121px;
          width: min-content;
          height: 26px;
          padding: 4px 12px;
          &-text {
            font-size: 12px;
            line-height: 18px;
          }
        }
        &__courses {
          padding: 0 !important;
          border-radius: 0;
        }
        &__course {
          padding: 16px 0;
          margin: 0 16px;
          &-type-icon {
            width: 40px;
            margin-right: 26px;
            & img {
              width: 40px;
            }
          }
          // &-wrapper {
          //   display: flex;
          //   flex-direction: column;
          //   width: calc(100% - 66px);
          // }
          &-data {
            width: 100%;
            max-width: 100%;
          }
          &-title {
            font-weight: 500;
            font-size: 14px;
            line-height: 18px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
          &-results {
            width: min-content;
            & .course-result-block__title > span {
              font-weight: normal;
              font-size: 9.35002px;
              line-height: 14px;
              letter-spacing: 0.02em;
              color: #7C7C7C;
            }
            & .hm-result {
              width: 42px !important;
              height: 42px !important;
              & canvas {
                width: 42px !important;
                height: 42px !important;
              }
            }
            & .hm-result-data > span,
            & .hm-result-noprogress > div > span {
              font-size: 12px !important;
              line-height: 12px;
            }
            & .course-result-block {
              min-height: min-content;
            }
          }
        }
      }
    }
    @media(max-width: 440px) {
      .hm-my-program {
        width: calc(100% + 32px);
        margin: 0 -16px;
      }
    }
</style>
