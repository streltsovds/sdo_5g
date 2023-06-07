<template>
  <div class="hm-single-choice">
    <ul class="hm-single-choice__variants">
      <li class="hm-single-choice__variant" v-for="(variant, index) in variants" :key="index">
        <hm-checkbox v-if="checkboxNeeded" :name="`variants__is_correct__${variant.question_variant_id}`"
                     :attribs="{
                       label:'Правильный ответ',
                       hideDetails: true,
                     }"
                     @change="(val) => clearCheckboxes(val, index)"
                     :checked="!!(variant.is_correct) || false"
        />
        <div class="hm-single-choice__text-row">
          <div class="hm-single-choice__text">
            <hm-text :name="`variants__variant__${variant.question_variant_id}`"
                     :attribs="{
                       label:'Текст варианта'
                     }"
                     :value="variant.shorttext || variant.variant || ''"
            />
          </div>
          <div class="hm-single-choice__btn">
            <v-tooltip bottom>
              <template #activator="{ on }">
                <v-btn
                  slot="activator"
                  v-on="on"
                  @click="removeVariant(index)"
                  text
                  icon
                  color="error"
                >
                  <v-icon dark>
                    clear
                  </v-icon>
                </v-btn>
              </template>
              <span>Удалить</span>
            </v-tooltip>
          </div>
        </div>
      </li>
    </ul>
    <div class="hm-single-choice__actions">
      <v-tooltip bottom>
        <template #activator="{ on }">
          <v-btn slot="activator"
                 v-on="on"
                 @click="createNewVariant"
                 fab
                 dark
                 small
                 color="primary"
          >
            <v-icon dark>
              add
            </v-icon>
          </v-btn>
        </template>
        <span>Добавить</span>
      </v-tooltip>
    </div>
  </div>
</template>
<script>
import HmCheckbox from '@/components/forms/hm-checkbox';
import HmText from '@/components/forms/hm-text';

export default {
  name: "HmSingleChoice",
  components: { HmCheckbox, HmText },
  props: {
    value: {
      type: String,
      default: '{}'
    },
    params:{
      type: Object,
      default: {}
    }
  },
  data(){
    return {
      variants: JSON.parse(this.value) || {},
      newCounter: 0,
    }
  },
  computed: {
    checkboxNeeded() {
      /** @see HM_Form_QuestionStep2 method _addDefaultSingleChoice() */
      return this.params.type === 'test';
    }
  },
  methods:{
    createNewVariant(){
      let newVars = {...this.variants};
      newVars[`new_${this.newCounter}`] = {
        question_variant_id:'new[]'
      };
      this.variants = newVars;
      this.newCounter++;
    },
      clearCheckboxes(val, id){
      let newVars = { ...this.variants};
      if(val){
        Object.keys(newVars).forEach(key => {
          newVars[key].is_correct = (id === key);
        })
      }
      this.variants = newVars;
    },
    removeVariant(id){
      let newVars = {...this.variants};
      delete newVars[id];
      this.variants = newVars;
    }
  }
};

</script>
<style lang="scss">
.hm-single-choice{
    &__variants{
      list-style-type: none;
    }
    &__text-row{
        display: flex;
        align-items: center;
        width: 50%;
    }
    &__text{
        flex: 10;
    }
    &__btn{
        flex: 1;
    }
}
</style>