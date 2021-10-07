<template>
  <div v-if="currentUser.id && page.id" class="col-6 m-auto">
    <form>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Title</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" v-model="page.title" />
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Url</label>
        <div class="col-sm-10">
          <input type="email" class="form-control" v-model="page.url" />
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Bind ID</label>
        <div class="col-sm-10">
          <input type="email" class="form-control" v-model="page.bind_id" />
        </div>
      </div>

      <button type="button" class="btn btn-dark-blue shadow-sm" @click="setPage()" :disabled="saveDisabled">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
          <path
            d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"
          />
        </svg>
        {{ $store.state.language.save }}
      </button>
      <button type="button" class="btn btn-outline-light shadow-sm" @click="$router.go(-1)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
          <path
            d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
          />
        </svg>
        {{ $store.state.language.close }}
      </button>
    </form>

    <toast v-if="toast.show" :bg="toast.bg" :message="toast.message" @close="$emit ? (toast.show = false) : ''"></toast>
  </div>
</template>

<script>
export default {
  data() {
    return {
      page: [],
      saveDisabled: false,
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  created() {
    this.getPage();
  },
  computed: {
    currentUser() {
      return this.$store.state.user;
    },
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    getPage() {
      axios.post(this.$store.state.apiPath, { action: "getPage", id: this.$route.params.id }).then((response) => {
        this.page = response.data;
      });
    },
    setPage() {
      let set = {
        title: this.page.title,
        url: this.page.url,
        bind_id: this.page.bind_id === "" ? null : this.page.bind_id,
      };
      axios
        .post(this.$store.state.apiPath, {
          action: "setPage",
          id: this.page.id,
          set,
        })
        .then((response) => {
          if (response.data === true) {
            this.toastShow(this.$store.state.language.success, "bg-success");
          } else {
            this.toastShow(this.$store.state.language.error, "bg-danger");
          }
        });
    },
  },
};
</script>

<style lang="scss" scoped>
</style>