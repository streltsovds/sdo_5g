<template>
  <hm-modal
    :class-name="`hm-card-link__modal hm-card-modal` +' ' +styleCard"
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
    <v-card class="hm-card-modal__content">

      <div class="hm-card-modal__user" v-if="photo && userCard ">
        <div class="hm-card-modal__photo" :style="'background-image: url(' + photo + ');'" />
      </div>

      <div class="hm-card-modal__subject" v-if="subjectCard ">
        <div class="hm-card-modal__photo" v-if="photo" :style="'background-image: url(' + photo + ');'" />
        <div class="hm-card-modal__photo default" v-else :style="'background-image: url(' + defaultPhoto + ');'" />
      </div>

      <div class="hm-card-modal__full"
           v-if="photo && otherCard"
           :class="stylePhoto"
      >
        <div class="hm-card-modal__photo" :style="'background-image: url(' + photo + ');'" />
      </div>
      <v-card-title class="headline" v-if="title">
        <span>{{ title }}</span>
      </v-card-title>
      <v-card-text class="hm-card-modal__text">
        <div class="hm-card-modal__text-description" v-if="content">
          <hm-dependency :template="content" />
        </div>
        <!--        данные по карте после заголовка-->
        <v-layout v-if="fields && fields.length > 0" wrap>
          <v-flex>
            <v-list>
              <v-list-item
                class="hm-card-modal__text-info"
                v-for="(field, key) in fields"
                :key="key"
                v-if="field.value !== undefined && field.value !== null && field.value !== ''"
              >
                <v-list-item-content>
                  <!--              заголовок по каждому пунтку-->
                  <v-list-item-subtitle class="hm-card-modal__text-info__header">
                    {{ field.key }}
                  </v-list-item-subtitle>
                  <!--                  каждый пункт по карте -->
                  <v-list-item-title>
                    <span class="hm-card-modal__text-info__body-text" v-html="field.value" />
                  </v-list-item-title>
                </v-list-item-content>
              </v-list-item>
            </v-list>
          </v-flex>
        </v-layout>
        <div class="hm-card-modal__nav">
          <div class="hm-card-modal__nav-item hm-card-modal__nav-prev">
            <!--            <v-tooltip bottom>-->
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

            <!--            </v-tooltip>-->
          </div>
          <div class="hm-card-modal__nav-item hm-card-modal__nav-next">
            <!--            <v-tooltip bottom>-->
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
            <!--            </v-tooltip>-->
          </div>
        </div>
      </v-card-text>
    </v-card>
  </hm-modal>
</template>
<script>
import HmModal from "@/components/layout/hm-modal";
import HmDependency from "@/components/helpers/hm-dependency";
export default {
  components: { HmModal, HmDependency },
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
    };
  },
  computed: {
    styleCard() {
      return this.userCard ? 'half-card' : 'big-card'
    },
    stylePhoto() {
      return this.photo.indexOf('/images/icons/subject/exam.svg') !== -1 ? 'card-photo__nophoto' : 'card-photo'
    },
  },
  mounted() {
    //определение, если карточка пользователя
    this.userCard = this.typeCard.toLowerCase() === 'карточка' || this.typeCard.toLowerCase() === 'карточка пользователя' || this.typeCard.toLowerCase() === 'тьютор';
    this.subjectCard = this.typeCard.toLowerCase() === 'карточка учебного курса';
    this.otherCard = !this.userCard && !this.subjectCard;
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
    openNavLink(link) {
      if (!link) return;

      this.$emit("close");
      this.$nextTick(() => link.click());
    }
  }
};
</script>
<style lang="scss">
.big-card {
  width: 460px !important;
  max-width: 460px !important;
}
.half-card {
  width: 360px !important;
  max-width: 360px !important;
}

.hm-card-link {
  display: inline-block;
  i {
    margin-right: 0;
  }
}
.hm-card-modal:not(.v-dialog--fullscreen) {
  *p {
    margin-bottom: 0 !important;
  }
  .hm-card-modal__text {
    padding: 16px 26px 26px !important;
    > div{
      >div {
        > div{
          >div {
            padding: 0 !important;
          }
        }
      }
    }
    &-description {
      font-weight: 400;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: rgba(31, 32, 65, 0.75);
    }
    &-info {
      > div {
        > span {
          letter-spacing: 0.02em;
          color: #000000;
          font-size: 16px;
          line-height: 24px;
        }
      }
    }

    &-info:not(:last-child) {
      margin-bottom: 16px;
      > div {
        margin: 0;
        padding: 0;
        min-height: 24px;
      }
    }
    &-info:last-child {
      > div {
        margin: 0;
        padding: 0;
        min-height: 24px;
      }
    }
  }
  .hm-card-modal__nav {
    display: flex;
    justify-content: space-between;
    margin-top: 34px;
    .hm-card-modal__nav-item {
      width: calc(50% - 5px);


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
.hm-card-modal.v-dialog--fullscreen {
  .hm-card-modal__content {
    margin-top: 48px;
    min-height: calc(100vh - 48px);
    padding-left: 30px;
    padding-right: 30px;
  }
}
.hm-card-modal__content {
  position: relative;
}

.hm-card-modal__user {
  width: 100%;
  height: 220px;
  position: relative;
  display: flex;
  justify-content: center;

  &:before {
    content: "";
    width: 100%;
    height: 140px;
    background: #D4E3FB;
    border-radius: 4px 4px 0 0;
  }

  > .hm-card-modal__photo {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      position: absolute;
      top: 25%;
      background-size: cover;
      background-repeat: no-repeat;
  }
}

.hm-card-modal__subject {
  width: 100%;
  height: 220px;
  position: relative;
  display: flex;
  justify-content: center;

  &:before {
    content: "";
    width: 100%;
    height: 100%;
    background: #D4E3FB;
  }

  > .hm-card-modal__photo {
      width: 100%;
      height: 100%;
      position: absolute;
      background-size: cover;
      background-repeat: no-repeat;

    &.default {
      background-size: 40%;
      background-position: center;
    }
  }
}

.card-photo__nophoto {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 243px;
  width: 100%;
  background-color: #5181B8;
  > img {
    width: 78px;
    height: 78px;
  }
}

.card-photo {
  position: relative;
  height: 243px;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  > img {
    width: 100%;
    height: 100%;
  }
}

.headline {
  padding: 16px 26px 16px 26px;
  font-weight: 500;
  > span {
    font-size: 20px;
    line-height: 24px;
    letter-spacing: 0.02em;
    color: #1F2041;
  }

}

.hm-card-modal__nav-item {
  position: fixed;
  top: 50%;
  .v-btn {
    height: auto;
    width: auto;
  }
  i {
    /*font-size: 46px !important;*/
  }
}
.hm-card-modal__nav-prev {
  left: 0;
}
.hm-card-modal__nav-next {
  right: 0;
}
.hm-card-modal__text-info__header {
  font-size: 12px !important;
  line-height: 12px !important;
  color: #9c9c99 !important;
  opacity: 0.87 !important;
  margin-bottom: 5px !important;
}

.hm-card-modal__text-info__body-text {
  font-size: 16px;
  line-height: 24px;
  letter-spacing: 0.02em;
  color: #000000;
}
</style>
