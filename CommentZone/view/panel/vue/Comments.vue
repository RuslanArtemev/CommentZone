<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 border-bottom border-pale pb-2">{{ $store.state.language.comments }}: {{ count }}</div>

    <div class="row my-4">
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.page }}</span>
          <input type="text me-1" class="form-control" @change="actionFilter()" @keyup.enter="actionFilter()" v-model="filters.page" />
        </div>
      </div>
      <div class="col-sm my-1">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.bind_id }}</span>
          <input type="text me-1" class="form-control" @change="actionFilter()" @keyup.enter="actionFilter()" v-model="filters.bindId" />
        </div>
      </div>
    </div>

    <div class="row my-4 text-nowrap">
      <div class="col-6 col-sm-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.moderation" id="moderationSwitch" />
          <label class="form-check-label" for="moderationSwitch">{{ $store.state.language.on_moderation }}</label>
        </div>
      </div>
      <div class="col-6 col-sm-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.new" id="newSwitch" />
          <label class="form-check-label" for="newSwitch">{{ $store.state.language.new_ones }}</label>
        </div>
      </div>
      <div class="col-6 col-sm-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.posted" id="postedSwitch" />
          <label class="form-check-label" for="postedSwitch">{{ $store.state.language.deleted_items }}</label>
        </div>
      </div>
      <div class="col-6 col-sm-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.reports" id="reportsSwitch" />
          <label class="form-check-label" for="reportsSwitch">{{ $store.state.language.reports }}</label>
        </div>
      </div>
    </div>

    <div
      class="row m-0 mb-3 p-2 bg-box align-items-center"
      v-if="currentUser.permission && currentUser.permission.indexOf('manage_comments') !== -1"
    >
      <div class="col-auto">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" v-model="changeSelector" />
        </div>
      </div>
      <div class="col-10 overflow-hidden">
        <div v-menuHorizontal class="text-nowrap d-inline-block">
          <button
            type="button"
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            :disabled="checkedId.length > 0 ? false : true"
            v-if="currentUser.permission.indexOf('manage_comments') !== -1 && !filters.posted && !filters.moderation"
            @click="read"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="currentColor"
              class="bi bi-bookmark-check"
              viewBox="0 0 16 16"
            >
              <path
                fill-rule="evenodd"
                d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"
              />
              <path
                d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5V2zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1H4z"
              />
            </svg>
            {{ $store.state.language.read_it }}
          </button>
          <button
            type="button"
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            :disabled="checkedId.length > 0 ? false : true"
            v-if="currentUser.permission.indexOf('manage_comments') !== -1 && filters.posted"
            @click="recoverModal = true"
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
            type="button"
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            :disabled="checkedId.length > 0 ? false : true"
            v-if="currentUser.permission.indexOf('manage_comments') !== -1 && !filters.posted"
            @click="deleteModal = true"
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
            type="button"
            class="btn btn-dark-blue shadow-sm btn-sm my-1"
            :disabled="checkedId.length > 0 ? false : true"
            v-if="$store.state.config.delete_method === 'unposted' && currentUser.permission.indexOf('remove_comment') !== -1"
            @click="removeModal = true"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash2" viewBox="0 0 16 16">
              <path
                d="M14 3a.702.702 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671L2.037 3.225A.703.703 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2zM3.215 4.207l1.493 8.957a1 1 0 0 0 .986.836h4.612a1 1 0 0 0 .986-.836l1.493-8.957C11.69 4.689 9.954 5 8 5c-1.954 0-3.69-.311-4.785-.793z"
              />
            </svg>
            {{ $store.state.language.delete_from_database }}
          </button>
        </div>
      </div>
    </div>

    <comment
      v-for="(comment, key) in comments"
      :key="key"
      :comment="comment"
      :checked="checkedId.indexOf(comment.id) !== -1 ? true : false"
      @checked="checkedHandle(comment.id, $event)"
      @updateTextOrigin="comment.textOrigin = $event"
      @updateText="comment.text = $event"
      @updateAttach="comment.attach = $event"
      @updatePosted="comment.posted = $event"
      @updateModeration="comment.moderation = $event"
      @updateNewComment="comment.new = $event"
    ></comment>

    <div v-if="count === 0" class="mt-4 fs-3 text-center text-pale">{{ $store.state.language.no_comments }}</div>

    <div v-if="comments.length > 0" class="col-auto">
      <pagination
        class="my-1"
        :countItems="count"
        :limitItems="limit"
        pageKey="p"
        countButtons="8"
        @click="getComments($event)"
      ></pagination>
    </div>

    <modal-confirm
      v-if="deleteModal"
      :title="$store.state.language.delete_comments"
      :body="$store.state.language.confirm_delete_comments"
      @click="ActionConfirm('delete', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="removeModal"
      :title="$store.state.language.delete_comments"
      :body="$store.state.language.comments_completely_removed"
      @click="ActionConfirm('remove', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="recoverModal"
      :title="$store.state.language.recover_comments"
      :body="$store.state.language.confirm_recover_comments"
      @click="ActionConfirm('recover', $event)"
    ></modal-confirm>
  </div>
