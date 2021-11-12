<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 border-bottom border-pale pb-2">{{ $store.state.language.users }}: {{ countUsers }}</div>

    <div class="row my-4">
      <div class="col-auto">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.role }}</span>
          <select class="form-select form-select-sm bg-input" v-model="filters.role">
            <option :value="false">- {{ $store.state.language.all }} -</option>
            <option v-for="(permissions, name) in roles" :key="name" :value="name">{{ name }}</option>
          </select>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" v-model="filters.banned" id="bannedSwitch" />
          <label class="form-check-label" for="bannedSwitch">{{ $store.state.language.banned }}</label>
        </div>
      </div>
      <div class="col-sm-auto">
        <div class="form-check mt-2">
          <input class="form-check-input" type="checkbox" v-model="filters.deleted" id="deletedSwitch" />
          <label class="form-check-label" for="deletedSwitch">{{ $store.state.language.deleted_items }}</label>
        </div>
      </div>
    </div>

    <div class="row my-4">
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.email }}</span>
          <input type="text me-1" class="form-control" v-model="filters.email" @change="getUsers()" @keyup.enter="getUsers()" />
        </div>
      </div>
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale text-uppercase">{{ $store.state.language.ip }}</span>
          <input type="text" class="form-control" v-model="filters.ip" @change="getUsers()" @keyup.enter="getUsers()" />
        </div>
      </div>
    </div>

    <div>
      <div class="row m-0 p-2 bg-box" v-if="currentUser.permission && currentUser.permission.indexOf('manage_users') !== -1">
        <div class="col-auto mt-1">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" v-model="changeSelector" />
          </div>
        </div>
        <div class="col-10 overflow-hidden">
          <div v-menuHorizontal class="text-nowrap d-inline-block">
            <button
              v-if="filters.deleted"
              class="btn btn-dark-blue shadow-sm btn-sm my-1"
              type="button"
              :disabled="checkedId.length > 0 ? false : true"
              @click="modalToggle('recoverModal')"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                class="bi bi-arrow-counterclockwise"
                viewBox="0 0 16 16"
              >
                <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z" />
                <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z" />
              </svg>
              {{ $store.state.language.recover }}
            </button>
            <button
              v-if="!filters.deleted"
              class="btn btn-dark-blue shadow-sm btn-sm my-1"
              type="button"
              :disabled="checkedId.length > 0 ? false : true"
              @click="modalToggle('deleteModal')"
            >
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
            <button
              v-if="filters.deleted"
              class="btn btn-dark-blue shadow-sm btn-sm my-1"
              type="button"
              :disabled="checkedId.length > 0 ? false : true"
              @click="modalToggle('removeModal')"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash2" viewBox="0 0 16 16">
                <path
                  d="M14 3a.702.702 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671L2.037 3.225A.703.703 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2zM3.215 4.207l1.493 8.957a1 1 0 0 0 .986.836h4.612a1 1 0 0 0 .986-.836l1.493-8.957C11.69 4.689 9.954 5 8 5c-1.954 0-3.69-.311-4.785-.793z"
                />
              </svg>
              {{ $store.state.language.delete_from_database }}
            </button>
            <span v-if="!filters.deleted || filters.banned">
              <button
                v-if="filters.banned"
                class="btn btn-dark-blue shadow-sm btn-sm my-1"
                type="button"
                :disabled="checkedId.length > 0 ? false : true"
                @click="unban()"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-check-lg"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M13.485 1.431a1.473 1.473 0 0 1 2.104 2.062l-7.84 9.801a1.473 1.473 0 0 1-2.12.04L.431 8.138a1.473 1.473 0 0 1 2.084-2.083l4.111 4.112 6.82-8.69a.486.486 0 0 1 .04-.045z"
                  />
                </svg>
                {{ $store.state.language.unlock }}
              </button>
              <button
                v-if="!filters.banned"
                class="btn btn-dark-blue shadow-sm btn-sm my-1"
                type="button"
                :disabled="checkedId.length > 0 ? false : true"
                @click="modalToggle('banModal')"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-person-bounding-box"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM8 2c1.3 0 2.5 0.4 3.5 1.1l-8.4 8.4c-0.7-1-1.1-2.2-1.1-3.5 0-3.3 2.7-6 6-6zM8 14c-1.3 0-2.5-0.4-3.5-1.1l8.4-8.4c0.7 1 1.1 2.2 1.1 3.5 0 3.3-2.7 6-6 6z"
                  ></path>
                </svg>
                {{ $store.state.language.lock }}
              </button>
            </span>
          </div>
        </div>
      </div>

      <div class="my-3 user-table">
        <div class="row px-2 py-3 m-0 mb-3 bg-box" v-for="(user, index) in users" :key="index">
          <div class="col-auto mt-2">
            <div class="form-check">
              <input
                class="form-check-input"
                type="checkbox"
                :value="user.id"
                v-model="checkedId"
                :disabled="user.permission.indexOf('admin_panel_access') === -1 ? false : true"
              />
            </div>
          </div>
          <div class="col px-0">
            <div class="row m-0">
              <div class="col-auto mt-1">
                <img
                  width="36"
                  height="36"
                  :src="$store.state.config.resource + (user.avatar ? user.avatar.small : '/img/avatars/default.jpg')"
                />
              </div>
              <div class="col-auto" :title="user.permissionView">
                <span class="badge mt-2" :class="user.permission.indexOf('admin_panel_access') !== -1 ? 'bg-danger' : 'bg-primary'">{{
                  user.role
                }}</span>
              </div>
              <div class="col-12 col-sm text-nowrap">
                <router-link :to="'/profile/' + user.id">{{ user.name + (user.role === "anonim" ? "-" + user.puid : "") }}</router-link>
                <div class="text-pale fs-small">
                  {{ user.ip }}
                </div>
              </div>
              <div class="col-12 col-md">
                <div class="fs-small">{{ $store.state.language.comments }}: {{ user.commentsCount }}</div>
                <div class="fs-small">{{ $store.state.language.registration }}: {{ user.date_create }}</div>
              </div>
            </div>
          </div>
          <div class="col-auto">
            <div class="btn-group mt-2">
              <span class="actionsMenu" data-bs-toggle="dropdown"></span>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                <li>
                  <router-link class="dropdown-item" :to="'/profile/' + user.id">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-person-bounding-box"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"
                      />
                      <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                    </svg>
                    {{ $store.state.language.profile }}
                  </router-link>
                </li>
                <li>
                  <button
                    v-if="user.banned == 1 && currentUser.permission.indexOf('manage_users') !== -1"
                    class="dropdown-item"
                    type="button"
                    @click="unban(user.id)"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-check-lg"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M13.485 1.431a1.473 1.473 0 0 1 2.104 2.062l-7.84 9.801a1.473 1.473 0 0 1-2.12.04L.431 8.138a1.473 1.473 0 0 1 2.084-2.083l4.111 4.112 6.82-8.69a.486.486 0 0 1 .04-.045z"
                      />
                    </svg>
                    {{ $store.state.language.unlock }}
                  </button>
                  <button
                    v-if="
                      user.banned == 0 &&
                      user.permission.indexOf('admin_panel_access') === -1 &&
                      currentUser.permission.indexOf('manage_users') !== -1
                    "
                    class="dropdown-item"
                    type="button"
                    @click="modalToggle('banModal', user.id)"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-person-bounding-box"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM8 2c1.3 0 2.5 0.4 3.5 1.1l-8.4 8.4c-0.7-1-1.1-2.2-1.1-3.5 0-3.3 2.7-6 6-6zM8 14c-1.3 0-2.5-0.4-3.5-1.1l8.4-8.4c0.7 1 1.1 2.2 1.1 3.5 0 3.3-2.7 6-6 6z"
                      ></path>
                    </svg>
                    {{ $store.state.language.lock }}
                  </button>
                </li>
                <li>
                  <button
                    v-if="
                      user.deleted == 0 &&
                      user.permission.indexOf('admin_panel_access') === -1 &&
                      currentUser.permission.indexOf('manage_users') !== -1
                    "
                    class="dropdown-item"
                    type="button"
                    @click="modalToggle('deleteModal', user.id)"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-trash"
                      viewBox="0 0 16 16"
                    >
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
                  <button
                    v-if="user.deleted == 1 && currentUser.permission.indexOf('manage_users') !== -1"
                    class="dropdown-item"
                    type="button"
                    @click="modalToggle('recoverModal', user.id)"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-arrow-counterclockwise"
                      viewBox="0 0 16 16"
                    >
                      <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z" />
                      <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z" />
                    </svg>
                    {{ $store.state.language.recover }}
                  </button>
                </li>
                <li
                  v-if="
                    user.deleted == 1 &&
                    user.permission.indexOf('admin_panel_access') === -1 &&
                    currentUser.permission.indexOf('remove_user') !== -1
                  "
                >
                  <button class="dropdown-item" type="button" @click="modalToggle('removeModal', user.id)">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-trash2"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M14 3a.702.702 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671L2.037 3.225A.703.703 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2zM3.215 4.207l1.493 8.957a1 1 0 0 0 .986.836h4.612a1 1 0 0 0 .986-.836l1.493-8.957C11.69 4.689 9.954 5 8 5c-1.954 0-3.69-.311-4.785-.793z"
                      />
                    </svg>
                    {{ $store.state.language.delete_from_database }}
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <pagination
      class="my-3"
      :countItems="countUsers"
      :limitItems="limitUsers"
      pageKey="p"
      countButtons="8"
      @click="getUsers($event)"
    ></pagination>

    <modal-confirm
      v-if="deleteModal"
      :title="$store.state.language.delete_users"
      :body="$store.state.language.confirm_delete_users"
      @click="actionConfirm('delete', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="recoverModal"
      :title="$store.state.language.recover_users"
      :body="$store.state.language.confirm_recover_users"
      @click="actionConfirm('recover', $event)"
    ></modal-confirm>

    <modal-ban v-if="banModal" :checkedId="checkedId" @click="actionConfirm('ban', $event)"></modal-ban>

    <div v-if="removeModal" class="modal fade show">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title">{{ $store.state.language.delete_users }}</h5>
            <button type="button" class="btn-close btn-close-white" @click="modalToggle('removeModal')"></button>
          </div>
          <div class="modal-body">
            <span class="fw-bold">{{ $store.state.language.what_should_users_comments }}</span>
            <form>
              <div class="form-check">
                <input class="form-check-input" type="radio" value="leave" v-model="removeComments" id="removeParam1" />
                <label class="form-check-label" for="removeParam1"> {{ $store.state.language.retain }} </label>
              </div>
              <div class="form-check" v-if="currentUser.permission.indexOf('remove_comment') !== -1">
                <input class="form-check-input" type="radio" value="remove" v-model="removeComments" id="removeParam3" />
                <label class="form-check-label" for="removeParam3"> {{ $store.state.language.delete_from_database }} </label>
              </div>
            </form>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-dark-blue shadow-sm" @click="removeUsers">
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
              {{ $store.state.language.confirm }}
            </button>
            <button type="button" class="btn btn-outline-light shadow-sm" @click="modalToggle('removeModal')">
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
  </div>
