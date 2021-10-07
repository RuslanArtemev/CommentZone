<template>
  <div class="modal fade show">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title">{{ $store.state.language.blocking }}</h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('click', false)"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" v-model="banParams.permanent" id="banPermanent" />
              <label class="form-check-label" for="banPermanent"> {{ $store.state.language.permanent }} </label>
            </div>
            <div class="my-2 text-center fw-light">{{ $store.state.language.or }}</div>
            <div class="row">
              <div class="col">
                <label for="countDaysBanned" class="form-label">{{ $store.state.language.number_days }}</label>
                <input
                  class="form-control"
                  :class="!banValidate.countDays.approved ? 'is-invalid' : ''"
                  type="text"
                  v-model.number="banParams.countDays"
                  id="countDaysBanned"
                />
                <div class="invalid-feedback">{{ banValidate.countDays.message }}</div>
              </div>
            </div>
            <div class="my-2 text-center fw-light">{{ $store.state.language.or }}</div>
            <div>{{ $store.state.language.date }}</div>
            <div class="row">
              <div class="col">
                <input type="date" class="form-control" v-model="banParams.date" />
              </div>
            </div>
            <div class="mt-3">
              <label for="bannedNote" class="form-label">{{ $store.state.language.note }}</label>
              <textarea class="form-control" id="bannedNote" rows="3" v-model="banParams.note"></textarea>
            </div>

            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" v-model="banParams.banIp" id="banIp" />
              <label class="form-check-label" for="banIp"> {{ $store.state.language.ban_ip }} </label>
            </div>
          </form>
        </div>

        <div class="popover-block">
          <div class="popover fade bs-popover-top" :class="!banValidate.common.approved ? 'show' : ''">
            <div class="popover-arrow"></div>
            <div class="popover-body text-danger">{{ banValidate.common.message }}</div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-dark-blue shadow-sm" id="banButton" @click="ban">
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
            {{ $store.state.language.lock }}
          </button>
          <button type="button" class="btn btn-outline-light shadow-sm" @click="$emit('click', false)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
              <path
                d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
              />
            </svg>
            {{ $store.state.language.close }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      banParams: {
        permanent: false,
        countDays: null,
        date: null,
        note: null,
        banIp: null,
      },
      banValidate: {
        common: {
          approved: true,
          message: "",
        },
        countDays: {
          approved: true,
          message: "",
        },
      },
    };
  },
  props: ["checkedId"],
  methods: {
    validate(prop, approved = true, message = "") {
      prop.approved = approved;
      prop.message = message;

      document.addEventListener(
        "click",
        (e) => {
          if (e.target.id !== "banButton") {
            this.banValidate.common.approved = true;
          }
        },
        false
      );
    },
    ban() {
      if (this.checkedId.length === 0) {
        this.validate(this.banValidate.common, false, this.$store.state.language.user_not_selected);
        return false;
      }
      if (!this.banParams.permanent && !this.banParams.countDays && !this.banParams.date) {
        this.validate(this.banValidate.common, false, this.$store.state.language.choose_option);
        return false;
      }

      if (this.banParams.countDays && !parseFloat(this.banParams.countDays) && !this.banParams.date) {
        this.validate(this.banValidate.countDays, false, this.$store.state.language.need_enter_number);
        return false;
      }

      axios.post(this.$store.state.apiPath, { action: "banUser", idList: this.checkedId, banParams: this.banParams }).then((response) => {
        if (response.data === true) {
          this.$emit("click", true);
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.popover-block {
  position: relative;
  .popover {
    top: -30px;
    left: 0;
    .popover-arrow {
      transform: translate(100px, 0px);
      &::after {
        border-top-color: #3a3a3a !important;
      }
    }
    .popover-body {
      background: #3a3a3a;
      color: $danger !important;
    }
  }
}
</style>