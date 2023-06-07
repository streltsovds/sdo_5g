<template>
  <div class="hm-long-text-tooltip">
    <v-tooltip v-if="tooltipEnabled" bottom>
      <template
        v-slot:activator="{ on: onTooltip }"
      >
        <div
          :style="{ maxWidth: maxWidth }"
          v-on="onTooltip"
          class="hm-long-text-tooltip__activator"
        >
          {{ text }}
        </div>
      </template>

      <div
        class="hm-long-text-tooltip__content"
      >
        {{ text }}
      </div>
    </v-tooltip>

    <span v-else class="hm-long-text-tooltip__short-text">
      {{ text }}
    </span>
  </div>
</template>

<script>
/**
 * Компонент, позволяющий уместить текстовую строку в указанную ширину,
 * отрезав её символом "...".
 *
 * Текст полностью можно прочитать во всплывающей подсказке,
 * появляющейся при наведении мыши.
 */
export default {
  name: "HmLongTextTooltip",
  props: {
    text: {
      type: String,
      default: "",
    },
    maxWidth: {
      type: String,
      default: "200px",
    },
    /**
     * Мы можем определять переход в ellipsis только с помощью сложного js
     * (временно внедрять невидимый текст в документ, добавлять по букве и замерять ширину),
     * пока просто отключим обёртывание в tooltip для заведомо коротких строк
     */
    minCharsEnable: {
      type: Number,
      default: 25,
    },
  },
  computed: {
    tooltipEnabled() {
      return String(this.text).length > this.minCharsEnable;
    },
  },
};
</script>

<style lang="scss">
.hm-long-text-tooltip {
  &__activator {
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
  }
  &__content {
    max-width: 360px;
  }
}
</style>
