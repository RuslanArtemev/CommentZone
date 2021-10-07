<template>
  <div v-if="currentUser.id && user.id" class="col-10 col-lg-8 col-xl-6 m-auto">
    <form>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ $store.state.language.avatar }}</label>
        <div class="col-sm-10">
          <div class="row">
            <div
              v-if="!showloadAvatar"
              class="col-auto p-0 mx-3 avatar"
              :style="
                'background: url(' +
                (user.avatar !== null
                  ? $store.state.config.resource + user.avatar.small
                  : $store.state.config.resource + '/img/avatars/default.jpg') +
                ') center / contain no-repeat;'
              "
            ></div>
            <div
              v-if="showloadAvatar"
              class="col-auto p-0 mx-3 avatar"
              :style="'background: url(' + $store.state.config.resource + '/img/icons/spinner.gif) center / contain no-repeat'"
            ></div>
            <label for="avatar" class="col-auto btn btn-outline-light">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-upload" viewBox="0 0 16 16">
                <path
                  d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"
                />
                <path
                  d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"
                />
              </svg>
              {{ $store.state.language.upload_avatar }}
            </label>
            <div class="col-auto">
              <button type="button" class="btn btn-dark-blue shadow-sm" @click="deleteAvatar">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                  <path
                    d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"
                  />
                  <path
                    fill-rule="evenodd"
                    d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"
                  />
                </svg>
                {{ $store.state.language.delete }}
              </button>
            </div>
            <input type="file" accept="image/jpeg,image/png" id="avatar" class="d-none" @change="uploadAvatar" />
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ $store.state.language.name }}</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" v-model="user.name" />
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ $store.state.language.email }}</label>
        <div class="col-sm-10">
          <input type="email" class="form-control" v-model="user.email" />
        </div>
      </div>
      <div v-if="user.role !== 'admin'" class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ $store.state.language.role }}</label>
        <div class="col-sm-10">
          <select class="form-select bg-input" v-model="user.role">
            <option v-for="(permissions, name) in roles" :key="name" :value="name">{{ name }}</option>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">{{ $store.state.language.password }}</label>
        <div class="col-sm-10">
          <input type="email" class="form-control" v-model="password" />
        </div>
      </div>

      <button type="button" class="btn btn-dark-blue shadow-sm" @click="setProfile()" :disabled="saveDisabled">
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
      user: [],
      roles: [],
      password: "",
      saveDisabled: false,
      changedAvatar: false,
      showloadAvatar: false,
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  created() {
    this.getRoles();
    this.getProfile();
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
    getProfile() {
      axios.post(this.$store.state.apiPath, { action: "getProfile", id: this.$route.params.id }).then((response) => {
        this.user = response.data;
      });
    },
    getRoles() {
      axios.post(this.$store.state.apiPath, { action: "getRoles" }).then((response) => {
        this.roles = response.data;
      });
    },
    setProfile() {
      let set = {
        name: this.user.name,
        email: this.user.email,
        role: this.user.role,
      };
      if (this.changedAvatar) {
        this.$set(set, "avatar", this.user.avatar);
      }
      axios
        .post(this.$store.state.apiPath, {
          action: "setProfile",
          uid: this.user.id,
          password: this.password,
          set,
        })
        .then((response) => {
          if (response.data === true) {
            this.toastShow(this.$store.state.language.success, "bg-success");
            this.changedAvatar = false;
          }
        });
    },
    uploadAvatar(e) {
      var data = new FormData();
      data.append("action", "uploadAvatar");
      data.append("file", e.target.files[0]);

      this.saveDisabled = true;
      this.showloadAvatar = true;

      axios.post(this.$store.state.apiPath, data).then((response) => {
        if (response.data !== false) {
          this.user.avatar = response.data;
        }

        this.saveDisabled = false;
        this.showloadAvatar = false;
        this.changedAvatar = true;
      });
    },
    deleteAvatar() {
      this.changedAvatar = true;
      this.user.avatar = null;
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .avatar {
    width: 38px;
    height: 38px;
  }
}
</style>