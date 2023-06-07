<template>
  <div class="template_calendar">
    <div class="mod_calendar" :style="style">
      <div class="mod_calendar-title">
        <div class="mod_calendar-title__back" @click="eventActiveMonth('back')">
          <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M7 1.77637L1 7.26223L7 12.7481" stroke="#2C2C2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="mod_calendar-title__date" @click="activeModal = true">
          <span>{{ _(activeDateTitle) }}</span>
        </div>
        <div class="mod_calendar-title__next" @click="eventActiveMonth('next')">
          <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 12.748L7 7.26219L1 1.77632" stroke="#2C2C2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      <transition name="fade">
        <div class="mod_calendar-modal" v-if="activeModal">
          <calendar-modal
            :month-name="monthDeclensionsAbbreviated"
            :active-date="activeDate"
            :events="eventsByDateResult"
            @activeNewYears="activeNewYearsParent"
            @activeNewMonth="activeNewMonthparent"
          />
        </div>
      </transition>
      <div class="mod_calendar-week">
        <div class="mod_calendar-week__day" v-for="(day, key) in daysWeek" :key="key">
          <span>{{ day }}</span>
        </div>
      </div>
      <div class="mod_calendar-days">
        <div class="mod_calendar-days__day"
             v-for="el in daysImMonth + activeDayWeek "
             :key="el"
             @click="mActiveDayEvents(el - activeDayWeek)"
             :class="{
               'events-active': activeEventsDay(el - activeDayWeek),
               'current-date': styleCurrentDate() && el - activeDayWeek === currentDate[0] && !returnActiveDay(el - activeDayWeek),
               'current-day' : returnActiveDay(el - activeDayWeek)
             }">
          <span v-if="el > activeDayWeek">{{ el - activeDayWeek }}</span>
        </div>
      </div>
    </div>
    <div class="template_calendar-real">
      <div class="template_calendar-real__date">
        <span>{{ _(realDate) }}</span></div>
      <div class="template_calendar-real__separator"></div>
      <div class="template_calendar-real__action" @click="returnToCurrent">
        <span :title="_('вернуться на текущую дату')">{{ _('Сегодня') }}</span>
      </div>
  </div>
  </div>
</template>

