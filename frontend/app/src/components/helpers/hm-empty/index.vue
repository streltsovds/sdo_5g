<template>
  <div class="hm-empty">
    <div class="hm-empty__slot-default" v-if="defaultSlotIsSet">
      <div class="hm-empty__icon">
        <svg-icon name="search"
                  color="#FFFFFF"
                  width="48"
                  height="48"
                  stroke-width=".5"
        />
      </div>
      <slot />
    </div>

    <empty-search v-else-if="emptyType === 'full' && !defaultSlotIsSet"
                  :label="label"
                  :sub-label="subLabel"
                  :icon="!iconBool && icon"
    />
    <empty-block v-if="(emptyType === 'short' || emptyType === 'short-slider') && !defaultSlotIsSet" :type-block="emptyType" :label="label" />
  </div>
</template>

<script>
import EmptySearch from "@/components/helpers/hm-empty/typeEmpty/emptySearch";
import EmptyBlock from "@/components/helpers/hm-empty/typeEmpty/emptyBlock";
import SvgIcon from "@/components/icons/svgIcon";

export default {
  name: "HmEmpty",
  components: {
    EmptyBlock,
    EmptySearch,
    SvgIcon
  },
  props: {
    // 'full' | 'short'
    emptyType: {
      type: String,
      default: 'short'
    },
    label: {
      type:String,
      default: 'Нет данных'
    },
    subLabel: {
      type: String,
      default: 'Попробуйте изменить поисковый запрос'
    },
    icon: {
      type: Boolean,
      default: true
    }
  },
  computed: {
    iconBool() {
      if(this.emptyType === 'full') {
        return Object.keys(this.$slots).length > 0
      } else {
        return false
      }
    },
    defaultSlotIsSet(){
      console.log(this.$slots);
      return Object.keys(this.$slots).includes('default');
    }
  },
}
</script>

<style lang="scss">
.hm-empty {
  width: 100%;
  display: flex;
  align-items: center;
  flex-direction: column;
  &__icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(212, 227, 251);
    margin-bottom: 16px;
  }
  &__slot-default{
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 50px 0 25px 0;
  }
}
</style>
