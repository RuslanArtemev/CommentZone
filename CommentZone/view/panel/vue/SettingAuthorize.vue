<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.authorize }}</div>

    <div class="col">
      <form>
        <div class="mb-2 p-4 bg-box">
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.authorize }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="authorizeSwitch" v-model="setting.authorize" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.anonymous_users }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="anonimusSwitch" v-model="setting.anonimus" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.guest }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="guestSwitch" v-model="setting.guest" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.guest_email_required }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.guest_email_required" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.cookie_domain }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.cookie_domain" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.cookie_time }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.cookie_time" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.recaptcha }}</div>

          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="recaptchaSwitch" v-model="setting.recaptcha" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.current_version }}</div>
            <div class="col">
              <select class="form-select bg-input" v-model="setting.recaptchaVersion">
                <option value="v2">v2</option>
                <option value="v3">v3</option>
              </select>
            </div>
          </div>

          <div v-if="setting.recaptchaVersion === 'v3'" class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.recaptcha_user_score }} (0.0 - 1.0)</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.recaptchaScoreV3" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.recaptca_key_v2 }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.recaptchaKeyV2" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.recaptca_secret_key_v2 }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.recaptchaSecretKeyV2" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.recaptca_key_v3 }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.recaptchaKeyV3" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.recaptca_secret_key_v3 }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.recaptchaSecretKeyV3" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.vk }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="vkSwitch" v-model="setting.vk" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.vkRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.vkClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.vkClientSecret" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.fields }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.vkFields" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.api_version }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.vkApiVersion" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.ok }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="okSwitch" v-model="setting.ok" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.okRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.okClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.okClientSecret" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.public_key }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.okPublicKey" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.fb }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="fbSwitch" v-model="setting.fb" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.fbRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.fbClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.fbClientSecret" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.fields }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.fbFields" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.google }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="gooSwitch" v-model="setting.goo" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.gooRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.gooClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.gooClientSecret" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.mw }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="mailSwitch" v-model="setting.mail" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.mailRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.mailClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.mailClientSecret" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.yandex }}</div>
          
          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="yaSwitch" v-model="setting.ya" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.redirect_uri }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.yaRedirectUri" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_id }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.yaClientId" />
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.client_secret }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="setting.yaClientSecret" />
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
      axios.post(this.$store.state.apiPath, { action: "getSetting", category: "authorize" }).then((response) => {
        this.setting = response.data.setting;
      });
    },
    setSetting() {
      axios.post(this.$store.state.apiPath, { action: "setSetting", setting: this.setting }).then((response) => {
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