<script>
import CalendarModal from "@/components/controls/hm-calendar/calendar-modal/calendarModal";
export default {
  name: "calendar",
  components: {CalendarModal},
  props: {
    // полный размер
    full: {
      type: Boolean,
      default: true
    },
    // события для календаря
    events: {
      type: [Array, Object],
      default: () => []
    }
  },
  data() {
    return{
      currentDate: [],  //массив текущей даты (день. месяц. год, день недели начала месяца)
      activeDate: [],   //массив выбранной даты (день, месяц, год, день недели начала месяца)
      month: ['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'],
      monthDeclensions: ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'],
      monthDeclensionsAbbreviated: ['янв.','февр.','март','апр.','май','июнь','июль','авг.','сент.','окт.','нояб.','дек.'],
      daysWeek: ['пн','вт','ср','чт','пт','сб','вс'],
      daysImMonth: null, // количество дней в активном месяце
      activeDayEvents: [], // выбранный день месяца (день, месяц, год)
      activeModal: false,
      eventsByDate: [], // события по датам (просто массив дат когда будут события)
    }
  },
  computed: {
    style() {
      return {
        width: this.full ? '100%' : '50%'
      }
    },
    // строково представление активной ( текущей) даты - год / месяц
    activeDateTitle() {
      return `${this.activeDate[2]} ${this.month[this.activeDate[1]]}`
    },
    // возврат с какого дня недели начинаеться месяц (форматирование пн - 0 вс- 6)
    activeDayWeek() {
      let daysInWeek = null;
      if(this.activeDate[3] === 0) daysInWeek = 6;
      if(this.activeDate[3] === 1) daysInWeek = 0;
      if(this.activeDate[3] === 2) daysInWeek = 1;
      if(this.activeDate[3] === 3) daysInWeek = 2;
      if(this.activeDate[3] === 4) daysInWeek = 3;
      if(this.activeDate[3] === 5) daysInWeek = 4;
      if(this.activeDate[3] === 6) daysInWeek = 5;
      return daysInWeek
    },
    // строка с текущим днем и месяцем
    realDate() {
      return `${this.currentDate[0] < 10 ? '0' + this.currentDate[0] : this.currentDate[0]} ${this.monthDeclensions[this.currentDate[1]]}`
    },
    // разибивание даты, по - [год, месяц,день] -> [день, месяц, год]
    eventsByDateResult() {
      let result = [];
      for(let ev in this.events) {
        result.push([...ev.split('-').map(el=>Number(el)).reverse()])
      }
      return result
    }
  },
  watch: {
    activeDayEvents(data) {
      this.$emit('activeDays', {
        date: data,
        dateString: `${this.activeDayEvents[0]} ${this.monthDeclensions[Number(this.activeDayEvents[1])]} ${this.activeDayEvents[2]}`
      })
    },
    // отслеживание, когда перехватить
    activeModal(data) {
      if(data) {
        document.addEventListener('click', this.closeModal, true)
      } else {
        document.removeEventListener('click', this.closeModal, true)
      }
    },
  },
  methods: {
    /**
    *  метод определния текущей даты
    */
    mCurrentDate() {
      this.currentDate  = [];
      this.activeDate   = [];
      let date          = new Date();
      this.currentDate.push(date.getDate());
      this.currentDate.push(date.getMonth());
      this.currentDate.push(date.getFullYear());
      this.currentDate.push(this.mDayWeek(this.currentDate[2], this.currentDate[1]));
      this.mDAysInMonth(this.currentDate[2],this.currentDate[1]);

      this.activeDate   = this.currentDate.slice(); // при инициализации и при возврату приравнимаю текущию к реальной
      this.mActiveDayEvents(this.activeDate[0]); // активная сег. дата
      this.$emit('initCalendarDate', {
        date: [
          this.currentDate[0] < 10 ? '0' + this.currentDate[0] : this.currentDate[0],
          this.currentDate[1] < 10 ? '0' + this.currentDate[1] : this.currentDate[1],
          this.currentDate[2]
        ],
        dateString: `${this.activeDayEvents[0]} ${this.monthDeclensions[Number(this.activeDayEvents[1])]} ${this.activeDayEvents[2]}`
      })
    },
    // метод определния дня недели начала месяца
    mDayWeek(year, month) {
      return new Date(year, month, 1 ).getDay(); // день недели (0 - вс)
    },
    // метод по определнеию количетсва дней в текущем месяце
    mDAysInMonth(year, month) {
      this.daysImMonth =  32 - new Date(year, month, 32).getDate();
    },
    /**
    *  метод перехода
    * @param ev - next/ back
    */
    eventActiveMonth(ev) {
      if(ev === 'next') {
        if(this.activeDate[1] === 11) {
          this.activeDate[1] = 0;
          this.activeDate[2]++;
        }
        else {
          this.activeDate[1]++
        }
      }
      else if(ev === 'back') {
        if(this.activeDate[1] === 0) {
          this.activeDate[1] = 11;
          this.activeDate[2]--;
        }
        else {
          this.activeDate[1]--
        }
      }

      // определение дня начала недели активной даты
      this.activeDate[3] = this.mDayWeek(this.activeDate[2], this.activeDate[1]);

      // пределение количества дней в месяце
      this.mDAysInMonth(this.activeDate[2],this.activeDate[1]);

      // обновление данных, через Vue set не отработало
      let newDate = this.activeDate.slice();
      this.activeDate = [];
      this.activeDate = newDate.slice();
    },
    // метод возврата неа текущие дату
    returnToCurrent() {
      this.activeDate = [];
      this.activeDate = this.currentDate.slice();
      this.mDAysInMonth(this.activeDate[2],this.activeDate[1]);
      this.mActiveDayEvents(this.activeDate[0])
    },
    /**
    * метод по определению событий
    * @param day - активный день
    * @return есть ли события
    */
    activeEventsDay(day) {
      return this.events[`${this.activeDate[2]}-${this.activeDate[1] < 10 ? '0'+this.activeDate[1] : this.activeDate[1]}-${day < 10 ? '0'+ day : day}`] &&
        this.events[`${this.activeDate[2]}-${this.activeDate[1] < 10 ? '0'+this.activeDate[1] : this.activeDate[1]}-${day < 10 ? '0'+ day : day}`].length > 0
        ? this.events[`${this.activeDate[2]}-${this.activeDate[1] < 10 ? '0'+this.activeDate[1] : this.activeDate[1]}-${day < 10 ? '0'+ day : day}`]
        : null
    },
    styleCurrentDate() {
      return JSON.stringify(this.currentDate) === JSON.stringify(this.activeDate)
    },
    /**
    * метод по выбору дня
    * @param day
    */
    mActiveDayEvents(day) {
      this.activeDayEvents = [];
      this.activeDayEvents.push(day < 10 ? '0' + day : day);
      this.activeDayEvents.push(this.activeDate[1] < 10 ? '0' + this.activeDate[1] : this.activeDate[1]);
      this.activeDayEvents.push(this.activeDate[2]);
    },
    // выбранный день пользователем
    returnActiveDay(day) {
      let newArr = [];
      newArr.push(day < 10 ? '0'+ day : day);
      newArr.push(this.activeDate[1] < 10 ? '0'+this.activeDate[1] : this.activeDate[1]);
      newArr.push(this.activeDate[2] < 10 ? '0'+this.activeDate[1] : this.activeDate[2]);
      return this.activeDayEvents.join('-') === newArr.join('-')
    },
    // метод закрытия модалки
    closeModal(e) {
      !e.target.classList.contains('modal-calendar') &&  this.activeModal ? this.activeModal = false : ''
    },
    // выбор года
    activeNewYearsParent(data) {
      this.activeDate[2] = data;
      this.eventActiveMonth();
    },
    activeNewMonthparent(data) {
      this.activeDate[1] = data;
      this.eventActiveMonth();
      this.activeModal = false;
    },
  },
  mounted() {
    this.mCurrentDate()
  }
}
</script>

