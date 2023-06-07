<template>
  <div class="calendar-modal modal-calendar">
    <div class="calendar-modal__title modal-calendar">
      <div class="calendar-modal__title__back modal-calendar" @click="eventActiveMonth('back')">
        <svg class="modal-calendar" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path class="modal-calendar" d="M7 1.77637L1 7.26223L7 12.7481" stroke="#2C2C2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="calendar-modal__title__month modal-calendar" @click="modalYear = true">
        <span class="modal-calendar">{{ yearActive }}</span>
      </div>
      <div class="calendar-modal__title__next modal-calendar" @click="eventActiveMonth('next')">
        <svg class="modal-calendar" width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path class="modal-calendar" d="M1 12.748L7 7.26219L1 1.77632" stroke="#2C2C2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>
    <div class="calendar-modal__contains modal-calendar" >
      <div class="calendar-modal__contains-el modal-calendar"
           v-for="(el, key ) in monthName"
           :key="key"
           @click="activeMonth = key"
           :class="{'active-month': activeMonth === key, 'real-month': activeDateModalMonth === key, 'events-in-month': dateComparison(key)}">
        <span class="modal-calendar">{{ _(el) }} </span>
      </div>
    </div>
    <transition name="fade">
      <div class="calendar-modal__years hm__scrollbar modal-calendar"
           v-show="modalYear"
           ref="calendarModalyear"
           @scroll="scrollActiveBlock"
           @mouseover="mouseInYears = true"
           @mouseleave="mouseInYears = false"
           @click="test">
        <div class="calendar-modal__years-year modal-calendar"
             v-for="(ye, key) in year()"
             :key="key"
             :data-year="ye"
             :class="{'active-scroll-year': activeYearEl && activeYearEl === key, 'events-year': eventsYear(ye) }"
        >{{ ye }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: "calendarModal",
  props: {
    monthName: {
      type: Array,
      default: () => []
    },
    activeDate:{
      type: Array,
      default: () => []
    },
    events: {
      type: Array,
      default: ()=> []
    }
  },
  data() {
    return {
      activeMonth: null,
      activeDateModalYear: this.activeDate[2],
      activeDateModalMonth: this.activeDate[1],
      modalYear: false,
      mouseInYears: false,
      elTop: 3,
      numberYear: 20, // количество лет для отображения
      activeYearEl: null   //активный (центральный элемент )
    }
  },
  computed: {
    yearActive() {
      return this.activeDateModalYear
    },
  },
  watch: {
    activeDateModalYear(data) {
      this.$emit('activeNewYears', data);
    },
    activeMonth(data) {
      setTimeout(() => {
        this.$emit('activeNewMonth', data)
      }, 150)
    },
    mouseInYears(data) {
      if(data) {
        console.log(data)
      }
    },
    modalYear(data){
      if(data) {
        setTimeout(() => {
          let year = this.$refs.calendarModalyear;
          //:TODO можно оптимизировать , this.elTop - 3 это кол-во блоков сверху для пропуска.
          //:TODO т.е.   year.scrollHeight / 2 середина контента, year.scrollHeight / 200 * 3 - кол-во блоков для отступа ( т.к. влезает 7 лет, на центр вывести - нужно отступ из 3х блоков)
          year.scrollTop = year.scrollHeight / 2 - year.scrollHeight / this.numberYear * this.elTop;
        }, 100)
      }
    }
  },

  methods: {
    // при скроле, определение верхней точки и расчет акитвного (* центрального) блока  сохраняет актиный элемент
    scrollActiveBlock(e) {
      this.activeYearEl = Math.ceil((e.target.scrollTop  + (this.$refs.calendarModalyear.scrollHeight / this.numberYear * this.elTop)) / (this.$refs.calendarModalyear.scrollHeight / this.numberYear ))

    },
    eventActiveMonth(ev) {
      if(ev === 'next') {
        this.activeDateModalYear++
      }
      else if(ev === 'back') {
        this.activeDateModalYear--
      }
    },
    /**
    *  метод по проверке дат
    * @param key   - элемента перебора
    */
    dateComparison(key) {
      let status = false;
      for(let i = 0,el = this.events; i < el.length; i++) {
        if(el[i][2] === Number(this.activeDate[2]) ) {
          if(status) break;
          status = el[i][1] === key
        }
      }
      return status;
    },
    year(years = this.numberYear) {
      let test = [];
      let activeDate = Number(this.activeDate[2]);
      for(let i = 0; i < years; i++) {
        test.push(activeDate - years / 2 + i)
      }
      return test
    },
    test(e) {
      if(e.target.dataset.year) {
        this.activeDateModalYear = e.target.dataset.year;
        this.modalYear = !this.modalYear
      }
    },
    // поиск событий по годам
    eventsYear(key) {
      let status = false;
      for(let i=0, ev = this.events; i < ev.length; i++) {
        if(Number(key) === Number(ev[i][2])) {
          status = true;
          break;
        }
      }
      return status
    }
  },
}
</script>

