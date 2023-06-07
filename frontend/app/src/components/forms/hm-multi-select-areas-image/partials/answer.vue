<template>
  <div class="hm-multi-select-areas-image-answer">
    <div class="hm-multi-select-areas-image-answer__checkbox">
      <input :name="getInputName('is_correct')"
             :value="(+areaData.is_correct) || 0"
             type="hidden"
      >
      <v-checkbox
        v-model="areaData.is_correct"
        @change="onCheckboxChange"
      />
    </div>
    <div class="hm-multi-select-areas-image-answer__text">
      <v-text-field
        v-model="areaData.variant"
        :name="getInputName('variant')"
        @focus="() => toggleFocusArea(true)"
        @blur="() => toggleFocusArea(false)"
      />
      <input :name="getInputName('data')" :value="JSON.stringify(areaMapData)" type="hidden">
    </div>
    <div class="hm-multi-select-areas-image-answer__remove">
      <div @click="removeArea">
        <svg-icon width="18"
                  height="18"
                  color="inherit"
                  name="close"
                  title="Удалить"
        />
      </div>
    </div>
  </div>
</template>
<script>
import SvgIcon from "@/components/icons/svgIcon"

export default {
  name: "HmMultiSelectAreasImageAnswer",
  components: {
    SvgIcon,
  },
  props: {
    area: {
      type: Object,
      default: () => {}
    }
  },
  data(){
    return {
      areaData: this.area
    }
  },
  computed:{
    isNew(){
      return !this.area.answer_id
    },
    areaMapData(){
      let mapData = {...this.areaData};
      delete mapData['is_correct'];
      return mapData;
    },
  },
  methods:{
    removeArea(){
      this.$emit('removeArea', this.areaData.id);
    },
    getInputName(name){
      let inputName = `variants[${this.area.answer_id || 'new'}][${name}]${this.isNew?'[]':''}`;
      return inputName
    },
    setArea(newA){
      this.$emit('set-area', newA)
    },
    onCheckboxChange(val){
      let newA = {...this.areaData};
      newA.is_correct = val;
      this.areaData = newA;

      this.setArea(this.areaData)
    },
    toggleFocusArea(focus){
      let newA = {...this.areaData};
      newA.isFocus = focus;
      this.areaData = newA;

      this.setArea(this.areaData);
    }
  }
};
</script>
<style lang="scss">
.hm-multi-select-areas-image-answer {
    display: flex;
    justify-content: space-between;
    width: 60%;
    &__text{
        width: 100%;
    }
    &__remove{
        display: flex;
        flex-direction: column;
        justify-content: center;
        svg {
            cursor: pointer;
        }
    }
}
</style>
