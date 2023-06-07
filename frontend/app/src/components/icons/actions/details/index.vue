<template>
<div class="actions-details-container" :style="positionCo">
  <v-tooltip left>
    <template  v-slot:activator="{ on: onTooltip }">
      <a
        v-on="onTooltip"
        class="actions-details"
        :href="url">
        <svg width="90"
             height="24"
             viewBox="0 0 90 24"
             fill="none"
             :style="styleCo"
             xmlns="http://www.w3.org/2000/svg"
        >
          <path fill-rule="evenodd"
                clip-rule="evenodd"
                d="M45 23.2382C38.804 23.2382 33.762 18.1962 33.762 12.0002C33.762 5.80421 38.804 0.762207 45 0.762207C51.196 0.762207 56.238 5.80421 56.238 12.0002C56.238 18.1962 51.196 23.2382 45 23.2382ZM45 20.0552C49.442 20.0552 53.055 16.4402 53.055 12.0002C53.055 7.55921 49.441 3.94421 45 3.94421C40.559 3.94421 36.945 7.55921 36.945 12.0002C36.945 16.4412 40.56 20.0552 45 20.0552Z"
                fill="inherit"/>
          <path fill-rule="evenodd"
                clip-rule="evenodd"
                d="M11.239 0.762207C17.435 0.762207 22.477 5.80421 22.477 12.0002C22.477 18.1962 17.434 23.2382 11.238 23.2382C5.042 23.2382 0 18.1962 0 12.0002C0 5.80421 5.043 0.762207 11.239 0.762207ZM3.183 12.0002C3.183 16.4412 6.797 20.0552 11.238 20.0552C15.679 20.0552 19.293 16.4402 19.293 12.0002C19.293 7.55821 15.678 3.94421 11.238 3.94421C6.796 3.94421 3.183 7.55921 3.183 12.0002Z"
                fill="inherit"/>
          <path fill-rule="evenodd"
                clip-rule="evenodd"
                d="M67.524 12.0002C67.524 5.80421 72.566 0.762207 78.762 0.762207C84.958 0.762207 90 5.80421 90 12.0002C90 18.1962 84.958 23.2382 78.762 23.2382C72.566 23.2382 67.524 18.1962 67.524 12.0002ZM70.707 12.0002C70.707 16.4412 74.321 20.0552 78.762 20.0552C83.204 20.0552 86.816 16.4402 86.817 12.0002C86.817 7.55821 83.204 3.94421 78.762 3.94421C74.32 3.94421 70.707 7.55921 70.707 12.0002Z"
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
      default: 'Подробнее'
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
.actions-details-container {
  position: absolute;
  display: flex;
  cursor: pointer;
  z-index: 7;
  .actions-details {
    width: 24px;
    height: 24px;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    text-decoration: none;
  }
}
</style>
