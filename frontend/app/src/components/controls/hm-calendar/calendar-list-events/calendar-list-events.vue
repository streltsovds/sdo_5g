<template>
  <div class="calendar-list-events">
    <div class="calendar-list-events__title">
      <span>{{ _('Дата') }}: {{ date }} </span>
    </div>
    <div class="calendar-list-events__ev hm__scrollbar" :style="{marginTop: dataEvents.length === 0 ? '1rem' : ''}">
      <div class="calendar-list-events__ev-none" v-if="dataEvents.length === 0">
        <span>{{ _('На данную дату нет мероприятий') }}</span>
      </div>
      <a
        v-else
        :href="el.view_url"
        class="calendar-list-events__ev-list"
        v-for="(el, key) in dataEvents"
        :key="key">
        <div class="calendar-list-events__ev-list__icon">
          <svg-type-lesson :name="(typeIcon(el))" width="32" height="32"/>
        </div>
        <div class="calendar-list-events__ev-list__title">
          <span>{{ el.name }}</span>
          <span>{{ el.description }}</span>
        </div>
      </a>
    </div>
  </div>
</template>

<script>

import SvgTypeLesson from "@/components/icons/lessonType/svgTypeLesson";
export default {
  name: "calendarListEvents",
    components: {SvgTypeLesson},
    props: {
    dataEvents: {
      type: Array,
      default: () => []
    },
    date: {
      type: String,
      default: ''
    }
  },
  methods: {
    typeIcon(data) {
      if(data.type === 'subject') return data.type;
      else if(data.type === 'lesson') {
        if(data.subtype === 'resource' || data.subtype === 'task' || data.subtype === 'test' || data.subtype === 'eclass') {
          return data.subtype
        }
        return 'rest'
      }
      return 'rest'
    }
  }
}
</script>

<style lang="scss">
.calendar-list-events {
  width: 100%;
  min-height: 338px;
  display: flex;
  flex-direction: column;
  &__title {
    width: 100%;
    height: 16px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    > span {
      font-weight: 500;
      font-size: 0.9rem;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #2960A0;
    }
  }
  &__ev {
    width: 100%;
    height: 100%;
    max-height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: flex-start;
    &-none {
      width: 100%;
      > span {
        font-weight: normal;
        font-size: .9rem;
        line-height: 21px;
        letter-spacing: 0.02em;
        color: #3E4E6C;
      }
    }
    &-list {
      width: 100%;
      display: flex;
      justify-content: flex-start;
      align-items: flex-start;
      border-bottom: 1px solid rgba(0, 0, 0, 0.15);
      padding: 14px 0;
      text-decoration: none;

      &__icon {
        width: 48px;
        height: 48px;
        margin-right: 25px;

        & svg {
          width: 100%;
          height: 100%;
        }
      }
      &__title {
        width: calc(100% - 48px);
        display: flex;
        flex-direction: column;
        > span {
          font-weight: normal;
          font-size: .9rem;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #3E4E6C;
        }
      }
    }
  }
}
</style>
