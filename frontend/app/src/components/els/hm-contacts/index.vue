<template>
  <div id="contacts" class="contacts">
    <div v-if="getDataStoreContacts.teacher.length + getDataStoreContacts.student.length + getDataStoreContacts.graduated.length">
      <div class="contacts-body" ref="concatsBody" v-resize="onResize">
        <!--hm-hint :text="_('Здесь отображаются все участники данного курса, включая прошедших обучение; можно выделить нужных пользователей и отправить им сообщение.')"/-->
        <div
          class="contacts-body__teacher"
          v-if="getDataStoreContacts.teacher && getDataStoreContacts.teacher.length > 0"
        >
          <hm-contacts-card
            v-for="(item, i ) in getDataStoreContacts.teacher"
            :key="`teacher-${i}`"
            :data-card="item"
            :role="_('тьютор')"
            :enablePersonalInfo="enablePersonalInfo"
            :disableMessages="disableMessages"
          />
        </div>
        <div
          class="contacts-body__student"
          v-if="getDataStoreContacts.student && getDataStoreContacts.student.length > 0"
        >
          <hm-contacts-card
            v-for="(item, i ) in getDataStoreContacts.student"
            :key="`student-${i}`"
            :data-card="item"
            :role="_(' ')"
            :enablePersonalInfo="enablePersonalInfo"
            :disableMessages="disableMessages"
          />
        </div>
        <div
          class="contacts-body__graduated"
          v-if="getDataStoreContacts.graduated && getDataStoreContacts.graduated.length > 0"
        >
          <hm-contacts-card
            v-for="(item, i ) in getDataStoreContacts.graduated"
            :key="`graduated-${i}`"
            :data-card="item"
            :role="_('завершен')"
            :enablePersonalInfo="enablePersonalInfo"
            :disableMessages="disableMessages"
          />
        </div>
      </div>
      <hm-load-more-btn v-if="getDataStoreContacts.pageCount > getActivePage" :in-progress="isLoading" @click="showMore" />
    </div>
    <hm-empty v-else empty-type="full" />
  </div>
</template>

<script>
import HmHint from "@/components/helpers/hm-hint/index"
import HmEmpty from "@/components/helpers/hm-empty"
import HmContactsCard from "@/components/els/hm-contacts/hm-contacts-card/hmContactsCard";
import Axios from 'axios'
import SvgIcon from "@/components/icons/svgIcon";
import HmLoadMoreBtn from "@/components/helpers/hm-load-more-btn";

export default {
  components: {SvgIcon, HmContactsCard, HmHint, HmEmpty, HmLoadMoreBtn},
  props: {
    subjectId: {type:[Number,String], default: null},
    enablePersonalInfo: {type:[Boolean], default: true},
    disableMessages: {type:[Boolean], default: false},
  },
  data(){
    return{
      dataUser: [], //массив добавленных пользователей
      pageCurrent: 1,
      isLoading: false,
      pageCount: null,
      search: '', // поле для поиска ( нужно, для коректного поиска )
      widthBlock: 0, // размер родительского блока для карточек
      numberBlocksLine: null //количество блоков в каждой линии
    }
  },
  computed: {
    getDataStoreContacts() {
      return this.$store.getters['dataContacts/allData']
    },
    //гетер активной страницы
    getActivePage() {
      return this.$store.getters['dataContacts/activePage']
    },
    btnLoadingClasses(){
      let classes = ['hm-btn--loading','contacts-body__btn-loading'];

      if(this.isLoading) {
        classes.push('hm-btn--loading-process');
      }

      return classes.join(' ');;
    }
  },
  watch: {
    widthBlock(data) {
      let marginRight = 60;
      let widthBlock = 348;
      let sizeBlock = Math.floor(data / (marginRight + widthBlock));
      let balance = data - (sizeBlock * (marginRight + widthBlock))
      console.log(`размер 1 блока с учетом отступов - ${marginRight + widthBlock}`)
      console.log(`остаток - ${balance}`)
      console.log(data)
    }
  },
  mounted() {
    this.initComp()
    // console.dir(this.$refs.concatsBody)
  },
  methods: {
    // метод инициализации компонента
    initComp() {
      this.search = this.$store.getters['dataContacts/searchString'];
      this.page = this.$store.getters['dataContacts/activePage'];
    },
    // метод показывает следующие страницы :TODO БОЛЬШЕ ДАННЫХ, ПОКА ЧТО НЕ ПРОЕРИТЬ
    showMore() {
      this.isLoading = true;
      setTimeout(()=> {
        this.isLoading = false;
      }, 200)
      this.$store.dispatch('dataContacts/uersLoading', {subject_id:this.subjectId} )
    },
    // метод определения размера
    onResize() {
      this.widthBlock =   this.$refs.concatsBody.offsetWidth
    }
  }
}
</script>

<style lang="scss">
#contacts {
  width: 100%;
  height: auto;
  min-height: 695px;
  background: #FFFFFF;
  box-shadow: 0 10px 30px rgba(209, 213, 223, 0.5);
  border-radius: 4px;
  margin-bottom: 25px;
  padding: 34px 26px 27px 26px;
  position: relative;
  .contacts-title {
    width: 100%;
    height: auto;
    > span {
      font-weight: 500;
      font-size: 20px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
  .contacts-body {
    width: 100%;
    height: auto;
    &__student,
    &__graduated {
      margin: 26px 0 33px 0;
      display: flex;
      flex-wrap: wrap;
    }
    &__teacher {
      display: flex;
      flex-wrap: wrap;
      margin: 0 0 33px 0;
      &:after {
        content: ' ';
        width: 100vw;
        height: 1px;
        background: rgba(112, 136, 158, 0.31);
      }
    }
  }

  .contacts-loading {
    width: 100%;
    height: 36px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    bottom: 26px;
    > div {
      width: 220px;
      height: 100%;
      border: 1px solid #1F8EFA;
      border-radius: 4px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      > svg {
        margin-right: 8px;
      }
      > span {
        font-style: normal;
        font-weight: 500;
        font-size: 14px;
        line-height: 24px;
        letter-spacing: 0.16px;
        color: #1E1E1E;
      }
    }
  }
}
@media(max-width: 960px) {
  #contacts {
    & .contacts-body__teacher {
      &:after {
        margin-top: 26px;
      }
    }
  }
}
@media(max-width: 768px) {
  #contacts {
    padding: 16px;
    padding-bottom: 26px;
    & .contacts-body__teacher {
      margin-bottom: 26px;
    }
  }
}
@media(max-width: 440px) {
  #contacts {
    width: calc(100% + 32px);
    margin: 0 -16px;
  }
}
</style>
