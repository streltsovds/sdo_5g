<template>
  <div class="INFOBLOCK infoblock-schedule-daily">
    <hm-empty v-if="dataDaily.lessonAssigns.length === 0"
    >
      {{ _('Нет данных для отображения') }}
    </hm-empty>
    <div class="infoblock-schedule-daily__contains"
         v-else
    >
      <list v-for="(dataList, key) in arrayList"
            :data-list="dataList"
            :title="dataList.title"
            :show="key === 0 ? true : false"
            :key="key"
      />
    </div>
  </div>
</template>
<script>
import List from "@/components/layout/infoblocks/hm-schedule-daily/listDaily";
import HmEmpty from "@/components/helpers/hm-empty";
export default {
  components: {List, HmEmpty},
  props: {
    dataDaily: {
      type: [Array, Object, String],
      default: () => []
    }
  },
  data() {
    return {
      isOpen: []
    };
  },
  computed: {
    arrayList() {
      let newArray = [];
      for(let i in this.dataDaily.lessonAssigns) {
        newArray.push({
          title:i,
          data: this.dataDaily.lessonAssigns[i]
        })
      };
      return newArray
    }
  },
};
</script>
<style lang="scss">
.infoblock-schedule-daily {
  width: 100%;
  height: auto;
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
  &__contains {
    width: 96%; /* не прилипать к скроллу */
    display: flex;
    justify-content: flex-start;
    align-items: flex-start;
    flex-direction: column;
    > div {
      &:not(:last-child) {
        border-bottom: 1px solid rgba(18, 91, 181, 0.1);
      }
    }
  }
}
@media(max-width: 768px) {
  .infoblock-schedule-daily {
    padding: 0 16px;
    &__contains {
      width: 100%;
    }
  }
}
</style>
