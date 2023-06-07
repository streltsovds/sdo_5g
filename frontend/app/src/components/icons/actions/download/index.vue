<template>
<div class="actions-download-container" :style="positionCo">
  <v-tooltip left>
    <template  v-slot:activator="{ on: onTooltip }">
      <a
        v-on="onTooltip"
        class="actions-download"
        :href="url">
        <svg width="90"
             height="90"
             viewBox="0 0 90 90"
             fill="none"
             :style="styleCo"
             xmlns="http://www.w3.org/2000/svg"
        >
          <path d="M88.408 49.2232C87.528 49.2232 86.816 49.9352 86.816 50.8162V78.1632C86.816 82.6012 83.204 86.2162 78.761 86.2162H11.238C6.797 86.2162 3.183 82.6022 3.183 78.1632V50.8152C3.183 49.9342 2.47 49.2222 1.591 49.2222C0.712 49.2222 0 49.9352 0 50.8152V78.1622C0 84.3572 5.042 89.3992 11.238 89.3992H78.761C84.958 89.3992 90 84.3572 90 78.1622V50.8152C90 49.9352 89.288 49.2232 88.408 49.2232Z"
                fill="inherit"/>
          <path d="M41.8289 61.9901C43.5769 63.7381 46.4229 63.7401 48.1719 61.9901L73.9389 36.2211C74.5599 35.6001 74.5599 34.5931 73.9389 33.9721C73.3179 33.3501 72.3089 33.3501 71.6879 33.9721L46.5919 59.0681V2.19407C46.5919 1.31307 45.8799 0.601074 44.9999 0.601074C44.1209 0.601074 43.4079 1.31307 43.4079 2.19407V59.0691L18.3119 33.9731C17.6919 33.3511 16.6829 33.3511 16.0619 33.9731C15.4409 34.5941 15.4409 35.6011 16.0619 36.2221L41.8289 61.9901Z"
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
      default: 'Скачать'
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
      default: () => [12,14]
    },
    url: {
      type: String,
      default: ''
    }
  },
  computed: {
    positionCo() {
      return {top: (this.position[0] - 4)+'px', right: this.position[1]+'px'}
    },
    styleCo() {
       return {transform: `scale(${this.size})`, fill: this.color}
    }
  }
}
</script>

<style lang="scss">
.actions-download-container {
  position: absolute;
  display: flex;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  overflow: hidden !important;
  z-index: 3;
  &:hover {
    background-color: rgba(30, 30, 30, 0.08);
  }
  &:active {
    background-color: rgba(30, 30, 30, 0.24);
  }
  .actions-download {
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
