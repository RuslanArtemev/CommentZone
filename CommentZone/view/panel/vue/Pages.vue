<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 border-bottom border-pale pb-2">
      {{ $store.state.language.pages }}: {{ count }}
    </div>

    <div class="row my-4">
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{
            $store.state.language.url
          }}</span>
          <input
            type="text me-1"
            class="form-control"
            v-model="filters.url"
            @change="actionFilter()"
            @keyup.enter="actionFilter()"
          />
        </div>
      </div>
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{
            $store.state.language.bind_id
          }}</span>
          <input
            type="text me-1"
            class="form-control"
            v-model.number="filters.bind_id"
            @change="actionFilter()"
            @keyup.enter="actionFilter()"
          />
        </div>
      </div>
    </div>

    <div
      class="row m-0 p-2 bg-box"
      v-if="
        currentUser.permission &&
        currentUser.permission.indexOf('manage_comments') !== -1
      "
    >
      <div class="col-auto mt-1">
        <div class="form-check">
          <input
            class="form-check-input"
            type="checkbox"
            v-model="changeSelector"
          />
        </div>
      </div>
      <div class="col-10 overflow-hidden">
        <div v-menuHorizontal class="text-nowrap d-inline-block">
          <button
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            type="button"
            :disabled="checkedId.length > 0 ? false : true"
            @click="modalToggle('moveCommentModal')"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-signpost-split"
              viewBox="0 0 16 16"
            >
              <path
                d="M7 7V1.414a1 1 0 0 1 2 0V2h5a1 1 0 0 1 .8.4l.975 1.3a.5.5 0 0 1 0 .6L14.8 5.6a1 1 0 0 1-.8.4H9v10H7v-5H2a1 1 0 0 1-.8-.4L.225 9.3a.5.5 0 0 1 0-.6L1.2 7.4A1 1 0 0 1 2 7h5zm1 3V8H2l-.75 1L2 10h6zm0-5h6l.75-1L14 3H8v2z"
              />
            </svg>
            {{ $store.state.language.move_comments }}
          </button>
          <button
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            type="button"
            :disabled="checkedId.length > 0 ? false : true"
            @click="recountComments()"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-calculator"
              viewBox="0 0 16 16"
            >
              <path
                d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"
              />
              <path
                d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"
              />
            </svg>
            {{ $store.state.language.recount_comments }}
          </button>
          <button
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            type="button"
            :disabled="checkedId.length > 0 ? false : true"
            @click="modalToggle('deleteModal')"
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
        </div>
      </div>
    </div>

    <div class="my-3 pages-table">
      <div
        class="row px-2 py-3 m-0 mb-3 bg-box"
        v-for="(page, index) in pages"
        :key="index"
      >
        <div class="col-auto mt-2">
          <div class="form-check">
            <input
              class="form-check-input"
              type="checkbox"
              :value="page.id"
              v-model="checkedId"
            />
          </div>
        </div>
        <div class="col px-0">
          <div class="row m-0">
            <div class="col text-nowrap">
              <div class="fs-small">
                <span>
                  <a class="text-info" :href="page.url" target="_blank">{{
                    page.title
                  }}</a>
                </span>
              </div>
              <div class="fs-small">
                <span class="text-white">ID : </span>
                <span>{{ page.id }}</span>
              </div>
            </div>
            <div class="col">
              <div class="fs-small">
                <span class="text-white"
                  >{{ $store.state.language.url }} :
                </span>
                <span>{{ page.url }}</span>
              </div>
              <div class="fs-small">
                <span class="text-white"
                  >{{ $store.state.language.bind_id }} :
                </span>
                <span>{{
                  page.bind_id === null || page.bind_id === ""
                    ? "NULL"
                    : page.bind_id
                }}</span>
              </div>
            </div>
            <div class="col-md">
              <div class="fs-small">
                <span class="text-white"
                  >{{ $store.state.language.comments }} :
                </span>
                <span>{{ page.count_main + page.count_answer }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto">
          <div class="btn-group mt-2">
            <span class="actionsMenu" data-bs-toggle="dropdown"></span>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
              <li
                v-if="currentUser.permission.indexOf('manage_profile') !== -1"
              >
                <router-link
                  class="dropdown-item"
                  :to="'/pages/' + page.id + '/edit'"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-pencil"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"
                    />
                  </svg>
                  {{ $store.state.language.edit }}
                </router-link>
              </li>
              <li>
                <button
                  v-if="
                    currentUser.permission.indexOf('admin_panel_access') !== -1
                  "
                  class="dropdown-item"
                  type="button"
                  @click="modalToggle('moveCommentModal', page.id)"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-signpost-split"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M7 7V1.414a1 1 0 0 1 2 0V2h5a1 1 0 0 1 .8.4l.975 1.3a.5.5 0 0 1 0 .6L14.8 5.6a1 1 0 0 1-.8.4H9v10H7v-5H2a1 1 0 0 1-.8-.4L.225 9.3a.5.5 0 0 1 0-.6L1.2 7.4A1 1 0 0 1 2 7h5zm1 3V8H2l-.75 1L2 10h6zm0-5h6l.75-1L14 3H8v2z"
                    />
                  </svg>
                  {{ $store.state.language.move_comments }}
                </button>
              </li>
              <li>
                <button
                  v-if="
                    currentUser.permission.indexOf('admin_panel_access') !== -1
                  "
                  class="dropdown-item"
                  type="button"
                  @click="recountComments(page.id)"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-calculator"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"
                    />
                    <path
                      d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"
                    />
                  </svg>
                  {{ $store.state.language.recount_comments }}
                </button>
              </li>
              <li>
                <button
                  v-if="
                    currentUser.permission.indexOf('admin_panel_access') !== -1
                  "
                  class="dropdown-item"
                  type="button"
                  @click="modalToggle('deleteModal', page.id)"
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
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div v-if="pages.length > 0" class="col-auto">
      <pagination
        class="my-1"
        :countItems="count"
        :limitItems="limit"
        pageKey="p"
        countButtons="8"
        @click="getPages($event)"
      ></pagination>
    </div>

    <modal-confirm
      v-if="deleteModal"
      :title="$store.state.language.delete_page"
      :body="$store.state.language.confirm_delete_page"
      @click="actionConfirm('delete', $event)"
    ></modal-confirm>

    <toast
      v-if="toast.show"
      :bg="toast.bg"
      :message="toast.message"
      @close="$emit ? (toast.show = false) : ''"
    ></toast>

    <loader v-if="loader"></loader>

    <div v-if="moveCommentModal" class="modal fade show">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header border-0">
            <h5 class="modal-title">
              {{ $store.state.language.move_comments }}
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              @click="actionConfirm('moveComments', false)"
            ></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="input_id_page" class="form-label">{{
                $store.state.language.page_id
              }}</label>
              <input
                type="text"
                class="form-control"
                :class="errors.page_id ? 'is-invalid' : ''"
                id="input_id_page"
                @input="errors.page_id = ''"
                v-model.number="moveToPageId"
              />
              <div class="invalid-feedback">
                {{ errors.page_id ? errors.page_id : "" }}
              </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button
              type="button"
              class="btn btn-dark-blue shadow-sm"
              @click="actionConfirm('moveComments', true)"
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
              {{ $store.state.language.confirm }}
            </button>
            <button
              type="button"
              class="btn btn-outline-light shadow-sm"
              @click="actionConfirm('moveComments', false)"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                class="bi bi-x-circle"
                viewBox="0 0 16 16"
              >
                <path
                  d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"
                />
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
import Loader from "./components/Loader.vue";
export default {
  components: { Loader },
  name: "Comments",
  data() {
    return {
      count: 0,
      pages: [],
      checkedId: [],
      moveToPageId: null,
      loader: false,
      filters: {
        url: localStorage.getItem("filters-url-pages") || "",
        bind_id: localStorage.getItem("filters-bindId-pages") || "",
      },
      deleteModal: false,
      moveCommentModal: false,
      toast: {
        show: false,
        bg: "",
        message: "",
      },
      errors: {
        page_id: "",
      },
    };
  },
  computed: {
    limit() {
      return JSON.parse(localStorage.getItem("limitCommentsInPanel")) || 50;
    },
    currentUser() {
      return this.$store.state.user;
    },
    changeSelector: {
      get() {
        return (
          this.checkedId.length >= this.pages.length && this.pages.length !== 0
        );
      },
      set(val) {
        this.checkedId = val
          ? this.pages.map((n) => {
              return n.id;
            })
          : [];
      },
    },
  },
  created() {
    this.getPages();
    this.getPagesCount();
  },
  methods: {
    actionConfirm(action, confirm) {
      if (confirm) {
        if (action === "delete") {
          this.deletePages();
        }
        if (action === "recover") {
          this.recoverPage();
        }
        if (action === "moveComments") {
          this.moveComments();
        }
      } else {
        if (action === "delete") {
          this.deleteModal = false;
        }
        if (action === "recover") {
          this.recoverModal = false;
        }
        if (action === "moveComments") {
          this.moveCommentModal = false;
        }
      }
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
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    getPagesCount() {
      axios
        .post(this.$store.state.apiPath, {
          action: "getPagesCount",
          filters: {
            url: this.filters.url ? this.filters.url : false,
            bind_id: this.filters.bind_id ? this.filters.bind_id : false,
          },
        })
        .then((response) => {
          this.count = Number(response.data);
        });
    },
    getPages(listId = 0) {
      axios
        .post(this.$store.state.apiPath, {
          action: "getPages",
          limit: this.limit,
          listId: listId,
          filters: {
            url: this.filters.url ? this.filters.url : false,
            bind_id: this.filters.bind_id ? this.filters.bind_id : false,
          },
        })
        .then((response) => {
          if (typeof response.data === "object") {
            this.pages = response.data;
          }
        });
    },
    deletePages() {
      this.loader = true;

      axios
        .post(this.$store.state.apiPath, {
          action: "deletePages",
          idsList: this.checkedId,
        })
        .then((response) => {
          this.loader = false;

          if (response.data === true) {
            this.getPages();
            this.getPagesCount();
            this.toastShow(this.$store.state.language.success, "bg-success");
          } else {
            this.toastShow(
              this.$store.state.language.delete_page_failed,
              "bg-danger"
            );
          }
          this.deleteModal = false;
        });
    },
    moveComments() {
      if (!this.moveToPageId) {
        this.errors.page_id = this.$store.state.language.empty_field;
        return false;
      }

      if (typeof this.moveToPageId !== "number") {
        this.errors.page_id = this.$store.state.language.must_be_number;
        return false;
      }

      this.loader = true;

      axios
        .post(this.$store.state.apiPath, {
          action: "moveComments",
          fromIds: this.checkedId,
          toId: this.moveToPageId,
        })
        .then((response) => {
          this.loader = false;

          if (response.data === true) {
            this.getPages();
            this.toastShow(this.$store.state.language.success, "bg-success");
          } else {
            this.toastShow(this.$store.state.language.error, "bg-danger");
          }
          this.actionConfirm("moveComments", false);
        });
    },
    recountComments(id = null) {
      this.loader = true;

      if (id !== null) {
        this.checkedId = [];

        if (Object.values(this.checkedId).indexOf(id) === -1) {
          this.checkedId.push(id);
        }
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "recountComments",
          idsList: this.checkedId,
        })
        .then((response) => {
          this.loader = false;

          if (response.data === true) {
            this.getPages();
            this.toastShow(this.$store.state.language.success, "bg-success");
          } else {
            this.toastShow(this.$store.state.language.error, "bg-danger");
          }
        });
    },
    actionFilter() {
      this.getPages();
      this.getPagesCount();
    },
  },
  watch: {
    "filters.url": function (val) {
      localStorage.setItem("filters-url-pages", val);
    },
    "filters.bind_id": function (val) {
      localStorage.setItem("filters-bindId-pages", val);
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .pages-table {
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
