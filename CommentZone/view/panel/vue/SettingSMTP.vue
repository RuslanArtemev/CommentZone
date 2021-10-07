<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">SMTP</div>

    <div class="col">
      <form>
        <div class="mb-2 p-4 bg-box">
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="smtpSwitch" v-model="setting.smtpMail" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.smtp_login }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.smtpMailLogin" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.smtp_password }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.smtpMailPassword" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.smtp_host }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.smtpMailHost" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.smtp_port }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.smtpMailPort" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.smtp_encoding }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.smtpMailEncoding" />
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="button" class="btn btn-dark-blue shadow-sm" @click="setSetting">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-check-circle"
              viewBox="0 0 16 16"
            >
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
              <path
                d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"
              />
            </svg>
            {{ $store.state.language.save }}
          </button>
        </div>
      </form>
    </div>

    <toast v-if="toast.show" :bg="toast.bg" :message="toast.message" @close="$emit ? (toast.show = false) : ''"></toast>
  </div>
</template>

<script>
export default {
  data() {
    return {
      setting: [],
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  created() {
    this.getSetting();
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    getSetting() {
      axios.post(this.$store.state.apiPath, { action: "getSetting", category: "antiflood" }).then((response) => {
        this.setting = response.data.setting;
      });
    },
    setSetting() {
      axios.post(this.$store.state.apiPath, { action: "setSetting", setting: this.setting }).then((response) => {
        if (response.data) {
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