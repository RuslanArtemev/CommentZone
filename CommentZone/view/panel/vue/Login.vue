<template>
  <div class="container text-light p-4 mt-4 rounded auth-block">
    <div class="fs-4 mb-3 text-center">{{ $store.state.language.login }}</div>

    <form>
      <div class="mb-2">
        <label for="email" class="form-label">{{ $store.state.language.login_email }}</label>
        <input
          v-model.trim.lazy="email.value"
          type="email"
          class="form-control auth-input"
          :class="!email.validate.approved ? 'is-invalid' : ''"
          id="email"
        />
        <div class="invalid-feedback">{{ email.validate.message }}</div>
      </div>
      <div class="mb-2">
        <label for="password" class="form-label">{{ $store.state.language.login_password }}</label>
        <input
          v-model.trim="password.value"
          type="password"
          class="auth-input form-control"
          :class="!password.validate.approved ? 'is-invalid' : ''"
          id="password"
        />
        <div class="invalid-feedback">{{ password.validate.message }}</div>
      </div>
      <div class="mb-4 form-check">
        <input v-model="remember" type="checkbox" class="form-check-input" id="remember" />
        <label class="form-check-label" for="remember">{{ $store.state.language.remember_me }}</label>
      </div>

      <div class="row">
        <div class="col-auto">
          <button type="button" class="btn btn-outline-light" @click="login">{{ $store.state.language.sign_in }}</button>
        </div>

        <div class="col popover-block">
          <div class="popover fade bs-popover-end" :class="!common.validate.approved ? 'show' : ''">
            <div class="popover-arrow"></div>
            <div class="popover-body text-danger">{{ common.validate.message }}</div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "login",
  data() {
    return {
      email: {
        value: "",
        validate: {
          approved: true,
          message: "",
        },
      },
      password: {
        value: "",
        validate: {
          approved: true,
          message: "",
        },
      },
      common: {
        validate: {
          approved: true,
          message: "",
        },
      },
      remember: false,
    };
  },
  created() {
    document.addEventListener(
      "click",
      (e) => {
        this.common.validate.approved = true;
      },
      false
    );
  },
  methods: {
    validate(prop, approved = true, message = "") {
      prop.validate.approved = approved;
      prop.validate.message = message;
    },
    login() {
      if (this.email.value === "") {
        this.validate(this.email, false, this.$store.state.language.empty_field);
        return false;
      }
      if (this.password.value === "") {
        this.validate(this.password, false, this.$store.state.language.empty_field);
        return false;
      }

      axios
        .post(this.$store.state.apiPath, { action: "adminAuth", email: this.email.value, password: this.password.value, remember: this.remember })
        .then((response) => {
          if (response.data === "error-data-authorize") {
            this.validate(this.common, false, this.$store.state.language.invalid_data);
          }
          if (response.data === "permission") {
            this.validate(this.common, false, this.$store.state.language.no_access);
          }
          if (response.data.auth === true) {
            this.$store.commit('changeUser', response.data.user);
            this.$router.push("/");
          }
        });
    },
  },
  watch: {
    'email.value': function (val, old) {
      if (val !== "" && !/^[^@]+@[^@\.]+\.+[^@\.]+$/g.test(val)) {
        this.validate(this.email, false, this.$store.state.language.invalid_email);
      } else {
        this.validate(this.email);
      }

      if (val === "") {
        this.validate(this.email, false, this.$store.state.language.empty_field);
      }
    },
    'password.value': function (val, old) {
      if (val === "") {
        this.validate(this.password, false, this.$store.state.language.empty_field);
      } else {
        this.validate(this.password);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .auth-block {
    width: 400px;
    background-color: #31353a;
    box-shadow: 0 0 8px 0 #242f3a;
    .auth-input {
      background-color: #26292d;
      border-color: #26292d;
      color: #fff;
      &.is-invalid {
        border-color: #dc3545;
      }
    }
  }
  .popover-block {
    position: relative;
    .popover {
      top: -8px;
      left: 0;
      .popover-arrow {
        transform: translate(0px, 19px);
      }
    }
  }
}
</style>