<style lang="scss">
.calendar-modal {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  &__title {
    width: 100%;
    height: 31px;
    display: flex;
    align-items: center;
    background: #eaf4fd;
    border-radius: 4px;
    &__month {
      width: 60%;
      justify-content: center;
    }
    &__back,&__next {
      width: 20%;
      height: 100%;
      justify-content: center;
    }
    > div {
      display: flex;
      align-items: center;
      cursor: pointer;
      > span {
        font-weight: 500;
        font-size: .9rem;
        line-height: 1.2rem;
        letter-spacing: 0.02em;
        color: #2C2C2C;
      }
    }
  }
  &__contains {
    width: 90%;
    height: calc(90% - 30px);
    display: flex;
    flex-wrap: wrap;
    margin: auto;
    &-el {
      width: calc(100% / 3);
      height: calc(100% / 4);
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      border-radius: 4px;
      > span {
        font-weight: 300;
        font-size: 1rem;
        line-height: 1.2rem;
        letter-spacing: 0.02em;
        color: #2C2C2C;
        padding: .3rem;
      }

      &.events-in-month {
        position: relative;
        &:after {
          content: '';
          position: absolute;
          bottom: 4px;
          left: calc(50% - 3px / 2);
          width: 4px;
          height: 4px;
          border-radius: 50%;
          background: #05C985;
          box-sizing: border-box;
        }
      }

    }
  }
  &__years {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 100;
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    &-year {
      padding: 3px 0;
      cursor: pointer;
      transition: .1s linear;
      opacity: .9;
      transform: scale(.9);
    }
  }

  .active-month {
    border: 1px solid #2574CF !important;
  }
  .real-month {
    background: #4A90E2 !important;
    > span {
      color: #FFFFFF;
    }
  }


  .active-scroll-year {
    transform: scale(1.2);
    color: #4A90E2;
    position: relative;
    & ~ div {
      opacity: .8;
      transform: scale(.95);
    }
    &:after {
      content: '';
      position: absolute;
      bottom: 4px;
      left: 0;
      height: 1px;
      width: 100%;
      background: #4A90E2;
    }

  }

  // класс для отображения событий по годам
  /*.events-year {*/
  /*  display: flex;*/
  /*  justify-content: center;*/
  /*  align-items: center;*/
  /*  &:before {*/
  /*    content: '';*/
  /*    position: absolute;*/
  /*    top: 1px;*/
  /*    height: 4px;*/
  /*    width: 4px;*/
  /*    border-radius:50%;*/
  /*    background: #05C985;*/
  /*  }*/
  /*}*/

  // СТИЛИ ДЛЯ АНИМАЦИИ активного года и переферических
  .active-year {
    transform: scale(1.1);
  }


  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active до версии 2.1.8 */ {
    opacity: 0;
  }
}
</style>
