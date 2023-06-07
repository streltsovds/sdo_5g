<template>
<div class="actions-results-container" :style="positionCo">
  <v-tooltip left>
    <template  v-slot:activator="{ on: onTooltip }">
      <a
        v-on="onTooltip"
        class="actions-results"
        :href="url">
        <svg width="96"
             height="96"
             viewBox="0 0 96 96"
             fill="none"
             :style="styleCo"
             xmlns="http://www.w3.org/2000/svg"
        >

          <path d="M88 49.5999H46.4V7.9999C46.4 7.1163 45.6836 6.3999 44.8 6.3999C20.0576 6.3999 0 26.4575 0 51.1999C0 75.9423 20.0576 95.9999 44.8 95.9999C69.5424 95.9999 89.6 75.9423 89.6 51.1999C89.6 50.3163 88.8836 49.5999 88 49.5999ZM44.8 92.7999C21.825 92.8053 3.1956 74.1849 3.19 51.2099C3.1848 28.8497 20.8562 10.4849 43.2 9.6303V51.1999C43.2 52.0835 43.9164 52.7999 44.8 52.7999H86.3696C85.483 75.1239 67.1416 92.7729 44.8 92.7999Z"
                fill="inherit"/>
          <path d="M51.2 0C50.3164 0 49.6 0.7164 49.6 1.6V44.8C49.6 45.6836 50.3164 46.4 51.2 46.4H94.4C95.2836 46.4 96 45.6836 96 44.8C95.9718 20.0694 75.9306 0.0282 51.2 0ZM52.8 43.2V3.2304C74.5068 4.0942 91.9058 21.4932 92.7696 43.2H52.8Z"
                fill="inherit"/>

        </svg>
      </a>
    </template>
    <span>{{ _(title) }}</span>
  </v-tooltip>
</div>
</template>

<script>
export default {
  props: {
    title: {
      type: String,
      default: 'Результаты'
    },
    size: {
      type: Number,
      default: 1
    },
    color: {
      type: String,
      default: '#1E1E1E'
    },
    position: {
      type: Array,
      default: () => [20,20]
    },
    url: {
      type: String,
      default: ''
    }
  },
  computed: {
    positionCo() {
      return {top: this.position[0]+'px', right: this.position[1]+'px'}
    },
    styleCo() {
       return {transform: `scale(${this.size})`, fill: this.color}
    }
  }
}
</script>

<style lang="scss">
.actions-results-container {
  position: absolute;
  display: flex;
  cursor: pointer;
  z-index: 10;
  .actions-results {
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    text-decoration: none;
    overflow: hidden;
  }
}
</style>
