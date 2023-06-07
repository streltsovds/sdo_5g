<template>
  <div :class="classes" v-on="$listeners">
    <div>
      <slot name="icon">
        <svg-icon color="#1E1E1E"
                  stroke-width=".5"
                  width="16px"
                  name="rotation"
        />
      </slot>
      <span><slot>Загрузить еще</slot></span>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";

export default {
  name: "HmLoadMoreBtn",
  components:{
    SvgIcon
  },
  props: {
    inProgress: {
      type: Boolean,
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false,
    }
  },
  computed:{
    classes(){
      let classes = ['hm-load-more-btn'];
      if(this.inProgress){
        classes.push('hm-load-more-btn--in-progress');
      }
      if(this.isDisabled){
        classes.push('hm-load-more-btn--disabled');
      }
      return classes.join(' ');
    }
  }
};
</script>

<style lang="scss">
  .hm-load-more-btn{
    width: 100%;
    height: 29px;
    display: flex;
    justify-content: center;
    align-items: center;
    &--disabled{
      opacity: 0.7;
    }
    &--in-progress{
      svg {
        animation:spin 1.2s linear infinite;
      }
    }
    > div {
      width: 191px;
      height: 100%;
      border: 1px solid #1F8EFA;
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      &:hover{
        background-color: #5181B8;
        border: 1px solid #5181B8;
        span{
          color: #fff;
        }
        svg *{
          fill: #fff;
          stroke: #fff;
        }
      }
      &:active{
        background-color: #40638b;
      }
      > svg {
        margin-right: 8px;
      }
      > span {
        font-style: normal;
        font-size: 13px;
        line-height: 24px;
        letter-spacing: 0.16px;
        color: #1E1E1E;
      }
    }
  }
  @keyframes spin { 100% { -webkit-transform: rotate(360deg); transform:rotate(360deg); } }
</style>
