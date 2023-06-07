<template>
  <div class="hm-assessment-form">
    <hm-assessment-user-info :data="data.user" />
    <div class="hm-assessment-form__table">
      <div class="hm-assessment-form__header">
        <div class="hm-assessment-form__header-cell hm-assessment-form__header-cell_first">
          <p>Индикатор</p>
        </div>
        <div class="hm-assessment-form__header-cell" v-for="(scaleValue, index) in data.scaleValues" :key="index">
          <p>{{ scaleValue.text }}</p>
        </div>
      </div>
      <div class="hm-assessment-form__body" v-if="data.options.competenceUseIndicators">
        <hm-row-indicator
          v-for="(indicator, index) in criteria"
          :key="index"
          :name="indicator.name_questionnaire || indicator.name"
          :id="indicator.question_id"
          :indicator-info="indicator"
          :scale-values="data.scaleValues"
          :selected-answer="chosenAnswers[indicator.question_id]"
          @hm:test:answer-confirmed="onAnswerChosen"
        />
      </div>
      <div class="hm-assessment-form__body" v-else>
        <hm-row-indicator
          v-for="(criterion, index) in criteria"
          :key="index"
          :name="criterion.name"
          :id="criterion.question_id"
          :indicator-info="indicator"
          :scale-values="data.scaleValues"
          :selected-answer="chosenAnswers[criterion.question_id]"
          @hm:test:answer-confirmed="onAnswerChosen"
        />
      </div>
    </div>
  </div>
</template>
<script>
import HmRowIndicator from "./row.vue";
import HmAssessmentUserInfo from "./user.vue";
export default {
  components: {
    HmRowIndicator,
    HmAssessmentUserInfo
  },
  props: {
    chosenAnswers: {
      type: Object,
      default: () => {}
    },
    criteria: {
      type: Array,
      default: () => []
    },
    data: {
      type: Object,
      default: () => {}
    }
  },
  methods: {
    onAnswerChosen(payload) {
      this.$emit('hm:test:answer-confirmed', payload)
    },
  }
}
</script>
<style lang="scss">
.hm-assessment-form {
  display: flex;
  flex-direction: column;
  width: 100%;
  &__table {
    display: flex;
    flex-direction: column;
    width: 100%;
    border: 1px solid #D4E3FB;
  }
  &__header {
    display: flex;
    align-items: center;
    width: 100%;
    height: 86px;
    background: #EDF4FC;
    border-bottom: 1px solid #D4E3FB;
    &-cell {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 184px;
      min-width: 184px;
      height: 100%;
      border-right: 1px solid #D4E3FB;
      padding: 16px 26px;
      box-sizing: border-box;
      & p {
        font-weight: 500;
        font-size: 16px;
        line-height: 20px;
        text-align: center;
        letter-spacing: 0.02em;
        color: #000000;
        margin: 0 !important;
      }
      &_first {
        width: 100%;
        height: 100%;
      }
      &:last-of-type {
        border-right: none;
      }
    }
  }
  &__body {
    display: flex;
    flex-direction: column;
    width: 100%;
  }
  &__row {
    display: flex;
    align-items: center;
    width: 100%;
    min-height: 104px;
    background-color: #ffffff;
    border-bottom: 1px solid #D4E3FB;
    position: relative;
    &_active {
      background-color: #E7F7ED !important;
    }
    &:nth-child(2n) {
      background-color: #F5F5F5;
    }
    &:last-child {
      border: none;
    }
    &-buttons {
      margin: 0;
      padding: 0;
      height: 100% !important;
      position: absolute;
      top: 0;
      right: 0;
      & .v-input__control {
        height: 100% !important;
        & .v-messages {
          display: none;
        }
        & .v-input__slot {
          margin: 0;
          height: 100% !important;
          & .v-input--radio-group__input {
            display: flex;
            align-items: center;
            flex-direction: row;
            height: 100% !important;
          }
        }
      }
    }
    &-cell {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 184px;
      min-width: 184px;
      height: 100%;
      border-right: 1px solid #D4E3FB;
      margin: 0 !important;
      padding: 16px 26px;
      box-sizing: border-box;
      &:first-child {
        border-left: 1px solid #D4E3FB;;
      }
      .v-input--selection-controls__input {
        margin: 0 !important;
      }
      & p {
        font-weight: 300;
        font-size: 22px;
        line-height: 32px;
        letter-spacing: 0.02em;
        color: #000000;
        margin: 0 !important;
      }
      &_first {
        width: 100%;
        height: 100%;
        justify-content: flex-start;
        border: none;
      }
      &:last-of-type {
        border-right: none;
      }
    }
  }
}
@media(max-width: 1264px) {
  .hm-assessment-form__header-cell,
  .hm-assessment-form__row-cell {
    width: 140px;
    min-width: 140px;
    &_first {
        width: 100%;
      }
  }
}
@media(max-width: 960px) {
  .hm-assessment-form__header-cell,
  .hm-assessment-form__row-cell {
    width: 100px;
    min-width: 100px;
    &_first {
        width: 100%;
      }
  }
  .hm-assessment-form__header-cell p {
    font-size: 12px;
  }
  .hm-assessment-form__row-cell p {
    font-size: 16px;
    line-height: 28px;
  }
}
</style>
