<template>
  <hm-modal
    :class-name="`hm-card-link__modal hm-card-modal-course` +' ' +styleCard"
    :is-shown="isOpen"
    :fullscreen="fullscreen"
    :close-btn="!fullscreen"
    :class="userCard ? 'red' : 'blue'"
    @close="$emit('close')"
  >
    <v-toolbar v-if="fullscreen"
               dark
               color="primary"
               fixed
    >
      <v-btn @click="$emit('close')" icon dark>
        <v-icon>close</v-icon>
      </v-btn>
      <v-spacer />
      <v-toolbar-items>
        <v-btn
          v-for="(link, key) in links"
          :key="key"
          :href="link.url"
          v-text="link.name"
          dark
          text
          target="_blank"
        />
      </v-toolbar-items>
    </v-toolbar>
    <v-card class="hm-card-modal-course__content">
      <h3 class="hm-card-modal-course__title">{{ title }}</h3>
      <div class="hm-card-modal-course__subject" v-if="subjectCard">
        <div class="hm-card-modal-course__photo" v-if="photo" :style="'background-image: url(' + photo + ');'" />
        <div class="hm-card-modal-course__photo default" v-else :style="'background-image: url(' + defaultPhoto + ');'" />
        <p class="hm-card-modal-course__description" v-if="description">{{description}}</p>
      </div>
      <v-card-text class="hm-card-modal-course__text">
        <div class="hm-card-modal-course__text-description" v-if="content">
          <hm-dependency :template="content" />
        </div>
        <!--        данные по карте после заголовка-->
        <v-layout v-if="fields && fields.length > 0" wrap>
          <v-flex>
            <v-list class="hm-card-modal-course__list" >
              <v-list-item
                class="hm-card-modal-course__text-info"
                v-for="(field, key) in fields"
                v-if="field.key !== 'Краткое описание' && field.value !== undefined && field.value !== null && field.value !== ''"
                :key="key"
              >
                <v-list-item-content>
                  <div  class="hm-card-modal-course__text-wrapper">
                    <icons :type="field.key" class="hm-card-modal-course__text-icon"/>
                    <div style="max-width: calc(100% - 48px)">
                      <v-list-item-subtitle class="hm-card-modal-course__text-info__header">
                        {{ field.key }}
                      </v-list-item-subtitle>
                      <v-list-item-title>
                        <span class="hm-card-modal-course__text-info__body-text" v-html="field.value" />
                      </v-list-item-title>
                    </div>
                  </div>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-flex>
        </v-layout>
        <div class="hm-card-modal-course__nav">
          <div class="hm-card-modal-course__nav-item hm-card-modal-course__nav-prev">
            <v-tooltip bottom>
              <v-btn
                slot="activator"
                :disabled="!prevLink"
                @click="openNavLink(prevLink)"
                icon
                text
                color="primary"
              >
                <v-icon class="float-left">
                  chevron_left
                </v-icon>
                <span>{{ prevLinkText }}</span>
              </v-btn>
            </v-tooltip>
          </div>
          <div class="hm-card-modal-course__nav-item hm-card-modal-course__nav-next">
            <v-tooltip bottom>
             <v-btn
                slot="activator"
                :disabled="!nextLink"
                @click="openNavLink(nextLink)"
                icon
                text
                color="primary"
              >
                <span>{{ _(nextLinkText) }}</span>
                <v-icon class="float-right">
                  chevron_right
                </v-icon>
              </v-btn>
            </v-tooltip>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </hm-modal>
