<template>
  <div v-if="$store.state.user.permission.indexOf('manage_role') !== -1" class="p-0">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.permissions }}</div>

    <div class="">
      <div class="row mx-1 mb-2" v-for="(items, key) in permissions" :key="key">
        <div class="col-md-4 col-lg-5 col-xl-5 px-3 py-2 bg-box">
          <input type="text" class="form-control" v-model="items.name" placeholder="Name" />
        </div>
        <div class="col-md-4 col-lg-4 col-xl-5 px-3 py-2 bg-box">
          <input type="text" class="form-control" v-model="items.code" placeholder="Code" />
        </div>
        <div class="col-md-4 col-lg-3 col-xl-2 px-3 py-2 bg-box text-end">
          <button type="button" class="btn btn-dark-blue shadow-sm mx-1" @click="deleteItems(key)">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
              <path
                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"
              />
              <path
                fill-rule="evenodd"
                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"
              />
            </svg>
            <span>{{ $store.state.language.delete }}</span>
          </button>
        </div>
      </div>

      <div class="d-grid gap-2 col-6 mx-auto mt-4">
        <button class="btn btn-blue text-white shadow-sm" type="button" @click="addItems">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
          </svg>
          <span>{{ $store.state.language.add }}</span>
        </button>
      </div>
    </div>

    <div class="mt-4">
      <button type="button" class="btn btn-dark-blue shadow-sm" @click="save">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
          <path
            d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"
          />
        </svg>
        {{ $store.state.language.save }}
      </button>
    </div>

    <toast v-if="toast.show" :bg="toast.bg" :message="toast.message" @close="$emit ? (toast.show = false) : ''"></toast>
  </div>
  <div v-else>
    <div class="text-pale fs-2 my-4 text-center">{{ $store.state.language.no_access }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      permissions: [],
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  created() {
    if (this.$store.state.user.permission.indexOf("manage_role") === -1) {
      return false;
    }

    this.getPermissions();
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    addItems() {
      this.permissions.push({ name: "", code: "" });
    },
    deleteItems(index) {
      this.permissions.splice(index, 1);
    },
    getPermissions() {
      axios.post(this.$store.state.apiPath, { action: "getPermissions" }).then((response) => {
        this.permissions = response.data;
      });
    },
    save() {
      axios.post(this.$store.state.apiPath, { action: "updatePermissions", permissions: this.permissions }).then((response) => {
        if (response.data === "permission") {
          this.toastShow(this.$store.state.language.no_access, "bg-danger");
          return false;
        }

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
.active {
  background-color: $input !important;
}
</style>