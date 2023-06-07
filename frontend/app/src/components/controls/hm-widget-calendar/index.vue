<template>
  <div class="hm-widget-calendar">
    <div class="hm-widget-calendar__block">
      <div class="hm-widget-calendar__block-cal">
        <calendar
          :events="objectiveEvents"
          @activeDays="activeDaysParent"
          @initCalendarDate="initCalendarDateParent"
        />
      </div>
      <div class="hm-widget-calendar__block-events">
        <calendar-list-events :data-events="eventsList" :date="strDate" />
      </div>
    </div>

  </div>

</template>

<script>
import Calendar from "@/components/controls/hm-widget-calendar/calendar/calendar";
import CalendarListEvents from "@/components/controls/hm-widget-calendar/calendar-list-events/calendar-list-events";
export default {
  components: {CalendarListEvents, Calendar},
  props: {
    dataCalendar: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      activeDate: [], // активная дата
      dateString: '',
      monthDeclensions: ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'],
      eventsInProps: null
    }
  },
  computed: {
    eventsList() {
      return this.eventsInProps[this.activeDate.reverse().join('-')]
    },
    strDate() {
      return this.dateString
    },
    objectiveEvents() {
      return this.eventsInProps
    }
  },
  methods: {
    activeDaysParent(data) {
      this.activeDate = data.date.slice();
      this.dateString = data.dateString;
    },
    initCalendarDateParent(data) {
      this.activeDate = data.date.slice();
      this.dateString = data.dateString;
    },
    // метод форматирования (события / месяц)
    formatDateMonthInEvents() {
      this.eventsInProps = {};
      for(let el in this.dataCalendar) {
        this.eventsInProps[`${el.split('-')[0]}-${Number(el.split('-')[1]-1) < 10 ? '0'+Number(el.split('-')[1]-1) : Number(el.split('-')[1]-1)}-${el.split('-')[2]}`] = this.dataCalendar[el]
      }
    }
  },
  beforeMount() {
    this.formatDateMonthInEvents();
  },
  mounted() {

  }
}
</script>

<style lang="scss">
 .hm-widget-calendar {
   width: 100%;
   &__block {
     width: 100%;
     display: flex;
     &-cal {
       width:227px;
     }
     &-events {
       margin-left: 26px;
       width: calc(100% - 253px);
     }
   }
 }
 @media(max-width: 768px) {
   .hm-widget-calendar {
     margin-bottom: 16px;
     &__block {
       flex-direction: column;
       align-items: center;
       &-cal {
         width: 100%;
         padding: 0 16px;
       }
       &-events {
        margin-left: 0;
        padding: 16px;
        width: 100%
       }
     }
   }
 }
 @media(max-width: 520px) {
   .hm-widget-calendar {
     &__block {
       &-cal {
        max-width: 440px;
        min-width: 260px;
       }
     }
   }
 }
</style>
