<template>
  <div class="INFOBLOCK infoblock-faq-block" ref="infoBlockFaq">
    <v-expansion-panels v-if="faqs.length > 0">
      <v-expansion-panel
        v-for="(item,i) in faqs"
        :key="i"
      >
        <v-expansion-panel-header>{{ item.question }}</v-expansion-panel-header>
        <v-expansion-panel-content>
          {{ item.answer }}
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
    <hm-empty v-else>
      Нет данных для отображения
    </hm-empty>
  </div>
</template>
<script>
import { mapActions } from "vuex";
import HmEmpty from "@/components/helpers/hm-empty";

export default {
  components: {HmEmpty},
  props: {
    faqs: {
      type: Array,
      default: () => []
    },
  },
};
</script>
<style lang="scss">
.infoblock-faq-block {


}
</style>
