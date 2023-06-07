<template>
    <v-card class="subject-lessons-section">
    <div class="subject-lessons-section__title">
      <div class="subject-lessons-section__title-wrapper">
        <div class="toggler"
            @click="changeStatus"
        >
          <!-- <img v-if="status"
              style="margin-top: 3px;"
              src="/images/icons/circle-arrow-down.svg"
              alt=""
              srcset=""
          > -->
          <icon v-if="status" style="transform: rotate(180deg)" />
          <icon v-else />
          <!-- <img v-else
              style="margin-top: 6px;"
              src="/images/icons/circle-arrow-up.svg"
              alt=""
              srcset=""
          > -->
        </div>
        <hm-text-link-edit
          :lessonId="id"
          :receiverUrl="receiverUrl"
          type="section"
        >
          {{ name }}
        </hm-text-link-edit>
      </div>
      <v-menu offset-y offset-x left>
        <template v-slot:activator="{ on }">
          <div
            class="button-shape"
            v-on="on">
            <svg width="5" height="21" viewBox="0 0 5 21" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M4.99512 10.3423C4.99512 11.7193 3.87458 12.8398 2.49756 12.8398C1.12055 12.8398 0 11.7193 0 10.3423C0 8.96527 1.12055 7.84472 2.49756 7.84472C3.87458 7.84472 4.99512 8.96527 4.99512 10.3423ZM4.28725 10.3426C4.28725 9.35535 3.48384 8.55239 2.49708 8.55239C1.51011 8.55239 0.706699 9.35557 0.706699 10.3426C0.706699 11.3295 1.51011 12.1327 2.49708 12.1327C3.48406 12.1327 4.28725 11.3293 4.28725 10.3426Z" fill="inherit"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M0 17.8421C0 16.465 1.12055 15.3445 2.49756 15.3445C3.87458 15.3445 4.99512 16.4653 4.99512 17.8423C4.99512 19.2193 3.87458 20.3398 2.49756 20.3398C1.12055 20.3398 0 19.2191 0 17.8421ZM2.49708 19.6334C3.48406 19.6334 4.28725 18.8302 4.28725 17.8432C4.28725 16.8563 3.48384 16.0531 2.49708 16.0531C1.50988 16.0531 0.706699 16.8565 0.706699 17.8432C0.706699 18.8304 1.51011 19.6334 2.49708 19.6334Z" fill="inherit"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M2.49756 5.33594C1.12055 5.33594 0 4.21539 0 2.83838C0 1.46136 1.12055 0.340816 2.49756 0.340816C3.87458 0.340816 4.99512 1.46136 4.99512 2.83838C4.99512 4.21539 3.87458 5.33594 2.49756 5.33594ZM2.49708 4.62878C3.48406 4.62878 4.28725 3.82559 4.28725 2.83861C4.28725 1.85141 3.48384 1.04867 2.49708 1.04845C1.50988 1.04845 0.706699 1.85141 0.706699 2.83861C0.706699 3.82582 1.51011 4.62878 2.49708 4.62878Z" fill="inherit"/>
            </svg>
          </div>
        </template>
        <div
          class="subject-lessons-edit-lesson__actions-menu"
        >
            <edit-action-button
              v-for="item in order"
              :title="_(item.name)"
              :icon-name="item.iconName"
              :action-url="item.data.url"
              :confirm-action="item.confirm"
            />
        </div>
      </v-menu>
    </div>
    <div v-if="status" :style="{background: [themeColors.contentColor]}" class="subject-lessons-section__lessons">
      <p class="subject-lessons-section__lessons-text" v-if="lessons.length === 0">Перетащите занятие</p>
      <draggable
        class="subject-lessons-section__lessons-graggable"
        :list="lessons"
        @start="drag = true"
        @end="drag = false"
        @change="changeLessons"
        :scroll-sensitivity="150"
        :force-fallback="true"
        group="lessons"
      >
        <manager-lesson
          v-for="(lesson, i) in lessons"
          :lesson="lesson"
          :key="`lesson-${i}`"
        />
      </draggable>
    </div>
  </v-card>
</template>
<script>
import ManagerLesson from './managerLesson';
import draggable from "vuedraggable";
import HmTextLinkEdit from "@/components/helpers/hm-text-link-edit";
import EditActionButton from "@/components/els/subject/lessons/editActionButton";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
import Icon from './icon';
export default {
  mixins: [VueMixinConfigColors],
  props: {
    lessons: {type:Array, default:()=>{}},
    name: {type:String, default:''},
    id: {type:Number, default:0},
    deleteUrl: {type: String, default: 0},
    receiverUrl: {
      type: String,
      default: '/section/ajax/change-title'
    },
    expanded: {type: Boolean, default: false}
  },
  components: {
    ManagerLesson,
    draggable,
    HmTextLinkEdit,
    EditActionButton,
    Icon
  },
  data() {
    return {
      order: [{
        "data":{
          "url": this.deleteUrl,
          "order":1
        },
        "name":"удалить",
        "iconName":"Delete",
        "confirm":true
      }],
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
.subject-lessons-section {
  background-color: #ffffff;
  margin-top: 16px;
  box-shadow: 0 10px 30px rgb(209 213 223 / 50%);
  &__title {
    padding: 26px 24px;
    // background-color: inherit;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #EDF4FC;
    & * {
      fill: #4A90E2;
    }
    & .button-shape {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      cursor: pointer;
      &:hover {
        & svg {
          fill: #3796eb !important;
          * {
            fill: #3796eb !important;
          }
        }
      }
      & svg {
        fill: #1e1e1e70 !important;
        * {
            fill: #1e1e1e70 !important;
          }
      }
    }
    span {
      font-weight: 500;
      font-size: 20px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
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
  & .subject-lessons-edit-lesson:first-child {
    border-top: none;
  }
  & .subject-lessons-edit-lesson__icon {
    min-height: min-content;
  }
  & .subject-lessons-edit-lesson__info {
    margin-top: 16px;
    margin-bottom: 14px;
  }
  & .subject-lessons-edit-lesson__actions {
    margin-top: 16px;
    margin-right: 20px;
  }
  & .subject-lessons-edit-lesson__info-title {
    margin-bottom: 0;
    & span {
      font-size: 16px;
      line-height: 20px;
    }
  }
  & .subject-lessons-edit-lesson__info-description {
    margin-bottom: 0;
    & span {
      font-size: 12px;
      line-height: 15px;
    }
  }
  & .subject-lessons-edit-lesson__info-conditions {
    margin-top: 6px;
    & span {
      font-size: 12px;
      line-height: 15px;
    }
  }
  & .subject-lessons-edit-lesson__icon img {
    width: 30px;
  }
}
</style>
