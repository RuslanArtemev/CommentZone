export default {
  state: {
    apiPath: document.getElementById('commentPanel').getAttribute('data-api-path'),
    config: [],
    language: [],
    user: [],
    notice: {
      show: false,
      type: "",
      message: "",
    }
  },
  mutations: {
    changeConfig(state, payload) {
      state.config = payload;
    },
    changeLanguage(state, payload) {
      state.language = payload;
    },
    changeUser(state, payload) {
      state.user = payload;
    },
    setNotice(state, payload) {
      state.notice.show = payload.show;
      state.notice.type = payload.type;
      state.notice.message = payload.message;
    },
  },
  actions: {
    getConfig({ commit, state }, payload) {
      axios.post(state.apiPath, { action: "getConfig" }).then((respons) => {
        commit("changeConfig", respons.data);
        commit("changeLanguage", respons.data.language);
      });
    },
  }
}