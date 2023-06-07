<template>
  <v-card class="hm-calendar-wrapper">
    <div class="hm-calendar">
      <div class="hm-calendar__block">
        <div class="hm-calendar__block-cal">
          <calendar
            :events="objectiveEvents"
            @activeDays="activeDaysParent"
            @initCalendarDate="initCalendarDateParent"
          />
        </div>
        <div class="hm-calendar__block-events">
          <calendar-list-events :data-events="eventsList" :date="strDate" />
        </div>
      </div>

    </div>
  </v-card>
</template>

<script>
import Calendar from "@/components/controls/hm-calendar/calendar/calendar";
import CalendarListEvents from "@/components/controls/hm-calendar/calendar-list-events/calendar-list-events";
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
.hm-calendar-wrapper {
  min-height: 100%;
  padding: 26px;
}
 .hm-calendar {
   width: 100%;
   &__block {
     width: 100%;
     display: flex;
     &-cal {
       width:380px;
     }
     &-events {
       margin-left: 78px;
       width: calc(100% - 380px - 78px);
     }
   }
 }
</style>
