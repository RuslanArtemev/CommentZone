<template>
  <div v-if="$store.state.user.permission.indexOf('manage_role') !== -1" class="p-0">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.roles }}</div>

    <div class="row">
      <div class="col-md-4 mb-4">
        <div
          class="row bg-box mx-0 mb-2 pointer role-item"
          :class="activeRole === key ? 'active' : ''"
          v-for="(role, key) in roles"
          :key="key"
        >
          <div class="col-md-9 col-10 px-3 py-2" @click="selectRole(key)">@{{ key }}</div>
          <div class="col-md-3 col-2 px-3 py-2 text-end">
            <div class="d-inline-block btn-dark rounded" @click="deleteRole(key)">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 18">
                <path
                  d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                />
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-box p-2 mb-2 pointer" :class="activeRole === key ? 'active' : ''" v-for="(role, key) in addedRoles" :key="key">
          <div class="input-group">
            <span class="input-group-text bg-pale border-pale text-pale">@</span>
            <input type="text" class="form-control ps-0" v-focus v-model="addedRoles[key]" placeholder="Name" />
            <button @click="approveAddedRole(key)" class="btn btn-dark" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
              </svg>
            </button>
            <button @click="disapproveAddedRole(key)" class="btn btn-dark" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                <path
                  d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                />
              </svg>
            </button>
          </div>
        </div>

        <div class="d-grid gap-2">
          <button class="btn btn-blue text-white shadow-sm" type="button" @click="addItems">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-plus-circle"
              viewBox="0 0 16 16"
            >
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
              <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
            </svg>
            <span>{{ $store.state.language.add }}</span>
          </button>
        </div>
      </div>
      
      <div class="col-md-8">
        <div class="bg-box p-4">
          <div class="row p-2 my-2" v-for="(permission, key) in permissions" :key="key">
            <div class="col-11">
              <div>{{ permission.name }}</div>
              <div class="text-pale fs-small">{{ permission.code }}</div>
            </div>
            <div class="col-1">
              <div class="form-check form-switch mt-2">
                <input class="form-check-input input-switch" :value="permission.code" v-model="roles[activeRole]" type="checkbox" />
              </div>
            </div>
          </div>
        </div>
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
      roles: [],
      permissions: [],
      activeRole: "admin",
      addedRoles: [],
      toast: {
        show: false,
        bg: "",
        message: "",
      },
    };
  },
  directives: {
    focus: {
      inserted: function (e) {
        e.focus();
      },
    },
  },
  created() {
    if (this.$store.state.user.permission.indexOf("manage_role") === -1) {
      return false;
    }

    this.getRoles();
    this.getPermissions();
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    approveAddedRole(index) {
      this.$set(this.roles, this.addedRoles[index], []);
      this.activeRole = this.addedRoles[index];
      this.addedRoles.splice(index, 1);
    },
    disapproveAddedRole(index) {
      this.addedRoles.splice(index, 1);
    },
    addItems() {
      this.addedRoles.push("");
    },
    deleteRole(index) {
      this.$delete(this.roles, index);

      if (this.activeRole === index) {
        let rolesKey = Object.keys(this.roles);

        if (rolesKey.length > 0) {
          this.selectRole(rolesKey[0]);
        }
      }
    },
    getRoles() {
      axios.post(this.$store.state.apiPath, { action: "getRoles" }).then((response) => {
        this.roles = response.data;
      });
    },
    getPermissions() {
      axios.post(this.$store.state.apiPath, { action: "getPermissions" }).then((response) => {
        this.permissions = response.data;
      });
    },
    selectRole(name) {
      this.activeRole = name;
    },
    save() {
      axios.post(this.$store.state.apiPath, { action: "updateRoles", roles: this.roles }).then((response) => {
        if (response.data === "permission") {
          this.toastShow(this.$store.state.language.no_access, "bg-danger");
          return false;
        }

        if (response.data === true) {
          this.toastShow(this.$store.state.language.success, "bg-success");
        } else {
          this.toastShow(this.$store.state.language.not_save, "bg-danger");
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.active {
  background-color: #434e5a !important;
}
</style>