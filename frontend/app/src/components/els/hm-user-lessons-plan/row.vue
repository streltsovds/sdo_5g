<template>
  <div
    :class="addCssClassesPrefixed(
      'hm-user-lessons-plan-row',
      {
        '--breakpoint-sm-and-down': $vuetify.breakpoint.smAndDown,
        'no-active-card': arrayCard.isUnaccessible,
      }
    )"
    :title="arrayCard.isUnaccessible"
  >
    <div class="user-lessons-plan__icon">
      <a v-if="!arrayCard.isUnaccessible" :href="arrayCard.executeUrl"><img :src="arrayCard.iconUrl" alt=""/></a>
      <span v-else><img :src="arrayCard.iconUrl" alt=""/></span>
      <span>{{ arrayCard.lessonType }}</span>
    </div>
    <div class="user-lessons-plan__info">
      <div
        v-if="arrayCard.lessonTitle && arrayCard.lessonTitle !== ''"
        class="user-lessons-plan__info-title">
        <a v-if="!arrayCard.isUnaccessible" :href="arrayCard.executeUrl">
          <span>{{ arrayCard.lessonTitle }}</span>
        </a>
        <span v-else>{{ arrayCard.lessonTitle }}</span>
      </div>
      <div class="user-lessons-plan__info-description"
        v-if="arrayCard.lessonDescription && arrayCard.lessonDescription !== ''">
        <span style="white-space: pre-line;">{{ arrayCard.lessonDescription }}</span>
      </div>
      <div class="user-lessons-plan__info-rest">
        <div class="user-lessons-plan__info-rest__date">
          <div class="user-lessons-plan__info-rest__date__start"
            v-if="arrayCard.lessonDate.begin && arrayCard.lessonDate.begin !== ''">
            <div class="date-title"><span>Начало занятия</span></div>
            <div class="dates-info">
              <svg-icon name="timeStart"></svg-icon>
              <span class="dates-info__text">
                {{arrayCard.lessonDate.begin}}
              </span>
            </div>
          </div>
          <div
            v-if="arrayCard.lessonDate.end && arrayCard.lessonDate.end !== ''"
            class="user-lessons-plan__info-rest__date__end">
            <div class="date-title"><span>Окончание занятия</span></div>
            <div class="dates-info">
              <svg-icon name="timeEnd"></svg-icon>
              <span class="dates-info__text">{{arrayCard.lessonDate.end}}</span>
            </div>
          </div>
          <div
            v-if="!arrayCard.lessonDate.end || arrayCard.lessonDate.end === ''"
            class="user-lessons-plan__info-rest__date__end"
          >
            <div class="date-title"><span>Время обучения</span></div>
            <div class="dates-info">
              <svg-icon name="timeEnd"></svg-icon>
              <span class="dates-info__text">Не ограничено</span>
            </div>
          </div>
          <span
            v-if="arrayCard.lessonCondition && arrayCard.lessonCondition !== ''"
            v-html="arrayCard.lessonCondition"
          ></span>
        </div>
        <div
          v-if="arrayCard.lessonComment !== '' && arrayCard.lessonComment"
          class="user-lessons-plan__info-rest__comments"
        >
          <span>Комментарий</span>
          <div>
            <span>{{ arrayCard.lessonComment }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="user-lessons-plan__result" v-if="arrayCard.isScoreable">
      <a v-if="arrayCard.resultUrl" :href="arrayCard.resultUrl" title="Подробные результаты занятия">
        <hm-results
          :score="String(arrayCard.lessonScore.score)"
          :scale-id="String(arrayCard.lessonScore.scale_id)"
        />
      </a>
        <hm-results v-else
          :score="String(arrayCard.lessonScore.score)"
          :scale-id="String(arrayCard.lessonScore.scale_id)"
        />
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import hmResults from "@/components/els/hm-results";
import addCssClassesPrefixed from '@/utilities/addCssClassesPrefixed';

export default {
  name: "HmUserLessonsPlan",
  components: { SvgIcon, hmResults },
  props: ["arrayCard"],
  mounted() {
    console.log(this.arrayCard);
  },
  methods: {
    addCssClassesPrefixed
  },
};
</script>

<style lang="scss">
.hm-user-lessons-plan-row {
  width: 100%;
  height: 100%;
  min-height: 115px;
  display: flex;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  position: relative;
  background: white;
  box-shadow: 0 10px 30px rgb(209 213 223 / 50%);
  .user-lessons-plan__icon {
    width: 108px;
    height: auto;
    min-height: 165px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 32px 0 26px;
    flex-direction: column;
    flex-grow: 0;
    flex-shrink: 0;
    img {
      width: 48px;
    }
    span {
      font-size: 0.75rem;
      color: #bbb;
      line-height: .9rem;
      text-align: center;
    }
  }
  .no-active-card {
    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
      background: rgba(30,30,30,.25);
      border-radius: 4px;
    }
  }

  .user-lessons-plan__info {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    margin: 26px 0 24px 0;
    padding-right: 74px;
    &-title {
      margin-bottom: 12px;
      > a {
        text-decoration: none;
      }
      span {
        font-weight: 500;
        font-size: 20px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #1e1e1e;
        mix-blend-mode: normal;
      }
    }
    &-description {
      margin-bottom: 16px;
      > span {
        font-weight: normal;
        font-size: 14px;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #1e1e1e;
      }
    }
    &-rest {
      width: 100%;
      display: flex;
      &__date {
        width: 40%;
        min-width: 180px;
        display: flex;
        flex-wrap: wrap;
        > div:first-child {
          margin-right: 46px;
        }
        > div {
          .date-title {
            > span {
              font-weight: normal;
              font-size: 12px;
              line-height: 18px;
              letter-spacing: 0.15px;
              color: #696969;
            }
          }
          .dates-info {
            > svg {
              margin-right: 6px;
            }
            > span {
              font-weight: 500;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #696969;
            }
          }
        }
        > span {
          margin-top: 14px;
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #70889e;
        }
      }
      &__comments {
        width: 60%;
        > span {
          font-weight: normal;
          font-size: 12px;
          line-height: 18px;
          letter-spacing: 0.15px;
          color: #696969;
        }
        > div {
          border: 1px solid #dadada;
          border-radius: 4px;
          padding: 8px 12px;
          > span {
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            letter-spacing: 0.02em;
            color: #70889e;
          }
        }
      }
    }
  }
  .user-lessons-plan__result {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    margin-right: 55px;
    > span {
      margin-bottom: 12px;
      font-weight: normal;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #7c7c7c;
    }
    > a {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #125bb5;
      margin-top: 4px;
      text-decoration: none;
      > span {
        color: inherit;
      }
    }
  }
}

