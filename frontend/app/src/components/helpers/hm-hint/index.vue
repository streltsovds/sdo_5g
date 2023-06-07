<template>
  <div class="hm-hint" :style="styles">
    <div class="hm-hint__icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.3125C6.65012 2.3125 2.3125 6.65168 2.3125 12C2.3125 17.3514 6.65012 21.6875 12 21.6875C17.3499 21.6875 21.6875 17.3514 21.6875 12C21.6875 6.65168 17.3499 2.3125 12 2.3125ZM12 6.60938C12.9061 6.60938 13.6406 7.34391 13.6406 8.25C13.6406 9.15609 12.9061 9.89062 12 9.89062C11.0939 9.89062 10.3594 9.15609 10.3594 8.25C10.3594 7.34391 11.0939 6.60938 12 6.60938ZM13.7188 17C13.9776 17 14.1875 16.7901 14.1875 16.5312V15.5938C14.1875 15.3349 13.9776 15.125 13.7188 15.125H13.25V11.2188C13.25 10.9599 13.0401 10.75 12.7812 10.75H10.2812C10.0224 10.75 9.8125 10.9599 9.8125 11.2188V12.1562C9.8125 12.4151 10.0224 12.625 10.2812 12.625H10.75V15.125H10.2812C10.0224 15.125 9.8125 15.3349 9.8125 15.5938V16.5312C9.8125 16.7901 10.0224 17 10.2812 17H13.7188Z" fill="black"/>
        <mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="2" y="2" width="20" height="20">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2.3125C6.65012 2.3125 2.3125 6.65168 2.3125 12C2.3125 17.3514 6.65012 21.6875 12 21.6875C17.3499 21.6875 21.6875 17.3514 21.6875 12C21.6875 6.65168 17.3499 2.3125 12 2.3125ZM12 6.60938C12.9061 6.60938 13.6406 7.34391 13.6406 8.25C13.6406 9.15609 12.9061 9.89062 12 9.89062C11.0939 9.89062 10.3594 9.15609 10.3594 8.25C10.3594 7.34391 11.0939 6.60938 12 6.60938ZM13.7188 17C13.9776 17 14.1875 16.7901 14.1875 16.5312V15.5938C14.1875 15.3349 13.9776 15.125 13.7188 15.125H13.25V11.2188C13.25 10.9599 13.0401 10.75 12.7812 10.75H10.2812C10.0224 10.75 9.8125 10.9599 9.8125 11.2188V12.1562C9.8125 12.4151 10.0224 12.625 10.2812 12.625H10.75V15.125H10.2812C10.0224 15.125 9.8125 15.3349 9.8125 15.5938V16.5312C9.8125 16.7901 10.0224 17 10.2812 17H13.7188Z" fill="white"/>
        </mask>
        <g mask="url(#mask0)">
          <rect width="24" height="24" fill="#2D6BB1"/>
        </g>
      </svg>
    </div>
    <div class="hm-hint__container">
      <span v-html="text"></span>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    text: {
      type: String,
      default: ''
    },
    color: {
      type: String,
      default: '#FAF3D8'
    }
  },
  computed: {
    styles() {
      return {
        background: this.color
      }
    }
  }
}
</script>

<style lang="scss">
.hm-hint {
  width: 100%;
  min-height: 56px;
  display: flex;
  align-items: flex-start;
  justify-content: flex-start;
  padding: 16px;
  box-sizing: border-box;
  margin-bottom: 16px;
  border-radius: 4px;
  &__icon {
    width: 24px;
    height: 24px;
    margin-right: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  &__container {
    width: 100%;
    height: auto;
    min-height: 24px;
    > span {
      font-weight: normal;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
}
</style>
