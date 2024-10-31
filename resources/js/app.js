// Vuetify
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import ToastPlugin from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-default.css';

import "./bootstrap";
import router from "./router";
import { createApp } from "vue";

import App from "./App.vue";

const vuetify = createVuetify({
  components,
  directives,
})
const app = createApp(App)
app.use(vuetify)
app.use(router)
app.use(ToastPlugin, {
  // One of the options
  position: 'top-right'
})
app.mount("#app");