.hm-user-lessons-plan-row--breakpoint-sm-and-down {
  .user-lessons-plan__icon {
    margin: 0 12px 0 4px;
  }
  .user-lessons-plan__result {
    margin-right: 32px;
  }
  .user-lessons-plan__info {
    padding-right: 44px;
  }
}
@media(max-width: 570px) {
  .hm-user-lessons-plan-row {
    flex-wrap: wrap;
    & .user-lessons-plan__icon {
      min-height: auto;
    }
    & .user-lessons-plan__info {
      width: calc(100% - 124px);
      padding-right: 16px !important;
      margin-bottom: 16px;
    }
    & .user-lessons-plan__info-title {
      margin-bottom: 8px;
      & span {
        font-size: 16px;
        line-height: 18px;
      }
    }
    & .user-lessons-plan__info-rest__date  {
      & > div .date-title > span {
        font-size: 10px;
        line-height: 15px;
      }
      & > div .dates-info > svg {
        width: 14px !important;
        height: 14px !important;
      }
      & > div .dates-info > span {
        font-weight: 500;
        font-size: 11.9275px;
        line-height: 18px;
        letter-spacing: 0.02em;
        color: #1E1E1E;
      }
    }
    & .user-lessons-plan__result {
      margin-left: 124px;
      margin-right: 16px;
      margin-bottom: 24px;
      & .hm-result {
        width: 56px !important;
        height: 56px !important;
        & canvas {
          width: 56px !important;
          height: 56px !important;
        }
      }
    }
  }
}
</style>
