<template>
    <div class="hm-forum-partial-section">

        <!-- <v-flex v-if="!subjectId" class="section-info">
            <h2 class="forum-section__title">{{ section.title }}</h2>

            <div class="section__actions" v-if="section.editable">
                <a :href="getSectionEditUrl(section)">
                    <svg-icon name="edit" class="section__actions-icon icon-edit" color="#485B70" height="20" width="20"/>
                </a>
                <a :href="getSectionDeleteUrl(section)">
                    <svg-icon name="delete" class="section__actions-icon icon-delete" color="#485B70" height="20" width="20"/>
                </a>
            </div>
        </v-flex> -->
        <v-btn v-if="Object.keys(section.subsections).length >= 4" elevation="0" color="warning" :href="getCreateSubsectionUrl(section)" target="_top" title="Создать тему"
               class="subsection__create-theme subsection__create-theme_top">
            <svg-icon name="message" class="subsection__create-theme-icon" color="#ffffff" height="20" width="20"></svg-icon>
            <span style="font-size: 16px; font-weight: 300; text-transform: none">Создать тему</span>
        </v-btn>

        <div v-if="Object.keys(section.subsections).length > 0" class="forum-section__themes">
          <hm-forum-topic
            v-for="(subsection, index) in section.subsections"
            :key="index"
            :data="subsection"
            :forum="forum"
            :subjectId="subjectId"
          />
        </div>

        <v-btn elevation="0" color="warning" :href="getCreateSubsectionUrl(section)" target="_top" title="Создать тему"
               class="subsection__create-theme">
            <svg-icon name="message" class="subsection__create-theme-icon" color="#ffffff" height="20" width="20"></svg-icon>
            <span style="font-size: 16px; font-weight: 300; text-transform: none">Создать тему</span>
        </v-btn>

    </div>
</template>


<script>
    import HmCardLink from "@/components/els/hm-card-link"
    import SvgIcon from "@/components/icons/svgIcon"
    import HmForumTopic from "./topic";

    export default {
        name: "HmForumPartialSection",
        components: {HmCardLink, SvgIcon, HmForumTopic},
        props: {
            section: {
                type: Object,
                required: true
            },
            subjectId: {
              type: Number,
              default: null
            },
            forum: {
              type: Object,
              required: true
            }
        },
        data() {
            return {}
        },
        computed: {},
        mounted() {
        },
        methods: {
            // getSectionUrl(section) {
            //     if(this.subjectId) return ['/forum/messages/index/forum_id/', section.forum_id, '/section_id/', section.section_id, '/subject_id/', this.subjectId].join('')
            //     else return ['/forum/messages/index/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
            // },

            getSectionEditUrl(section) {
                return ['/forum/sections/edit/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
            },

            getSectionDeleteUrl(section) {
                return ['/forum/sections/delete/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
            },

            getCreateSubsectionUrl(section) {
                return ['/forum/themes/new/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
            }
        }
    };
</script>

<style lang="scss">
    .hm-forum-partial-section {
      padding-top: 16px;
    }
    .forum-section {

        &__title {
            line-height: 32px;
            font-size: 24px;
            margin-top: 36px;
            margin-bottom: 12px;
        }

        &__themes {
          display: flex;
          flex-direction: column;
        }
    }

    .section-info {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

    .subsection__subsection-content {
        margin-left: 54px; // На ширину subsection-info__user-avatar + padding + margin слева-спаава
    }

    .subsection__link {
        color: #131313 !important;
        text-decoration: none;

        &:hover {
            text-decoration: underline;
        }
    }

    .subsection__subsection-text {
        font-size: 14px;
    }

    .subsection__create-theme {
        width: 153px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 28px;
        font-size: 14px;
        line-height: 24px;
        margin-top: 16px;
        &_top {
          margin-top: 0;
          margin-bottom: 16px;
        }
    }

    .subsection__create-theme-icon {
        margin-right: 5px;
    }

    .section__actions {
        position: relative;
        top: 40px;
        width: 48px;

        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

    .subsection__actions {
        position: absolute;
        width: 80px;
        height: 20px;
        top: 0;
        right: 0;

        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

</style>
