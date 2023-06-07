<template>
  <div class="INFOBLOCK infoblock-my-events-block">
    <hm-date-picker
      name="infoblock-my-events-block_date"
      :label="datePickerLabel"
      :loading="isLoading"
      :disabled="isLoading"
      :value="value"
      @input="date = $event"
    >
    </hm-date-picker>
    <template v-if="!isLoading">
      <v-alert
        :value="!Object.keys(sessionEvents).length"
        type="info"
        outlined
        color="primary"
        >{{ emptyResponse }}</v-alert
      >
      <table
        v-if="Object.keys(sessionEvents).length > 0"
        class="infoblock-my-events-block_table"
      >
        <thead>
          <tr>
            <th v-for="field in fields" :key="field.column">
              {{ field.title }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="event in sessionEvents"
            :key="event.session_event_id"
            :class="event.tr_class"
          >
            <td
              v-for="field in fields"
              :key="`${field.column}_${event.session_event_id}`"
            >
              <template v-if="event.hasOwnProperty(field.column)">
                <hm-dependency
                  v-if="field.html"
                  :template="event[field.column]"
                ></hm-dependency>
                <span v-else>{{ event[field.column] }}</span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </template>
  </div>
</template>
<script>
import hmDatePicker from "../../../forms/hm-date-picker/index";
import hmDependency from "../../../helpers/hm-dependency/index";

export default {
  components: { hmDatePicker, hmDependency },
  props: {
    datePickerLabel: {
      type: String,
      default: null
    },
    url: {
      type: String,
      default: null
    },
    emptyResponse: {
      type: String,
      default: "Отсутствуют данные для отображения"
    },
    fields: {
      type: Array,
      default: () => []
    },
    value: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      date: this.value,
      isLoading: false,
      sessionEvents: {}
    };
  },
  computed: {
    requestUrl() {
      return `${this.url}${this.formatDate}`;
    },
    formatDate() {
      if (!this.date) return null;

      const [day, month, year] = this.date.split(".");
      return `${day}-${month}-${year}`;
    }
  },
  watch: {
    formatDate() {
      this.loadData();
    }
  },
  created() {
    this.loadData();
  },
  methods: {
    loadData() {
      if (this.isLoading) return;
      this.isLoading = true;

      this.$axios
        .get(this.requestUrl)
        .then(r => {
          if (r.status !== 200 || !r.data || !r.data.sessionEvents)
            throw new Error();
          console.log(r);
          this.sessionEvents = r.data.sessionEvents;
        })
        .catch(e => console.error(e))
        .finally(() => (this.isLoading = false));
    }
  }
};
</script>
<style lang="scss">
.infoblock-my-events-block_table {
  .red-tr {
    background-color: #fddddd;
  }
}
</style>
