<template>
  <div class="hm-my-assessment-card">
    <div class="hm-my-assessment-card__image">
      <icon/>
      <p class="hm-my-assessment-card__image-title">Сессия оценки</p>
    </div>
    <div class="hm-my-assessment-card__content">
      <a :href="dataCard.assessmentUrl" class="hm-my-assessment-card__content-title">
        {{dataCard.assessmentTitle}}
      </a>
      <div class="hm-my-assessment-card__content-dates hm-my-assessment-dates">
            <div
              class="hm-my-assessment-dates__start"
              v-if="dataCard.assessmentDates.begin && dataCard.assessmentDates.begin !== ''"
            >
              <p class="hm-my-assessment-dates__title">Дата начала</p>
              <div class="hm-my-assessment-dates__info">
                <svg-icon name="timeStart" />
                <span class="hm-my-assessment-dates__text">{{dataCard.assessmentDates.begin}}</span>
              </div>
            </div>
            <div
              class="hm-my-assessment-dates__end"
              v-if="dataCard.assessmentDates.end && dataCard.assessmentDates.end !== ''"
            >
              <p class="hm-my-assessment-dates__title">Дата окончания</p>
              <div class="hm-my-assessment-dates__info">
                <svg-icon name="timeEnd" />
                <span class="hm-my-assessment-dates__text">{{dataCard.assessmentDates.end}}</span>
              </div>
            </div>
      </div>
    </div>
    <div class="hm-my-assessment-card__statistics">
      <hm-my-assessment-statistics
        v-for="stat in dataCard.assessmentStatistics"
        :key="stat.title"
        :data="stat"
      />
    </div>
  </div>
</template>
<script>
import Icon from "../icon";
import SvgIcon from "@/components/icons/svgIcon";
import HmMyAssessmentStatistics from "../statistics";
export default {
  props: ["dataCard"],
  components: { Icon, SvgIcon, HmMyAssessmentStatistics }
}
</script>
<style lang="scss">
  .hm-my-assessment-card {
    width: 100%;
    min-height: 200px;
    box-sizing: border-box;
    display: flex;
    border-top: 1px solid rgba(184, 197, 211, 0.3);
    padding: 26px 0;
    &:first-child {
      border-top: 0;
    }
    &__image {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 0 33px;
      min-height: 100%;
      &-title {
        font-style: normal;
        font-weight: normal;
        font-size: 12px;
        line-height: 14px;
        text-align: center;
        letter-spacing: 0.15px;
        color: #C4C4C4;
        margin-top: 12px;
      }
    }
    &__content {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 100%;
      width: calc(100% - 504px);
      &-title {
        font-style: normal;
        font-weight: 500;
        font-size: 20px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #1E1E1E !important;
        text-decoration: none;
        &:hover {
          color: #2960A0 !important;
        }
      }
    }
    &__statistics {
      padding: 0 26px;
      display: flex;
      flex-direction: column;
    }
  }
  .hm-my-assessment-dates {
    display: flex;
    flex-direction: column;
    margin-top: 18px;
    &__start {
      margin-bottom: 24px;
    }
    &__title {
      font-style: normal;
      font-weight: normal;
      font-size: 12px;
      line-height: 18px;
      letter-spacing: 0.15px;
      color: #666666;
      margin-bottom: 6px !important;
    }
    &__info {
      display: flex;
      align-items: center;
    }
    &__text {
      font-style: normal;
      font-weight: 300;
      font-size: 14px;
      line-height: 16px;
      letter-spacing: 0.02em;
      color: #696969;
      margin-left: 8px;
    }
  }
</style>
