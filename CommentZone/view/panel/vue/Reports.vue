<template>
  <div class="p-0 ms-1">
    <comment
      v-if="Object.keys(comment).length > 0"
      :comment="comment"
      @updateText="comment.text = $event"
      @updateAttach="comment.attach = $event"
      @updatePosted="comment.posted = $event"
      @updateModeration="comment.moderation = $event"
      @updateNewComment="comment.new = $event"
    ></comment>

    <div class="text-pale fs-4 border-bottom border-pale pb-2 my-4">{{ $store.state.language.reports }}: {{ count }}</div>

    <div v-if="Object.keys(reports).length > 0">
      <button
        v-if="currentUser.permission.indexOf('manage_comments') !== -1"
        class="btn btn-dark-blue shadow-sm btn-sm"
        type="button"
        @click="readAll()"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark-check" viewBox="0 0 16 16">
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
        v-if="currentUser.permission.indexOf('manage_comments') !== -1"
        class="btn btn-dark-blue shadow-sm btn-sm"
        type="button"
        @click="deleteReports(comment.id)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eraser" viewBox="0 0 16 16">
          <path
            d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828l6.879-6.879zm2.121.707a1 1 0 0 0-1.414 0L4.16 7.547l5.293 5.293 4.633-4.633a1 1 0 0 0 0-1.414l-3.879-3.879zM8.746 13.547 3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293l.16-.16z"
          />
        </svg>
        {{ $store.state.language.clear_reports }}
      </button>
    </div>

    <div v-for="(report, key) in reports" :key="key" class="card p-2 mt-4 border-0 shadow-sm bg-box">
      <div class="card-header bg-transparent border-0">
        <div class="row">
          <div class="col-auto">
            <img
              width="50"
              height="50"
              :src="$store.state.config.resource + (report.authorAvatar ? report.authorAvatar.small : '/img/avatars/default.jpg')"
            />
          </div>
          <div class="col-auto p-0">
            <div class="row">
              <div class="col-auto fw-bold">
                <span
                  ><router-link :to="'/profile/' + report.authorId">{{
                    report.authorName + (report.authorRole === "anonim" ? "-" + report.authorPuid : "")
                  }}</router-link></span
                >
              </div>
              <div class="col-auto text-pale">
                <span :title="report.datePublished.title">{{ report.datePublished.view }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body">
        <p class="card-text text-white text-view" v-html="report.text"></p>
      </div>

      <div class="card-footer bg-transparent border-0">
        <button
          type="button"
          class="btn btn-success text-white shadow-sm btn-sm"
          v-if="currentUser.permission.indexOf('manage_comments') !== -1 && report.new === 1"
          @click="read(report.id)"
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
          class="btn btn-dark-blue shadow-sm btn-sm"
          v-if="currentUser.permission.indexOf('manage_comments') !== -1"
          @click="deleteReport(report.id)"
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
      </div>
    </div>

    <div v-if="Object.keys(reports).length > 0" class="col-auto">
      <pagination
        class="my-1"
        :countItems="count"
        :limitItems="limit"
        pageKey="p"
        countButtons="8"
        @click="getReports($event)"
      ></pagination>
    </div>
  </div>
</template>

<script>
export default {
  name: "Reports",
  data() {
    return {
      comment: [],
      count: 0,
      reports: [],
      indexes: [],
    };
  },
  computed: {
    limit() {
      return JSON.parse(localStorage.getItem("limitCommentsInPanel")) || 50;
    },
    idComment() {
      return this.$route.params.id;
    },
    currentUser() {
      return this.$store.state.user;
    },
  },
  created() {
    this.getCount();
    this.getComment();
    this.getReports();
  },
  methods: {
    readAll() {
      axios
        .post(this.$store.state.apiPath, {
          action: "readAllReport",
          cid: this.comment.id
        })
        .then((response) => {
          if (response.data) {
            for (const key in this.reports) {
              if (Object.hasOwnProperty.call(this.reports, key)) {
                const element = this.reports[key];
                element.new = 0;
              }
            }
          }
        });
    },
    read(id) {
      axios
        .post(this.$store.state.apiPath, {
          action: "readReport",
          id,
        })
        .then((response) => {
          if (response.data) {
            this.reports[this.indexes[id]].new = 0;
          }
        });
    },
    deleteReport(id) {
      axios
        .post(this.$store.state.apiPath, {
          action: "deleteReport",
          id,
        })
        .then((response) => {
          if (response.data) {
            this.$delete(this.reports, this.indexes[id]);
            this.count--;
          }
        });
    },
    deleteReports(cid) {
      axios
        .post(this.$store.state.apiPath, {
          action: "deleteReports",
          cid,
        })
        .then((response) => {
          if (response.data) {
            this.reports = [];
            this.count = 0;
          }
        });
    },
    getComment() {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCommentById",
          id: this.idComment,
        })
        .then((response) => {
          this.comment = response.data;
        });
    },
    getReports(listId = 0) {
      axios
        .post(this.$store.state.apiPath, {
          action: "getReports",
          idComment: this.idComment,
          limit: this.limit,
          listId: listId,
        })
        .then((response) => {
          this.reports = response.data.reports;
          this.indexes = response.data.indexes;
        });
    },
    getCount() {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCountReports",
          cid: this.idComment,
        })
        .then((response) => {
          this.count = response.data;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
</style>