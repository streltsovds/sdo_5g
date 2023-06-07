<template>
<v-container>

    <img id='logo' style='max-width:65%; margin-bottom:10%' src='/assets/logo.svg' />
    <v-spacer></v-spacer>
    <div  align="left">
                    <h2>Авторизация</h2>
                    <p>Для входа в приложение введите следующие параметры доступа</p>
    </div>
    <v-spacer></v-spacer>
    <v-form>
        <v-text-field label="Имя пользователя" name="login" prepend-icon="person" type="text" v-model="account.user"></v-text-field>
        <form-password :element="{label:'Пароль', prependIcon:'lock'}" v-model="account.password"></form-password>
        <v-text-field label="Адрес eLearning-сервера" name="host" prepend-icon="home" type="text" v-model="account.host"></v-text-field>
    </v-form>
    <v-spacer></v-spacer>
    <v-btn @click="doLogin" color="positive" style='position:absolute; left:0; top:85%; width:96%;color:white; background-color:#ff9800'>Войти</v-btn>
</v-container>

</template>

<script>
import formPassword from "../../src/components/hm-form/partials/formPassword";

import storage from "../services/storage";
//import svgIcon from '../../src/components/icons/svgIcon'
//import formPassword from "./formPassword";
//import { Plugins, GeolocationOptions } from "@capacitor/core";
//const { Geolocation } = Plugins;

require("../assets/logo.svg");

export default {
  name: "AuthPage",
  components: {
//    svgIcon,
    formPassword
  },
  data() {
    return {
      account : {//temp filled
user:'admin',
password:'pass',
host:'http://develop50',
      },
      location: {}
    };
  },
  methods: {
    doLogin(){
        fetch(this.account.host+'/mobile/auth/login', {
          method: 'post',
          body: JSON.stringify({login: this.account.user, password: this.account.password}),

        })
        .then(response => response.json())
        .then(data => {
console.log(data.user);
            storage['account'] = this.account;
            storage['user'] = data.user;
            storage['menu'] = data.menu;

            this.$router.replace('/');
        });

    }
  }
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
@media screen and (max-device-height: 500px) {
    #logo {display:none;}
}
</style>
