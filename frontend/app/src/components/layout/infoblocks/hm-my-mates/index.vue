<template>
  <v-flex v-if="items.length > 0" xs12 class="mates-block">

    <v-list class="mates-block__list">
      <div
        class="mates-block__list-item"
        v-for="(mate, index) in items"
        avatar
      >
        <v-flex class="mates-block__list-item-wrapper d-flex">
            <hm-card-link
              title="карточка пользователя"
              :url="mate.viewUrl"
              rel="pcard"
              float=" "
              @click.native.stop
              class="mate-avatar"
            >
                <img
                  v-if="mate.avatar"
                  :alt="mate.name"
                  :src="mate.avatar"
                />

                <div v-else class="mate-avatar--no-image">
                  {{ mate.user.FirstName[0] }}
                </div>
            </hm-card-link>


          <v-list-item-content>
            <v-list-item-title class="mates-block__name">
              {{ mate.user.FirstName + " " + mate.user.LastName }}
            </v-list-item-title>
            <div v-if="mate.isAdmin || mate.isDean || !mate.hasSameSubjects" class="mates-block__description">
              Роль:
              <span class="mates-block__role" :class="mate.isAdmin ? 'mates-block__role--admin' : ''">
                {{ mate.roleName }}
              </span>
            </div>
            <div v-else class="mates-block__description">
              Общие курсы: <span class="mates-block__subject">{{ subjectName(mate.sameSubjects) }}</span>
            </div>
          </v-list-item-content>
        </v-flex>
        <v-divider v-if="index < (items.length - 2)"/>
      </div>
    </v-list>
    <hm-load-more-btn  v-if="allowLoadMore" @click="showMore" :isLoading="isLoading"/>
  </v-flex>
  <v-flex v-else xs12>
    <p class="no-users text-xs-center">{{ _("Нет пользователей online") }}</p>
  </v-flex>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import hmCardLink from "@/components/els/hm-card-link/index";
import HmLoadMoreBtn from "@/components/helpers/hm-load-more-btn";

export default {
  components: { SvgIcon, hmCardLink },
  props: {
    onlineMates: {
      type: Array,
      default: () => []
    },
    url: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      items: [],
      pageCount: null,
      pageCurrent: 1,
      isLoading: false
    };
  },
  computed: {
    allowLoadMore() {
      return this.pageCount === null || this.pageCount >= this.pageCurrent + 1;
    },
    btnLoadingClasses(){
      let classes = ['hm-btn--loading'];
      if(isLoading){
        classes.push('hm-btn--loading-process');
      }
      return classes.join(' ')
    }
  },
  mounted() {
    this.items = Object.values(this.onlineMates);
  },
  created() {

  },
  methods: {
    subjectName(subjects) {
      let subjectName = 0,
          subjectsValues = Object.values(subjects);

      if(subjectsValues.length > 1) {
        subjectName = subjectsValues.length;
      } else if(1 === subjectsValues.length) {
        subjectName = subjectsValues[0];
      }
      return subjectName;
    },
    showMore() {
      if(!this.allowLoadMore) return;

      this.$axios
        .get(this.url, {params: { page: (this.pageCurrent + 1) }})
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.items)
            throw new Error("Ошибка при загрузке учебных курсов");
          return r.data;
        })
        .then(data => {
          if(data.pageCount >= this.pageCurrent + 1) {
            this.items = [...this.items, ...Object.values(data.items)];
          }

          this.pageCount = data.pageCount;
          this.pageCurrent = data.pageCurrent;

          console.log(this.items);
        })
        .catch(e => console.error(e))
        .finally(() => (this.isLoading = false));
    },
  }
};
</script>

<style lang="scss" scoped>
.mates-block {
  &__name {
    font-weight: 500;
    font-size: 14px;
  }
  &__description {
    font-size: 12px;
  }
  &__role {
    color: #2960a0;
    &--admin {
      color: #E31F28;
    }
  }
  &__subject {
    color: #979797;
  }
  &__list-item {
    width: 45%;
  }
  &__list {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    min-height: 100px;
    max-height: 350px;
    overflow-y: auto;
    margin-bottom: 35px;
    .mate-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      background: #FFFFFF;
      margin-right: 24px;
      margin-top: 8px;
      margin-bottom: 8px;
      > a {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
        > img {
          height: 46px;
        }
      }
      &--no-image {
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #2196f3;
        text-align: center;
        vertical-align: middle;
        line-height: 40px;
      }
    }
  }
}
@media(max-width: 768px) {
  .mates-block {
    padding: 0 16px;
    padding-bottom: 16px;
    &__list {
      padding: 0;
      margin: 0;
      min-height: auto;
      & .mate-avatar {
        width: 34px;
        height: 34px;
        margin: 0;
        margin-right: 11px;
        & a img {
          width: 34px;
          height: 34px;
        }
      }
      &-item {
        width: 100%;
        border-bottom: 2px solid rgba(0, 0, 0, 0.15);
        &:last-of-type {
          border-bottom: 0;
        }
        &-wrapper {
          align-items: center;
        }
      }
    }
  }
  .no-users {
    padding: 0 16px;
  }
}
</style>
