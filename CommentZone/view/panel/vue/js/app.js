import Vue from 'vue'
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import routes from './routes';
import createStore from "./store";
import Methods from './methods';
import Index from '../Index.vue';

import Comment from "../components/Comment.vue";
import Pagination from "../components/Pagination.vue";
import ModalConfirm from "../components/ModalConfirm.vue";
import ModalBan from "../components/ModalBan.vue";
import Toast from "../components/Toast.vue";
import Loader from "../components/Loader.vue";
import Images from "../components/Images.vue";

window.axios = require('axios');
axios.defaults.headers.common = {
  'Cz-Csrf-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};

Vue.config.productionTip = false
Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(Methods);
Vue.component('comment', Comment);
Vue.component('pagination', Pagination);
Vue.component('ModalConfirm', ModalConfirm);
Vue.component('ModalBan', ModalBan);
Vue.component('toast', Toast);
Vue.component('loader', Loader);
Vue.component('imagesView', Images);


const router = new VueRouter({
  mode: 'hash',
  routes,
})

const store = new Vuex.Store(createStore);

const app = new Vue({
  el: '#commentPanel',
  render: h => h(Index),
  router,
  store
})