</template>
<script>
import HmModal from "@/components/layout/hm-modal";
import HmDependency from "@/components/helpers/hm-dependency";
import icons from "./icons/icons";
export default {
  components: { HmModal, HmDependency, icons },
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: null
    },
    content: {
      type: String,
      default: null
    },
    fields: {
      type: Array,
      default: () => []
    },
    photo: {
      type: String,
      default: null
    },
    defaultPhoto: {
      type: String,
      default: null
    },
    fullscreen: {
      type: Boolean,
      default: false
    },
    links: {
      type: Array,
      default: () => []
    },
    nextLinkText: {
      type: String,
      default: "Вперед"
    },
    prevLinkText: {
      type: String,
      default: "Назад"
    },
    rel: {
      type: String,
      default: null
    },
    typeCard: {// тип карты для пользователя - карточка и карточка пользователя
      type: String,
      default: null
    },
  },
  data() {
    return {
      prevLink: null,
      nextLink: null,
      userCard: false,
      subjectCard: false,
      otherCard: false,
      description: null
    };
  },
  computed: {
    styleCard() {
      return this.description ? 'big-card' : 'small-card'
    },
    stylePhoto() {
      return this.photo.indexOf('/images/icons/subject/exam.svg') !== -1 ? 'card-photo__nophoto' : 'card-photo'
    },
  },
  mounted() {
    //определение, если карточка пользователя
    this.userCard = this.typeCard.toLowerCase() === 'карточка' || this.typeCard.toLowerCase() === 'карточка пользователя' || this.typeCard.toLowerCase() === 'тьютор';
    this.subjectCard = this.typeCard.toLowerCase() === 'карточка учебного курса' || this.typeCard.toLowerCase() === 'карточка учебной сессии';
    this.otherCard = !this.userCard && !this.subjectCard;
    this.description = this.getDescription();
    this.$nextTick(() => this.init());
  },
  methods: {
    init() {
      const selector = `[rel=${this.rel}]`;
      let currentRelLink = this.$el.parentNode.querySelector(selector);

      let allRelLink = document.querySelectorAll(selector);

      let indexCurrentRelLink = null;
      allRelLink.forEach((link, key) => {
        if (currentRelLink.isSameNode(link)) indexCurrentRelLink = key;
      });

      if (indexCurrentRelLink === null) return;

      if (indexCurrentRelLink - 1 > 0)
        this.prevLink = allRelLink[indexCurrentRelLink - 1];
      if (indexCurrentRelLink + 1 < allRelLink.length)
        this.nextLink = allRelLink[indexCurrentRelLink + 1];
    },
    getDescription() {
      let description = null;
      this.fields.forEach((field) => {
        if (field.key === 'Краткое описание') {
          description = field.value;
        }
      });
      return description;
    },
    openNavLink(link) {
      if (!link) return;

      this.$emit("close");
      this.$nextTick(() => link.click());
    }
  }
};
</script>
<style lang="scss">
.hm-card-modal-course {
  font-family: Roboto, sans-serif;
  &__content {
    padding: 19px 36px;
  }
  &__title {
    font-style: normal;
    font-weight: 500;
    font-size: 20px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1F2041;
    width: 100%;
    box-sizing: border-box;
    padding-right: 60px;
    margin-bottom: 19px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  &__subject {
    display: flex;
    width: 100%;
    height: 200px;
    margin-bottom: 41px;
    border-radius: 4px;
    &::before {
      display: none;
    }
  }
  &__photo {
    position: static !important;
    width: 50% !important;
    max-width: 380px !important;
    height: 100%;
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-color: #D4E3FB;
    border-radius: 4px;
  }
  &__photo.default {
    width: 50% !important;
    max-width: 380px !important;
    background-size: 40%;
  }
  &__description {
    box-sizing: border-box;
    width: 50%;
    font-style: normal;
    font-weight: 300;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
    padding-left: 26px;
    padding-right: 16px;
    margin: 0 !important;
    margin-bottom: 10px !important;
    -webkit-line-clamp: 8;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  &__text {
    padding: 0 !important;
    width: 100%;
  }
  &__list {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    width: 100%;
    padding: 0 !important;
  }
  &__text-wrapper {
    display: flex;
    align-items: center;
  }
  &__text-info {
    padding: 0 !important;
    flex: 1 1 calc(50% - 26px) !important;
    max-width: 393px;
    margin-bottom: 27px;

    .v-list-item__content {
      padding: 0 !important;
    }

    &:nth-child(odd) {
      margin-right: 26px;
    }
  }
  &__text-icon {
    margin-right: 24px;
  }
  &__text-info__header {
    font-style: normal;
    font-weight: normal;
    font-size: 13px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #666666;
  }
  &__text-info__body-text {
    font-style: normal;
    font-weight: 500;
    font-size: 16px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
  }
  .hm-modal_close {
    top: 19px !important;
  }
  &__nav {
    display: flex;
    justify-content: space-between;
    margin-top: 34px;
    .hm-card-modal-course__nav-item {
      width: 145px;


      position: relative;
      border: 1px solid #2960A0;
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
      > button {
        margin: 0;
        padding: 0;
        width: 100%;
        // width: 145px;
        height: 32px;
        border-radius: 4px;
        > div {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          letter-spacing: 0.02em;
          color: #4A90E2;
          position: relative;
          > i {
            font-size: 22px !important;
            position: absolute;
          }
          .left {
            left: 16px;
          }
          .right {
            right: 16px;
          }
        }
      }
      > button:before {
        border-radius: 4px;
      }
    }
  }
}
.big-card {
  width: 858px !important;
  max-width: 858px !important;
}
.small-card {
  width: 452px !important;
  max-width: 452px !important;
  .hm-card-modal-course__photo {
    width: 100% !important;
  }
  .hm-card-modal-course__text-info {
    min-width: 100% !important;
    margin-right: 0;
  }
}
</style>