</template>

<script>
export default {
  name: "users",
  data() {
    return {
      roles: [],
      countUsers: 0,
      users: [],
      checkedId: [],
      deleteModal: false,
      removeModal: false,
      recoverModal: false,
      banModal: false,
      removeComments: "leave",
      filters: {
        role: localStorage.getItem("filters-role-users") === "true" ? true : false,
        banned: localStorage.getItem("filters-banned-users") === "true" ? true : false,
        deleted: localStorage.getItem("filters-deleted-users") === "true" ? true : false,
        email: localStorage.getItem("filters-email-users") || "",
        ip: localStorage.getItem("filters-ip-users") || "",
      },
    };
  },
  computed: {
    currentUser() {
      return this.$store.state.user;
    },
    changeSelector: {
      get() {
        return this.checkedId.length >= this.users.length && this.users.length !== 0;
      },
      set(val) {
        this.checkedId = val
          ? this.users.map((n) => {
              if (n.permission.indexOf("admin_panel_access") === -1) {
                return n.id;
              }
            })
          : [];
      },
    },
    getFilters() {
      let filters = {};

      for (const key in this.filters) {
        if (Object.hasOwnProperty.call(this.filters, key)) {
          const element = this.filters[key];
          this.$set(filters, key, element);
        }
      }

      if (!filters.banned && !filters.deleted) {
        filters.banned = 0;
        filters.deleted = 0;
      }

      if (!filters.email) {
        filters.email = false;
      }
      if (!filters.ip) {
        filters.ip = false;
      }

      return filters;
    },
    limitUsers() {
      return JSON.parse(localStorage.getItem("limitUsers")) || 50;
    },
  },
  created() {
    this.getRoles();
    this.getUsers();
  },
  methods: {
    actionConfirm(action, confirm) {
      if (confirm) {
        if (action === "delete") {
          this.deleteUsers();
        }
        if (action === "recover") {
          this.recoverUsers();
        }
        if (action === "ban") {
          this.banModal = false;
          this.checkedId = [];
          this.getUsers();
        }
      } else {
        if (action === "delete") {
          this.deleteModal = false;
        }
        if (action === "recover") {
          this.recoverModal = false;
        }
        if (action === "ban") {
          this.banModal = false;
        }
      }
    },
    getRoles() {
      axios.post(this.$store.state.apiPath, { action: "getRoles" }).then((response) => {
        this.roles = response.data;
      });
    },
    getUsers(listId = 0) {
      axios
        .post(this.$store.state.apiPath, { action: "getUsers", limit: this.limitUsers, listId, filters: this.getFilters })
        .then((response) => {
          this.users = response.data;
          this.checkedId = [];
          this.getCountUsers();
        });
    },
    getCountUsers() {
      axios.post(this.$store.state.apiPath, { action: "getCountUsers", filters: this.getFilters }).then((response) => {
        this.countUsers = Number(response.data);
      });
    },
    deleteUsers() {
      axios
        .post(this.$store.state.apiPath, { action: "deleteUsers", listId: this.checkedId, removeComments: "unposted" })
        .then((response) => {
          if (response.data === true) {
            this.deleteModal = false;
            this.getUsers();
          }
        });
    },
    removeUsers() {
      axios
        .post(this.$store.state.apiPath, { action: "removeUsers", listId: this.checkedId, removeComments: this.removeComments })
        .then((response) => {
          if (response.data === true) {
            this.removeModal = false;
            this.getUsers();
          }
        });
    },
    recoverUsers() {
      axios.post(this.$store.state.apiPath, { action: "recoverUsers", listId: this.checkedId }).then((response) => {
        if (response.data === true) {
          this.recoverModal = false;
          this.getUsers();
        }
      });
    },
    unban(id = 0) {
      if (id > 0 && Object.values(this.checkedId).indexOf(id) === -1) {
        this.checkedId.push(id);
      }
      if (this.checkedId.length === 0) {
        return false;
      }

      axios.post(this.$store.state.apiPath, { action: "unbanUser", idList: this.checkedId }).then((response) => {
        if (response.data === true) {
          this.checkedId = [];
          this.getUsers();
        }
      });
    },
    modalToggle(prop, id = 0) {
      if (id > 0 && Object.values(this.checkedId).indexOf(id) === -1) {
        this.checkedId.push(id);
      }

      if (!this[prop]) {
        this[prop] = true;
      } else {
        this[prop] = false;
      }
    },
  },
  watch: {
    "filters.role": function (val) {
      localStorage.setItem("filters-role-users", val);
      this.getUsers();
    },
    "filters.banned": function (val) {
      localStorage.setItem("filters-banned-users", val);
      this.getUsers();
    },
    "filters.deleted": function (val) {
      localStorage.setItem("filters-deleted-users", val);
      this.getUsers();
    },
    "filters.email": function (val) {
      localStorage.setItem("filters-email-users", val);
    },
    "filters.ip": function (val) {
      localStorage.setItem("filters-ip-users", val);
    },
    "banParams.countDays": function (val, old) {
      if (parseFloat(this.banParams.countDays)) {
        this.validate(this.banValidate.countDays);
      }
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .user-table {
    border-color: transparentize($borderPale, 0.8) /*#485769*/;
    color: $light;
    tbody tr:hover {
      color: #fff;
    }
    .actionsMenu {
      cursor: pointer;
      transform: rotate(90deg);
      width: 20px;
      height: 20px;
      &::after {
        content: "...";
        font-size: 24px;
        line-height: 0;
        position: absolute;
        top: 2px;
        left: 2px;
      }
    }
  }
  .modal-body {
    .is-invalid {
      border-color: #dc3545 !important;
    }
  }
}
</style>