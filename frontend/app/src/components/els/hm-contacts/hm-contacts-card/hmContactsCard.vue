<template>
  <div id="hmContactsCard" :class="{'user-card-online': dataCard.online,'user-card-ofline': !dataCard.online,}">
    <div class="contacts-card-image">
      <div class="contacts-card-image__img">
        <img v-if="dataCard.photo && dataCard.photo === ''" :src="dataCard.photo" alt="фотография">
        <svg-icon width="36" height="36" color="#4A90E2" v-else  name="user-round"/>
      </div>
      <div class="contacts-card-image__role">
        <span>{{ role }}</span>
      </div>
      <div class="contacts-card-image__online">
        <span :style="{color: dataCard.online ? ' #05C985' : '#70889E' }">{{ dataCard.online ? 'online' : '' }}</span>
      </div>
    </div>
    <div class="contacts-card-info">
        <div class="contacts-card-info__FIO"><span>{{ dataCard.name }}</span></div>
        <div
          v-if="enablePersonalInfo && dataCard.phone && dataCard.phone !== ''"
          class="contacts-card-info__phone">
          <svg-icon  width="16" height="16" color="#4A90E2" name="Phone"/>
          <a :href="`tel:${dataCard.phone}`"><span>{{ dataCard.phone }}</span></a>
        </div>
        <div
          v-if="enablePersonalInfo && dataCard.email && dataCard.email !== ''"
          class="contacts-card-info__mail">
          <svg-icon  width="16" height="16" color="#4A90E2"  name="Mail"/>
          <a :href="`mailto:${dataCard.email}`"><span>{{ dataCard.email }}</span></a>
        </div>
        <div
          v-if="dataCard.org_position && dataCard.org_position !== ''"
          class="contacts-card-info__position">
          <svg-icon width="16" height="16" color="#4A90E2" name="Portfolio"/>
          <span>{{ dataCard.org_position }}</span>
        </div>
    {{ checkedStore }}
    </div>
    <div class="contacts-card-checked">
      <div
        v-if="!disableMessages"
        @click="adduserChecked"
        :style="styleChecked"
        class="contacts-card-checked-form">
        <svg-icon
          v-if="checked"
          width="14"
          height="14"
          name="checkmark"
          color="#FFFFFF" />
      </div>
      <v-tooltip v-else bottom>
        <template v-slot:activator="{ on, attrs }">
          <div
            :style="styleChecked"
            v-bind="attrs"
            v-on="on"
            class="contacts-card-checked-form">

          </div>
        </template>
        <span>Отправка сообщений запрещена настройками безопасности портала</span>
      </v-tooltip>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
export default {
  name: "hmContactsCard",
    components: {SvgIcon},
    props:{
    dataCard:{type:Object, default:()=> {}},
    role: {type:String, default: ''},
    enablePersonalInfo: {type:[Boolean], default: true},
    disableMessages: {type:[Boolean], default: false},
  },
  data(){
    return {
      checked: false
    }
  },
  computed: {
    styleChecked() {
      if(this.checked) {return {background: '#2C98F0'}}
      else {return {border: '2px solid  rgba(0, 0, 0, 0.26)'}}
    },
    //свойство нахождения, добавлен ли уже пользователь
    checkedStore() {
       let result = this.$store.getters['dataContacts/selectedUser'].filter(el=> el.id === this.dataCard.id)
       result.length > 0 ? this.checked = true : this.checked = false
    }
  },
  methods: {
    /**
       * метод добавления пользователя
       */
    toggleSidebar() {
      this.$store.dispatch("sidebars/changeSidebarState", {
        name: 'subjectContacts',
        options: {
          opened: true
        }
      });
    },
    adduserChecked() {
      this.checked = !this.checked;
      let userData = {user: this.dataCard, flag: this.checked}
      this.$store.commit('dataContacts/SET__selectedUser',userData);
      this.toggleSidebar();
    },
  },
  mounted() {

  }
}
</script>

<style lang="scss">
#hmContactsCard {
  width: 320px;
  min-height: 112px;
  display: flex;
  background: #FFFFFF;
  box-shadow: 5px 5px 25px rgba(179, 179, 179, 0.25);
  border-radius: 8px;
  margin: 0 34px 34px 0;
  padding-top: 16px;
  .contacts-card-image {
    width: 44px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0  10px 0 23px;
    > div {
      display: flex;
      justify-content: center;
      align-items: center;
      > span {
        font-style: normal;
        font-weight: 500;
        font-size: 12px;
        line-height: 18px;
        letter-spacing: 0.15px;
      }
    }
    &__img {
      width: 36px;
      min-height: 36px;
      border-radius: 50%;
      margin-bottom: 5px;
    }
    &__role {
      margin-bottom: 1px;
      height: 18px;
    }
  }
  .contacts-card-info {
    width: 210px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    > div {
      width: 100%;
    }
    &__FIO {
      min-height: 30px;
      /*:TODO ДОПИСАТЬ*/
    }
    &__phone, &__mail, &__position {
      display: flex;
      align-items: center;
      margin: 4px 0;
      > svg {
        margin-right: 10px;
      }
      > a {
        text-decoration: none;
        color: #70889E;
        font-size: 12px;
        span {
          font-style: normal;
          font-weight: normal;
          line-height: 18px;
          letter-spacing: 0.15px;
        }
      }
      span {
        font-style: normal;
        font-weight: normal;
        line-height: 18px;
        letter-spacing: 0.15px;
        color: #70889E;
        font-size: 12px;
      }
    }
  }
  .contacts-card-checked {
    width: calc(100% - 216px - 71px);
    height: 100%;
    &-form {
      border: none;
      width: 18px;
      min-height: 18px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      transition: .2s ease-in-out;
    }
  }

}

//Общие стили

.user-card-online {
  position: relative;
  &::before {
    content: '';
    width: 9px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    background: #05C985;
    border-radius: 8px 0 0 8px;
  }
}

.user-card-ofline {
  position: relative;
  &::before {
    content: '';
    width: 9px;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    background: #70889E;
    border-radius: 8px 0 0 8px;
  }
}
@media(max-width: 960px) {
  #hmContactsCard {
    width: calc(50% - 8px);
    margin: 0;
    margin-right: 16px;
    margin-bottom: 16px;
    &:last-child {
      margin-bottom: 0;
    }
    &:nth-child(2n) {
      margin-right: 0;
    }
    & .contacts-card-info {
      width: calc(100% - 77px - 42px);
    }
    & .contacts-card-checked {
      width: 18px;
      margin: 0 12px;
    }
  }
}
@media(max-width: 640px) {
  #hmContactsCard {
    width: 100%;
    margin-right: 0;
  }
}
</style>
