<template>
  <div class="hm-user-lessons-plan-section">
    <div class="hm-user-lessons-plan-section__title">
      <div class="hm-user-lessons-plan-section__title-wrapper">
        <div class="toggler"
            @click="changeStatus"
        >
          <img v-if="status"
              style="margin-top: 3px;"
              src="/images/icons/circle-arrow-down.svg"
              alt=""
              srcset=""
          >
          <img v-else
              style="margin-top: 6px;"
              src="/images/icons/circle-arrow-up.svg"
              alt=""
              srcset=""
          >
        </div>
      </div>
      <p>{{name}}</p>
    </div>
    <div v-if="status" :style="{background: [themeColors.contentColor]}" class="hm-user-lessons-plan-section__lessons">
        <lesson
          v-for="(item, index) in lessons"
          :key="index"
          :array-card="item"
        />
    </div>
  </div>
</template>
<script>
import Lesson from './row';
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
export default {
  mixins: [VueMixinConfigColors],
  props: {
    lessons: {type:Array, default:()=>{}},
    name: {type:String, default:''},
    id: {type:Number, default:0},
    expanded: {type: Boolean, default: false}
  },
  components: {
    Lesson,
  },
  data() {
    return {
      status: this.expanded
    }
  },

  methods: {
    changeStatus() {
      this.status = !this.status
    },
    changeLessons() {
      this.$emit('change');
    }
  }
}
</script>
<style lang="scss">
.hm-user-lessons-plan-section {
  background-color: #ffffff;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  margin-top: 5px;
  box-shadow: 0 10px 30px rgb(209 213 223 / 50%);
  &__title {
    padding: 10px 24px;
    background-color: #1e1e1e30;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    & p {
      font-weight: 500;
      font-size: 20px;
      line-height: 24px;
      letter-spacing: .02em;
      color: #1e1e1e;
      margin-bottom: 0;
    }
    &-wrapper {
      display: flex;
      align-items: center;
      & .toggler {
        margin-right: 10px;
        display: flex;
        cursor: pointer;
        & img {
          width: 47px;
        }
      }
    }
  }
  &__lessons {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    &-graggable {
      min-height: 100px;
    }
    & > div {
      width: 100%;
    }
    &-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }
  }
  & .hm-user-lessons-plan-row:first-child {
    border-top: none;
  }
  & .user-lessons-plan__icon {
    min-height: min-content;
  }
  & .user-lessons-plan__info {
    margin-top: 16px;
    margin-bottom: 14px;
  }
  & .user-lessons-plan__info-title {
    margin-bottom: 0;
    & span {
      font-size: 16px;
      line-height: 20px;
    }
  }
  & .user-lessons-plan__info-description {
    margin-bottom: 0;
    & span {
      font-size: 12px;
      line-height: 15px;
    }
  }
  & .user-lessons-plan__info-conditions {
    margin-top: 6px;
    & span {
      font-size: 12px;
      line-height: 15px;
    }
  }
  & .user-lessons-plan__icon img {
    width: 30px;
  }
}
</style>
