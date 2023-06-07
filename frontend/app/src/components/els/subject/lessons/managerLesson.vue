<template>
  <div
    :style="{background: [themeColors.contentColor]}"
    class="subject-lessons-edit-lesson"
    :class="{'subject-lessons-edit_tile__column': $vuetify.breakpoint.xsOnly}">
    <div class="subject-lessons-edit-lesson__icon">
      <a v-if="lesson.isClickable" :href="lesson.executeUrl"><img :src="lesson.iconUrl" alt=""></a>
      <span v-else><img :src="lesson.iconUrl" alt=""></span>
      <span class="subject-lessons-edit-lesson__type">{{ lesson.lessonType }}</span>
    </div>
    <div class="subject-lessons-edit-lesson__info">
      <div
        v-if="lesson.lessonTitle && lesson.lessonTitle !== ''"
        class="subject-lessons-edit-lesson__info-title">
        <template v-if="lesson.isClickable">
          <hm-text-link-edit
                  :hrefUrl="lesson.executeUrl"
                  :lessonId="lesson.lessonId"
                  :receiverUrl="receiverUrl"
                  :setLink="true">
            {{ lesson.lessonTitle }}
          </hm-text-link-edit>
        </template>
        <span v-else>{{ lesson.lessonTitle }}</span>
      </div>
      <div
        v-if="lesson.lessonDescription && lesson.lessonDescription !== ''"
        class="subject-lessons-edit-lesson__info-description">
        <span>
            {{ lesson.lessonDescription }}
        </span>

      </div>
      <div class="subject-lessons-edit-lesson__info-date">
        <div
          v-if="lesson.lessonDate && lesson.lessonDate.begin && lesson.lessonDate.begin !== ''"
          class="subject-lessons-edit-lesson__info-date__start">
          <div class="date-title"><span>Начало занятия</span></div>
          <div class="dates-info">
            <svg-icon name="timeStart"></svg-icon>
            <span class="dates-info__text">{{ lesson.lessonDate.begin }}</span>
          </div>
        </div>
        <div
          v-if="lesson.lessonDate && lesson.lessonDate.end && lesson.lessonDate.end !== ''"
          class="subject-lessons-edit-lesson__info-date__end">
          <div class="date-title"><span>Окончание занятия</span></div>
          <div class="dates-info">
            <svg-icon name="timeEnd"></svg-icon>
            <span class="dates-info__text">{{ lesson.lessonDate.end }}</span>
          </div>
        </div>
        <div
          v-if="!lesson.lessonDate.end || lesson.lessonDate.end === ''"
          class="user-lessons-plan__info-rest__date__end">
          <div class="date-title"><span>Время обучения</span></div>
          <div class="dates-info">
            <svg-icon name="timeEnd"></svg-icon>
            <span class="dates-info__text">Не ограничено</span>
          </div>
        </div>
      </div>
      <div
        v-if="lesson.lessonCondition && lesson.lessonCondition !== ''"
        class="subject-lessons-edit-lesson__info-conditions">
        <span v-html="lesson.lessonCondition"></span>
      </div>
    </div>
    <div class="subject-lessons-edit-lesson__actions">
      <v-menu offset-y offset-x left>
        <template v-slot:activator="{ on }">
          <div
            class="button-shape"
            v-on="on">
            <svg width="4" height="17" viewBox="0 0 5 21" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M4.99512 10.3423C4.99512 11.7193 3.87458 12.8398 2.49756 12.8398C1.12055 12.8398 0 11.7193 0 10.3423C0 8.96527 1.12055 7.84472 2.49756 7.84472C3.87458 7.84472 4.99512 8.96527 4.99512 10.3423ZM4.28725 10.3426C4.28725 9.35535 3.48384 8.55239 2.49708 8.55239C1.51011 8.55239 0.706699 9.35557 0.706699 10.3426C0.706699 11.3295 1.51011 12.1327 2.49708 12.1327C3.48406 12.1327 4.28725 11.3293 4.28725 10.3426Z" fill="inherit"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M0 17.8421C0 16.465 1.12055 15.3445 2.49756 15.3445C3.87458 15.3445 4.99512 16.4653 4.99512 17.8423C4.99512 19.2193 3.87458 20.3398 2.49756 20.3398C1.12055 20.3398 0 19.2191 0 17.8421ZM2.49708 19.6334C3.48406 19.6334 4.28725 18.8302 4.28725 17.8432C4.28725 16.8563 3.48384 16.0531 2.49708 16.0531C1.50988 16.0531 0.706699 16.8565 0.706699 17.8432C0.706699 18.8304 1.51011 19.6334 2.49708 19.6334Z" fill="inherit"/>
              <path fill-rule="evenodd" clip-rule="evenodd" d="M2.49756 5.33594C1.12055 5.33594 0 4.21539 0 2.83838C0 1.46136 1.12055 0.340816 2.49756 0.340816C3.87458 0.340816 4.99512 1.46136 4.99512 2.83838C4.99512 4.21539 3.87458 5.33594 2.49756 5.33594ZM2.49708 4.62878C3.48406 4.62878 4.28725 3.82559 4.28725 2.83861C4.28725 1.85141 3.48384 1.04867 2.49708 1.04845C1.50988 1.04845 0.706699 1.85141 0.706699 2.83861C0.706699 3.82582 1.51011 4.62878 2.49708 4.62878Z" fill="inherit"/>
            </svg>
          </div>
        </template>
        <div
          class="subject-lessons-edit-lesson__actions-menu"
          :style="{background: [themeColors.contextMenu]}"
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
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import HmContextMenuButton from "@/components/layout/hm-context-menu-button/index";
import EditActionButton from "@/components/els/subject/lessons/editActionButton";
import HmTextLinkEdit from "@/components/helpers/hm-text-link-edit";
import VueMixinConfigColors from "@/utilities/mixins/VueMixinConfigColors";
export default {
  name: "managerLesson",
  mixins: [VueMixinConfigColors],
  components: {EditActionButton, HmContextMenuButton, SvgIcon, HmTextLinkEdit},
  props: {
    lesson: {type:Object, default:()=>{}},
    receiverUrl: {
      type: String,
      default: '/lesson/ajax/change-title'
    },
    receiverDescriptionUrl: {
      type: String,
      default: '/lesson/ajax/change-description'
    },
    descriptionParams: {
      type: Array,
      default: () => [4895]
    }
  },
  computed: {
    order() {
      let newOrder = [];
      this.lesson.editAssignUrl     ? newOrder.push({data:this.lesson.editAssignUrl,name:'назначить участников',iconName:'staff-recruitment'}) : '';
      this.lesson.deleteUrl         ? newOrder.push({data:this.lesson.deleteUrl,name:'удалить',iconName:'Delete',confirm:true})                : '';
      this.lesson.editMaterialUrl   ? newOrder.push({data:this.lesson.editMaterialUrl,name:'редактировать материал',iconName:'reports'})       : '';
      this.lesson.changeMaterialUrl ? newOrder.push({data:this.lesson.changeMaterialUrl,name:'заменить материал',iconName:'MaterialEdit'})     : '';
      this.lesson.editUrl           ? newOrder.push({data:this.lesson.editUrl,name:'редактировать занятие',iconName:'Edit'})                   : '';
      this.lesson.resultsUrl        ? newOrder.push({data:this.lesson.resultsUrl,name:'результаты',iconName:'Result'})                         : '';
      this.lesson.videoUrl          ? newOrder.push({data:this.lesson.videoUrl,name:'просмотр записей',iconName:'Play'})                       : '';
      this.lesson.proctoringUrl     ? newOrder.push({data:this.lesson.proctoringUrl,name:'контролировать прохождение',iconName:'Proctoring'})                        : '';
      newOrder.sort((a,b)=> {
          return a.data.order - b.data.order
      });

      return newOrder
    }
  }
}
</script>

