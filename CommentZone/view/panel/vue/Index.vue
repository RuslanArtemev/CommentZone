<template>
  <div id="commentzone">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: "Index",
  data() {
    return {};
  },
  created() {
    this.$store.dispatch("getConfig");
    this.getCurrentUser();
  },
  methods: {
    getCurrentUser() {
      axios.post(this.$store.state.apiPath, { action: "getCurrentUser" }).then((respons) => {
        if (respons.data.id === undefined || respons.data === "no_authorize") {
          if (this.$route.name !== "login") {
            this.$router.push("/login");
          }
        } else {
          this.$store.commit("changeUser", respons.data);
          if (this.$route.name === "login") {
            this.$router.push("/");
          }
        }
      });
    },
  },
  watch: {
    $route(to, from) {
      this.getCurrentUser();
    },
  },
};
</script>

<style lang="scss">
#commentzone {
  position: fixed;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: $backgroundBody !important;
  color: $light;
  a {
    color: $light;
    &:hover,
    &.active {
      color: $lightHover;
    }
  }
  .form-control {
    background-color: $input;
    border-color: $input;
    color: white;
    &:focus {
      box-shadow: 0 0 0 0.25rem rgb(60 65 72 / 25%);
    }
    &::placeholder {
      color: $textPale;
    }
    &.is-invalid {
      border-color: $form-feedback-invalid-color !important;
    }
  }
  .form-check-input {
    background-color: $input;
  }
  .bg-box {
    background-color: $screenComments;
  }
  .btn-approve {
    background: $success;
    color: #fff;
  }
  .btn-dark-blue {
    background-color: $buttons;
    border-color: $buttons;
    color: $buttonsText;
    &:hover {
      background-color: $buttonsHover;
      border-color: $buttonsHover;
      color: lighten($buttonsText, 12%);
    }
    &:focus {
      box-shadow: 0 0 0 0.25rem rgb(84 96 113 / 15%);
    }
    &:active {
      background-color: lighten($buttons, 10%);
      border-color: lighten($buttons, 10%);
      color: lighten($buttonsText, 12%);
    }
  }
  .btn-outline-light {
    border-color: $buttonsOutline;
    color: $buttonsText;
    &:hover {
      background-color: $buttonsHover;
      border-color: $buttonsHover;
      color: lighten($buttonsText, 12%);
    }
    &:focus {
      box-shadow: 0 0 0 0.25rem rgb(84 96 113 / 15%);
    }
    &:active {
      background-color: lighten($buttons, 10%);
      border-color: lighten($buttons, 10%);
      color: lighten($buttonsText, 12%);
    }
  }
  .bg-dark-blue {
    background-color: $block;
    color: $light;
  }
  .bg-light-blue {
    background-color: $light;
    color: #000;
  }
  .text-pale {
    color: $textPale !important;
  }
  .bg-input {
    background-color: $input;
    border-color: $input !important;
    color: white;
  }
  .modal {
    background-color: #000000ab;
    color: $light;
    .modal-content {
      background-color: $block;
    }
    &.show {
      display: block;
    }
  }
  .dropdown-item,
  a.dropdown-item {
    color: $light;
  }
  .pointer {
    cursor: pointer;
  }
  .form-check-input.input-switch {
    &:checked {
      background-color: $switch;
      border-color: $switch;
    }
  }
  .popover-block {
    position: relative;
    .popover {
      top: -64px;
      left: 15px;
      .popover-arrow {
        transform: translate(10px, 0px);
        &::after {
          border-top-color: $informers !important;
          border-right-color: $informers !important
        }
      }
      .popover-body {
        background: $informers;
      }
    }
  }
  .bi {
    width: 1em;
    height: 1em;
    vertical-align: -0.125em;
  }
  .fs-small {
    font-size: 0.8rem !important;
  }
}
</style>