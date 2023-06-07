<template>
  <div class="INFOBLOCK infoblock-rating-block">
    <v-list v-if="items.length > 0">
      <v-list-item
        v-for="item in items_"
        :key="item.resource_id"
        class="infoblock-rating-block_item"
      >
        <v-list-item-content class="infoblock-rating-block_title">
          <v-list-item-title><a :href="item.url" >{{ item.title }}</a></v-list-item-title>
        </v-list-item-content>
        <v-list-item-action class="infoblock-rating-block_values">
          <v-list-item-action-text style="font-weight: 500;color: #666666;">{{ item.average }}</v-list-item-action-text>
          <hm-rating @change="onRatingChange" :rating="parseInt(item.average)" :resource="item"></hm-rating>
          <v-list-item-action-text style="width: 80px; text-align: end;">{{
            getVotesText(item.count)
          }}</v-list-item-action-text>
        </v-list-item-action>
      </v-list-item>
    </v-list>
    <hm-empty v-else>Нет данных для отображения</hm-empty>
  </div>
</template>
<script>
import { decline } from "../../../../utilities";
import HmRating from "@/components/els/hm-rating/index";
import HmEmpty from "@/components/helpers/hm-empty";
export default {
  components: { HmRating, HmEmpty },
  props: {
    items: {
      type: Array,
      default: () => []
    },
    votesLabel: {
      type: Array,
      default: () => ["голос", "голоса", "голосов"]
    }
  },
  data: () => ({
    items_: [],
  }),
  mounted(){
    this.items_ = this.items;
  },
  methods: {
    getVotesText(voteCount) {
      return `${voteCount} ${decline(voteCount, this.votesLabel)}`;
    },
    onRatingChange({count, average, id}){

      let newItems = [...this.items_];
      let index = newItems.findIndex(item => item.resource_id === id);

      newItems[index] = {
        ...newItems[index],
        count,
        average
      }

      this.items_ = newItems;
    }
  }
};
</script>
<style lang="scss">
.infoblock-rating-block_values {
  flex-direction: row;
  align-items: center;
  .yellow--text.text--lighten-1 {
    color: #FFE99D!important;
    caret-color: #FFE99D!important;
  }
}
.infoblock-rating-block_title {
  flex-shrink: 0;
}
.infoblock-rating-block_item {
  .v-list__tile {
    flex-wrap: wrap;
  }
}

@media screen and (max-width: 768px) {
  .infoblock-rating-block {
    padding: 0 16px;
    padding-bottom: 16px;
    & .v-list {
      padding: 0 !important;
    }
    & .infoblock-rating-block_item {
      padding: 0;
      flex-direction: column;
      align-items: flex-start;
      min-height: auto !important;
      margin-bottom: 26px;
      &:last-child {
        margin-bottom: 0;
      }
    }
    & .infoblock-rating-block_title {
      align-self: flex-start;
      padding: 0;
      padding-bottom: 8px;
      & a {
        font-size: 0.875rem;
        line-height: 1.25rem;
      }
    }
    & .infoblock-rating-block_values {
      margin: 0;
      & .v-rating .v-icon {
        padding: 0 3px;
      }
    }
    & .v-rating {
      margin-right: auto;
      margin-left: 12px;
    }
  }
}
</style>