<style lang="scss">
.subject-lessons-edit-lesson {
  display: flex;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  background: #FFFFFF;
  cursor: grab;
  &__icon {
    width: auto;
    height: auto;
    min-height: 165px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0 40.5px 0 38px;
    display: flex;
    flex-direction: column;
    img {
      width: 48px;
    }
    span {
      font-size: 0.75rem;
      color: #bbb;
      line-height: .9rem;
      text-align: center;
    }
  }
  &__type {
    min-width: 100px;
  }
  &__info {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    margin: 26px 0 24px 0;
    padding-right: 74px;
    &-title {
        margin-bottom: 12px;
        > a {
          text-decoration: none;
        }
        span {
          font-weight: 500;
          font-size: 20px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
          mix-blend-mode: normal;
        }
      }
    &-description {
        margin-bottom: 16px;
        > span {
          font-weight: normal;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
      }
    &-date {
        display: flex;
        > div:first-child {
          margin-right: 46px;
        }
        > div {
          .date-title {
            > span {
              font-weight: normal;
              font-size: 12px;
              line-height: 18px;
              letter-spacing: 0.15px;
              color: #696969;
            }
          }
          .dates-info {
            > svg {
              margin-right: 6px;
            }
            > span {
              font-weight: 500;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #696969;
            }
          }
        }
        > span {
          margin-top: 14px;
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #70889E;
        }
      }
    &-conditions {
        margin-top: 14px;
        > span {
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #70889E;
        }
      }
  }
  &__actions {
    margin-top: 27px;
    margin-right: 20px;
    .button-shape {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
      cursor: pointer;
      > svg {
        fill: #212121;
      }
      &:hover {
        background: rgba(102, 102, 102, 0.14);
        > svg {
          fill: #3796eb;
        }
      }
    }
    &-menu {
      display: flex;
      flex-direction: column;
      background: #FFFFFF;
      box-shadow: 0 8px 10px rgba(0, 0, 0, 0.2), 0 6px 30px rgba(0, 0, 0, 0.12);
      border-radius: 4px;
      > a:hover {
        > svg {

        }
      }
    }
  }
  }
.subject-lessons-edit-lesson:active {
  cursor: grabbing !important;
  background: rgba(170, 177, 186, 0.4) !important;
  position: relative;
}
.sortable-chosen {
    background: rgba(170, 177, 186, 0.4) !important;
}
</style>
