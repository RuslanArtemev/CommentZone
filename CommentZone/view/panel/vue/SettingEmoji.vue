<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">
      {{ $store.state.language.setting }}
    </div>

    <div class="col">
      <form>
        <div class="mb-2 p-4 bg-box emoji-setting">
          <div
            v-for="(item, key) in emoji"
            :key="'emoji-' + key"
            class="emoji-item"
            :class="emojiSelected[key] ? 'active' : ''"
            :title="key + ' - ' + item.name + ' - ' + item.sign"
            @mousedown="select(key)"
            @mouseenter="selectHover($event, key)"
          >
            <div
              :style="
                'width:32px; height: 32px; background: url(' +
                $store.state.config.resource +
                '/img/emoji/emoji-icons.png) -' +
                item.position[0] +
                'px -' +
                item.position[1] +
                'px / 1344px;'
              "
            ></div>
          </div>
        </div>

        <div class="mt-4">
          <button
            @click="setEmoji()"
            type="button"
            class="btn btn-dark-blue shadow-sm"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-check-circle"
              viewBox="0 0 16 16"
            >
              <path
                d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"
              />
              <path
                d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"
              />
            </svg>
            {{ $store.state.language.save }}
          </button>
        </div>
      </form>
    </div>

    <toast
      v-if="toast.show"
      :bg="toast.bg"
      :message="toast.message"
      @close="$emit ? (toast.show = false) : ''"
    ></toast>
  </div>
</template>

<script>
export default {
  data() {
    return {
      emoji: [],
      emojiSelected: [],
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  created() {
    this.getEmoji();
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    select(key) {
      if (!this.emojiSelected[key]) {
        this.$set(this.emojiSelected, key, this.emoji[key]);
      } else {
        this.$delete(this.emojiSelected, key);
      }
    },
    selectHover(e, key) {
      if (e.which) {
        this.select(key);
      }
    },
    getEmoji() {
      axios
        .post(this.$store.state.apiPath, { action: "getEmoji" })
        .then((response) => {
          this.emoji = response.data.emoji;
          this.emojiSelected = response.data.emojiView;
        });
    },
    setEmoji() {
      let emojiView = {};

      for (const key in this.emojiSelected) {
        if (Object.hasOwnProperty.call(this.emojiSelected, key)) {
          const element = this.emojiSelected[key];
          emojiView[key] = element;
        }
      }

      console.log(emojiView);

      axios
        .post(this.$store.state.apiPath, {
          action: "setEmoji",
          data: emojiView,
        })
        .then((response) => {
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
#commentzone {
  .emoji-setting {
    display: flex;
    flex-wrap: wrap;
    .emoji-item {
      padding: 0.5rem;
      margin: 0.3rem;
      border-radius: 0.2rem;
      &:hover,
      &.active {
        background-color: #686f76;
      }
    }
  }
}
</style>