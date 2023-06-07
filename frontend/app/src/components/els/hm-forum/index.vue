<template>
  <v-card class="hm-forum">
    <v-tabs
      v-model="tab"
    >
      <v-tab
        v-for="(section, index) in forum.sections"
        :key="index"
      >
        <div class="hm-forum__tab">
          <hm-section-name
            :text="section.title"
            :sectionId="section.section_id"
            :changeSectionId="changeSectionId"
            :url="`/forum/sections/edit/forum_id/${section.forum_id}/section_id/${section.section_id}`"
            @changeStatus="changeNameSectionReturn"
          />

          <div class="hm-forum__tab-actions" v-if="section.editable">
            <v-btn @click.stop="changeNameSection(section.section_id)" elevation="0" icon x-small class="hm-forum__tab-action">
              <svg-icon name="edit" class="hm-forum__tab-actions-icon icon-edit" color="#485B70" height="14" width="14"/>
            </v-btn>
            <v-btn @click.stop v-if="statusRenderButton(section)" elevation="0" icon x-small class="hm-forum__tab-action" @click="openDialog(section)" >
              <svg-icon name="delete" class="hm-forum__tab-actions-icon icon-delete" color="#485B70" height="14" width="14"/>
            </v-btn>
          </div>
        </div>
      </v-tab>
      <div class="hm-forum__add-btn-wrapper v-card">
        <v-btn
          class="hm-forum__add-btn"
          v-if="(actions.length > 0) && forum.editable"
          :href="actions[0].href"
          elevation="0"
          icon
        >
          <svg-icon
            :name="actions[0].icon"
            :title="actions[0].label"
            :width="16"
            :height="16"
            :stroke-width="0.5"
            color="#485B70"
          />
        </v-btn>
      </div>
      <v-tabs-items v-model="tab">
        <v-tab-item
          v-for="(section, index) in forum.sections"
          :key="index"
        >
          <hm-forum-partial-section :forum="forum" :section="section" :subjectId="forum.subject_id" />
        </v-tab-item>
      </v-tabs-items>
    </v-tabs>
    <hm-dialog
      :status="dialog.status"
      size="small"
      semanticAccent="warning"
    >
      <template v-slot:content>
        <p>{{ dialog.text }}</p>
      </template>
      <template v-slot:buttons>
        <div>
          <v-btn
            :href="dialog.deleteUrl"
            :disabled="dialog.loading"
            @click="dialog.loading = true"
            :loading="dialog.loading"
            color="primary"
          >
            Да
          </v-btn>
          <v-btn :disabled="dialog.loading" @click="dialogClose" color="primary" text>
            Отмена
          </v-btn>
        </div>
      </template>
    </hm-dialog>
    <hm-empty v-if="Object.keys(forum.sections).length === 0 || (forum.subject_id && Object.values(forum.sections)[0].subsections.length === 0)">Нет данных для отображения</hm-empty>
  </v-card>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import hmCardLink from "@/components/els/hm-card-link";
import HmForumPartialSection from "@/components/els/hm-forum/partials/section";
import hmPartialsActions from "@/components/layout/hm-partials-actions";
import hmEmpty from "@/components/helpers/hm-empty";
import svgIcon from "@/components/icons/svgIcon";
import HmDialog from "@/components/controls/hm-dialog/HmDialog.vue";
import HmSectionName from "./components/sectionName";

export default {
  name: "HmForum",
  components: {
    HmForumPartialSection,
    SvgIcon,
    hmCardLink,
    hmPartialsActions,
    hmEmpty,
    svgIcon,
    HmDialog,
    HmSectionName
  },
  props: {
    forum: {
      type: Object,
      required: true
    },
    actions: {
      type: Object,
      required: false
    },
  },
  data() {
    return {
      tab: null,
      dialog: {
        status: false,
        text: "",
        deleteUrl: "",
        loading: false
      },
      changeSectionId: ""
    }
  },
  methods: {
    click(e) {
      console.log(e)
    },
    changeNameSectionReturn() {
      this.changeSectionId = ""
    },
    changeNameSection(id) {
      if(!this.changeSectionId) this.changeSectionId = id;
      else if(this.changeSectionId !== id) this.changeSectionId = id;
      else this.changeSectionId = "";
    },

    // Прямой урл или ajax-форма?
    getCreateSectionUrl(forum) {
      return ['/forum/sections/new/forum_id/', forum.forum_id].join('');
    },
    getSectionEditUrl(section) {
      return ['/forum/sections/edit/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
    },

    getSectionDeleteUrl(section) {
      return ['/forum/sections/delete/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
    },

    statusRenderButton(section) {
      if(section.forum_id === 1 && section.section_id === 1) return false
      else return true
    },

    openDialog(section) {
      this.dialog = {
        status: true,
        text: "Вы действительно желаете удалить раздел форума и все сообщения в нём?",
        deleteUrl: this.getSectionDeleteUrl(section),
        loading: false
      }
    },
    dialogClose() {
      this.dialog = {
        status: false,
        text: "",
        deleteUrl: "",
        loading: false
      }
    }
  }
};
</script>
<style lang="scss">
    .hm-forum {
        padding: 22px 26px;

        &__tab {
          max-width: 350px;
          &-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 270px;
            display: block;
          }
        }

        & .v-tabs-bar {
          margin: -22px -26px;
          margin-bottom: 0;
          width: calc(100% + 48px);
          max-width: calc(100% + 48px);
        }

        & .v-slide-group__prev,
        & .v-slide-group__next {
          display: none !important;
        }

        & .v-slide-group__wrapper {
          position: relative;
          overflow-x: auto;
          &::-webkit-scrollbar { display: none; }
        }
        & .v-slide-group__content {
          position: static;
          transform: translateX(0px) !important;
        }

        &__tab {
          display: flex;
          align-items: center;
          justify-content: space-between;
          height: 100%;
          &-actions {
            display: flex;
            align-items: center;
          }
          &-action {
            margin-left: 5px;
            &:hover {
              & svg * {
                fill: #2574CF;
              }
            }
          }
        }
        &__add-btn-wrapper.v-card {
          position: sticky;
          right: 0px;
          top: 0px;
          z-index: 100;
          height: 100%;

          display: flex;
          align-items: center;
          justify-content: center;
          box-shadow: none !important;
        }
        &__add-btn {
          &:hover {
            & svg * {
              stroke: #2574CF;
            }
          }
        }

        .hm-partials-actions {
            padding: 0;
        }

        .hm-partials-actions a {
            background-color: #1F8EFA !important;
            color: #FFFFFF !important;
            border-radius: 4px !important;
        }
        .hm-partials-actions a * {
            stroke: #FFFFFF !important;
            fill: #FFFFFF !important;
        }
    }
    @media(max-width: 599px) {
      .hm-forum {
        margin: 0 -16px;
        width: 100vw;
        max-width: 100vw;
        padding: 16px;
        padding-top: 22px;
        & .v-tabs-bar {
          margin: -22px -16px;
          margin-bottom: 0;
          width: calc(100% + 32px);
          max-width: calc(100% + 32px);
        }
      }
    }
</style>
