<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.setting }}</div>

    <div class="col">
      <form>
        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.common }}</div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.template }}</div>
            <div class="col">
              <select class="form-select bg-input" v-model="setting.theme">
                <option v-for="(theme, index) in themes" :key="index" :value="theme">{{ theme }}</option>
              </select>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.language }}</div>
            <div class="col">
              <select class="form-select bg-input" v-model="setting.language">
                <option v-for="(lang, index) in language" :key="index" :value="lang">{{ lang }}</option>
              </select>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.comment_length }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.text_length" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.nesting_level }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.level" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.count_comments_on_page }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.limit" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.default_sorting }}</div>
            <div class="col">
              <select class="form-select bg-input" v-model="setting.sort">
                <option value="new">{{ $store.state.language.new_ones }}</option>
                <option value="old">{{ $store.state.language.old }}</option>
                <option value="pop">{{ $store.state.language.popular }}</option>
              </select>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.delete_method }}</div>
            <div class="col">
              <select class="form-select bg-input" v-model="setting.delete_method">
                <option value="unposted">{{ $store.state.language.mark_deleted }}</option>
                <option value="delete">{{ $store.state.language.remove_from_database }}</option>
              </select>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.date_format }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.date_format" />
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.video }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.video" id="videoSwitch" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.emoticons }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.emoji" id="emojiSwitch" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.links }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.links" id="emojiSwitch" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.comment_rating }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.rating" id="ratingSwitch" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.query_in_url }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.url_query" id="urlQuerySwitch" />
              </div>
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.editing }}</div>

          <div class="row mb-3">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" id="editSwitch" v-model="setting.edit" />
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.edit_time }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.editTime" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.moderation }}</div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.moderate_before }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.moderation" />
              </div>
            </div>
          </div>

          <div v-if="!setting.moderation && setting.guest" class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.moderate_guests }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.moderationGuest" />
              </div>
            </div>
          </div>

          <div v-if="!setting.moderation && setting.anonimus" class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.moderate_anonymous }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.moderationAnonimus" />
              </div>
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.notifications }}</div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.user_notifications }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.notifyUsers" />
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.admin_notifications }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.notifyAdmin" />
              </div>
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.images }}</div>

          <div class="row mb-2">
            <div class="col-10 col-sm-5 col-form-label">{{ $store.state.language.on }}</div>
            <div class="col-2 col-sm-auto">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" type="checkbox" v-model="setting.images" id="imagesSwitch" />
              </div>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.file_size_kb }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.uploadImageSize" />
            </div>
          </div>

          <div class="text-pale fs-5 mt-4 mb-2">{{ $store.state.language.screen_resolutions }}</div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.big }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeLong[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeLong[1]" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.middle }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeMiddle[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeMiddle[1]" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.small }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeSmall[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="images.sizeSmall[1]" />
            </div>
          </div>
        </div>

        <div class="mb-2 p-4 bg-box">
          <div class="text-pale fs-5 mb-4">{{ $store.state.language.avatars }}</div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.file_size_kb }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model.number="setting.avatarSize" />
            </div>
          </div>

          <div class="text-pale fs-5 mt-4 mb-2">{{ $store.state.language.screen_resolutions }}</div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.big }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeLong[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeLong[1]" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.middle }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeMiddle[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeMiddle[1]" />
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-sm-5 col-form-label">{{ $store.state.language.small }}</div>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeSmall[0]" />
            </div>
            <span class="separator my-2"></span>
            <div class="col">
              <input type="text" class="form-control" v-model="avatars.sizeSmall[1]" />
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
      themes: [],
      language: [],
      setting: [],
      images: {
        sizeLong: [],
        sizeMiddle: [],
        sizeSmall: [],
      },
      avatars: {
        sizeLong: [],
        sizeMiddle: [],
        sizeSmall: [],
      },
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
      axios.post(this.$store.state.apiPath, { action: "getSetting" }).then((response) => {
        this.themes = response.data.themes;
        this.language = response.data.language;
        this.setting = response.data.setting;

        this.images.sizeLong = this.setting.uploadImageLongSize.split(",");
        this.images.sizeMiddle = this.setting.uploadImageMiddleSize.split(",");
        this.images.sizeSmall = this.setting.uploadImageSmallSize.split(",");

        this.avatars.sizeLong = this.setting.avatarSizeLong.split(",");
        this.avatars.sizeMiddle = this.setting.avatarSizeMiddle.split(",");
        this.avatars.sizeSmall = this.setting.avatarSizeSmall.split(",");
      });
    },
    setSetting() {
      this.setting.uploadImageLongSize = this.images.sizeLong.join();
      this.setting.uploadImageMiddleSize = this.images.sizeMiddle.join();
      this.setting.uploadImageSmallSize = this.images.sizeSmall.join();

      this.setting.avatarSizeLong = this.avatars.sizeLong.join(",");
      this.setting.avatarSizeMiddle = this.avatars.sizeMiddle.join(",");
      this.setting.avatarSizeSmall = this.avatars.sizeSmall.join(",");

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
.separator {
  box-sizing: content-box;
  width: 1em;
  height: 1em;
  padding: 0.25em 0.25em;
  filter: invert(1) grayscale(100%) brightness(200%);
  opacity: 0.3;
  background: transparent
    url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e")
    center/1em auto no-repeat;
}
</style>