<template>
  <div class="hm-my-assessment-progress">
      <span class="hm-my-assessment-progress__title">{{data.title}}</span>
      <div class="hm-my-assessment-progress__scale">
          <div v-if="data.type === 'percent'" class="hm-my-assessment-progress__scale-block">
              <span style="color: #000000;">{{`${data.value}%`}}</span>
              <div :style="{background: '#B6D3F3', width: `${data.value}%`}" class="hm-my-assessment-progress__scale-block-progress"></div>
          </div>
          <div v-if="data.type === 'time'" class="hm-my-assessment-progress__scale-block">
              <span v-if="getPercentFromTime(data.value, data.maxValue) >= 55" style="color: #ffffff">{{formattingTime(data.value)}}</span>
              <span v-else style="color: #000000;">{{formattingTime(data.value)}}</span>
              <div :style="{background: 'rgba(229, 115, 115, 0.9)', width: `${getPercentFromTime(data.value, data.maxValue)}%`}" class="hm-my-assessment-progress__scale-block-progress"></div>
          </div>
      </div>
  </div>
</template>
<script>
export default {
  props: ["data"],
  methods: {
    getPercentFromTime(value, maxValue) {
      return (100*value)/maxValue;
    },
    formattingTime(value) {
      const hours = Math.floor(value/60);
      let minutes = Math.floor(value%60);
      if(minutes < 10) minutes = `0${minutes}`;
      return `${hours}:${minutes}`;
    }
  }
}
</script>
<style lang="scss">
  .hm-my-assessment-progress {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 300px;
    margin-bottom: 16px;
    &:last-child {
      margin-bottom: 0;
    }
    &__title {
      font-style: normal;
      font-weight: 300;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
    &__scale {
      height: 100%;
      min-height: 18px;
      max-width: 100%;
      min-width: 100%;
      &-block {
        width: 100%;
        height: 18px;
        background: #f5f5f5;
        border-radius: 35px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        & span {
          z-index: 10;
          font-style: normal;
          font-weight: 500;
          font-size: 14px;
          line-height: 16px;
          letter-spacing: 0.02em;
        }
        &-progress {
          position: absolute;
          left: 0;
          top: 0;
          height: 100%;
          border-radius: 35px;
        }
      }
    }
  }
</style>
