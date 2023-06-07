<template>
  <v-rating
    v-if="value !== null"
    v-model="value"
    :readonly="readonly_"
    :resource="resource"
    @input="onChange"
    half-increments="true"
    empty-icon="mdi-star-outline"
    half-icon="mdi-star-half"
    full-icon="mdi-star"
    color="yellow lighten-1"
    background-color="grey lighten-1"
    medium
    hover
  />
</template>
<script>
import axios from 'axios';

export default {
  props: {
    rating: {
      type: Number,
      default: null
    },
    count: {
      type: Number,
      default: null
    },
    resource: {
      type: [Object],
      default: () => {}
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      value: this.rating,
      readonly_: this.readonly
    };
  },
  methods:{
    onChange(val){
      this.sendRating(val); 
    },
    sendRating(newRating){

      const fd = new FormData();
      fd.append('val', +newRating);
      fd.append('votes', +this.count);
      fd.append('score', +this.value);

      axios.post(`/kbase/assessment/index/resource_id/${this.resource.resource_id}/type/${this.resource.type}/field_name/average`, fd).then(res => {
        this.readonly_ = true;
        if (res.data.status == 'OK') {
          this.$emit('change', {
            count: res.data.msg.count,
            average: res.data.msg.average,
            id: this.resource.resource_id
          });
        } else if (res.data.status == 'ERR') {
          alert(res.data.msg);
        }

      }).catch(err => {
        this.value = this.rating;
      })
    }
  }
};
</script>