<style lang="scss">
.template_calendar {
  width: 100%;
  height: 100%;

  &-real {
    width: 100%;
    height: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    > span {
      font-weight: 500;
      font-size: 1.5rem;
      line-height: 21px;
      letter-spacing: 0.02em;
    }
    &__date {
      > span {
        color: #1E1E1E;
      }
    }
    &__action {
      cursor: pointer;
      > span {
        color: #2960A0;
      }
    }
    &__separator {
      width: 1px;
      height: 70%;
      background: #000000;
    }
  }

  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to  {
    opacity: 0;
  }
}
  .mod_calendar {
    height: auto;
    padding: 14px 15px;
    background: #FFFFFF;
    border: 1px solid #CDDBF2;
    border-radius: 10.0441px;
    display: flex;
    flex-direction: column;
    position: relative;
    &-modal {
      position: absolute;
      top: -0px;
      left: -0px;
      width: 100%;
      height: 100%;
      background: white;
      display: -webkit-box;
      display: -ms-flexbox;
      display: flex;
      padding: 14px 15px;
      -webkit-box-shadow: 0 2px 7px rgba(0, 0, 0, 0.12);
      box-shadow: 0 2px 7px rgba(0, 0, 0, 0.12);
    }
    &-title {
      width: 100%;
      height: 61px;
      background: rgba(74, 144, 226, 0.1);
      border-radius: 5px;
      display: flex;
      align-items: center;
      position: relative;
      &__back,&__next {
        width: 20%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
      }
      &__date {
        width: 60%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        > span {
          font-weight: 500;
          font-size: 1.5rem;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #2C2C2C;
        }
      }
    }
    &-week {
      width: 100%;
      height: 51px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: 6px;
      &__day {
        width: calc(100% / 7);
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        > span {
          font-weight: normal;
          font-size: 1.5rem;
          line-height: 21px;
          color: #70889E;
        }
      }
    }
    &-days {
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      &__day {
        width: calc(100% / 7 - 2px);
        min-height: 47px;
        min-width: 26px;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 50%;
        cursor: pointer;
        &:not(:nth-child(7n + 7)) {
          margin-right: 2px;
        }

        > span {
          font-weight: normal;
          font-size: 1.5rem;
          line-height: 21px;
          text-align: center;
          letter-spacing: 0.02em;
          color: #2C2C2C;
        }
      }
    }

    .events-active {
      background: rgba(212, 250, 228, 0.6);
    }

    .current-date {
      border: 1px solid #2574CF;
      box-sizing: border-box;
    }

    .current-day {
      background: #4A90E2 !important;
      > span {
        color: #FFFFFF;
      }
    }

  }
</style>

