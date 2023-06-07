<template>
  <a
    class="edit-actions-button"
    v-on:click="checkAction(confirmAction, actionUrl)">

    <svg-icon :name="iconName" />
    <span>{{ title }}</span>
  </a>
</template>
<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  components: {SvgIcon},
  props: {
    actionUrl: {
      type: String,
      required: '',
    },
    title: {
      type:String,
      default: ''
    },
    iconName: {
      type: String,
      required: true,
    },
    confirmAction: {
      type: Boolean,
      required: false,
    },
  },
  mounted() {
    console.log(this.actionUrl)
  },
  methods: {
    checkAction(confirmAction, url) {
       if(confirmAction) {
        if(confirm('Данное действие может быть необратимым. Вы действительно хотите продолжить?')){
          window.location.href = url;
        }
      } else {
        window.location.href = url;
      }
    }
  }
};
</script>
<style lang="scss">
.edit-actions-button {
  text-decoration: none;
  padding: 12.8px 56px 11px 18px;
  > svg {
    margin-right: 26px;
  }
  > span {
    font-style: normal;
    font-weight: normal;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #333333;
    display: inline-block;
    &:first-letter {
        text-transform: uppercase;
    }
  }
}
.edit-actions-button:hover {
  background: #CEDCF3;
  > span {
    color: #1E1E1E;
  }
}
</style>