</template>

<script>
export default {
  name: "Comments",
  data() {
    return {
      count: 0,
      comments: [],
      roles: [],
      checkedId: [],
      deleteModal: false,
      removeModal: false,
      recoverModal: false,
      filters: {
        page: localStorage.getItem("filters-page-home") || "",
        bindId: localStorage.getItem("filters-bindId-home") || "",
        moderation: localStorage.getItem("filters-moderation-home") === "true" ? true : false,
        posted: localStorage.getItem("filters-posted-home") === "true" ? true : false,
        new: localStorage.getItem("filters-new-home") === "true" ? true : false,
        reports: localStorage.getItem("filters-reports-home") === "true" ? true : false,
      },
    };
  },
  computed: {
    currentUser() {
      return this.$store.state.user;
    },
    limit() {
      return JSON.parse(localStorage.getItem("limitCommentsInPanel")) || 50;
    },
    changeSelector: {
      get() {
        return this.checkedId.length >= this.comments.length && this.comments.length !== 0;
      },
      set(val) {
        this.checkedId = val
          ? this.comments.map((n) => {
              return n.id;
            })
          : [];
      },
    },
  },
  created() {
    this.getComments();
    this.getCount();
  },
  methods: {
    ActionConfirm(action, val) {
      if (val) {
        if (action === "delete") {
          this.delete();
        }
        if (action === "remove") {
          this.remove();
        }
        if (action === "recover") {
          this.recover();
        }
      } else {
        if (action === "delete") {
          this.deleteModal = false;
        }
        if (action === "remove") {
          this.removeModal = false;
        }
        if (action === "recover") {
          this.recoverModal = false;
        }
      }
    },
    checkedHandle(id, event) {
      if (event) {
        this.checkedId.push(id);
      } else {
        this.checkedId.splice(this.checkedId.indexOf(id), 1);
      }
    },

    recover() {
      if (!this.checkedId.length === 0) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "recoverSelectedComments",
          ids: this.checkedId,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.getComments();
            this.getCount();

            this.checkedId = [];
            this.recoverModal = false;
          }
        });
    },

    read() {
      if (!this.checkedId.length === 0) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "readSelectedComments",
          ids: this.checkedId,
        })
        .then((response) => {
          if (response.data) {
            this.getComments();

            this.checkedId = [];
          }
        });
    },
    delete() {
      if (!this.checkedId.length === 0) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "deleteSelectedComments",
          ids: this.checkedId,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.getComments();
            this.getCount();

            this.checkedId = [];
            this.deleteModal = false;
          }
        });
    },
    remove() {
      if (!this.checkedId.length === 0) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "removeSelectedComments",
          ids: this.checkedId,
        })
        .then((response) => {
          if (response.data.success === true) {
            this.getComments();
            this.getCount();

            this.checkedId = [];
            this.removeModal = false;
          }
        });
    },
    getCount() {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCountCommentsPanel",
          page: this.filters.page ? this.filters.page : false,
          bindId: this.filters.bindId ? this.filters.bindId : false,
          posted: this.filters.posted ? false : true,
          moderation: this.filters.moderation,
          new: this.filters.new,
          reports: this.filters.reports,
        })
        .then((response) => {
          this.count = Number(response.data);
        });
    },
    getComments(listId = 0) {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCommentsPanel",
          limit: this.limit,
          listId: listId,
          page: this.filters.page ? this.filters.page : false,
          bindId: this.filters.bindId ? this.filters.bindId : false,
          posted: this.filters.posted ? false : true,
          moderation: this.filters.moderation,
          new: this.filters.new,
          reports: this.filters.reports,
        })
        .then((response) => {
          if (typeof response.data === "object") {
            this.comments = response.data;
            this.checkedId = [];
          }
        });
    },
    actionFilter() {
      this.getComments();
      this.getCount();
    },
  },
  watch: {
    "filters.page": function (val) {
      localStorage.setItem("filters-page-home", val);
    },
    "filters.bindId": function (val) {
      localStorage.setItem("filters-bindId-home", val);
    },
    "filters.moderation": function (val) {
      localStorage.setItem("filters-moderation-home", val);
      this.getComments();
      this.getCount();
    },
    "filters.posted": function (val) {
      localStorage.setItem("filters-posted-home", val);
      this.getComments();
      this.getCount();
    },
    "filters.new": function (val) {
      localStorage.setItem("filters-new-home", val);
      this.getComments();
      this.getCount();
    },
    "filters.reports": function (val) {
      localStorage.setItem("filters-reports-home", val);
      this.getComments();
      this.getCount();
    },
  },
};
</script>

<style lang="scss" scoped>
</style>