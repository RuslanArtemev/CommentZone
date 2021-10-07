<template>
  <div v-if="currentUser.id && user.id">
    <div v-if="currentUser.permission.indexOf('manage_profile') !== -1" style="position: relative">
      <router-link style="position: absolute; right: 0" class="btn btn-dark-blue shadow-sm btn-sm mb-4" :to="user.id + '/edit'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
          <path
            d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"
          />
        </svg>
        {{ $store.state.language.edit }}
      </router-link>
    </div>
    <div class="row">
      <div class="col-auto mx-auto mx-md-0">
        <div class="avatar-profile row m-0 align-items-center">
          <div class="col p-0"></div>
          <div class="col-auto p-0" v-if="user.avatar === null">
            <img :src="$store.state.config.resource + '/img/avatars/default.jpg'" width="100%" alt="" />
          </div>
          <div class="col-auto p-0" v-else>
            <img v-if="user.avatar.long" :src="$store.state.config.resource + user.avatar.long" width="100%" alt="" />
            <img v-else-if="user.avatar.middle" :src="$store.state.config.resource + user.avatar.middle" alt="" />
            <img v-else :src="$store.state.config.resource + user.avatar.small" alt="" />
          </div>
          <div class="col p-0"></div>
        </div>
        <div class="mt-3 text-center">
          <button
            v-if="
              user.deleted == 0 &&
              currentUser.permission.indexOf('manage_users') !== -1 &&
              user.permission.indexOf('admin_panel_access') === -1
            "
            class="btn btn-dark-blue shadow-sm btn-sm"
            type="button"
            @click="deleteUserModal = true"
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
            v-if="user.deleted == 1 && currentUser.permission.indexOf('manage_users') !== -1"
            class="btn btn-dark-blue shadow-sm btn-sm"
            type="button"
            @click="recoverUserModal = true"
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
            v-if="
              user.deleted == 1 &&
              currentUser.permission.indexOf('remove_user') !== -1 &&
              user.permission.indexOf('admin_panel_access') === -1
            "
            class="btn btn-dark-blue shadow-sm btn-sm"
            type="button"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash2" viewBox="0 0 16 16">
              <path
                d="M14 3a.702.702 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671L2.037 3.225A.703.703 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2zM3.215 4.207l1.493 8.957a1 1 0 0 0 .986.836h4.612a1 1 0 0 0 .986-.836l1.493-8.957C11.69 4.689 9.954 5 8 5c-1.954 0-3.69-.311-4.785-.793z"
              />
            </svg>
            {{ $store.state.language.delete_from_database }}
          </button>
          <button
            v-if="
              user.deleted == 0 &&
              user.banned == 0 &&
              currentUser.permission.indexOf('manage_users') !== -1 &&
              user.permission.indexOf('admin_panel_access') === -1
            "
            class="btn btn-dark-blue shadow-sm btn-sm"
            type="button"
            @click="banModal = true"
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
          <button
            v-if="user.banned == 1 && currentUser.permission.indexOf('manage_users') !== -1"
            class="btn btn-dark-blue shadow-sm btn-sm"
            type="button"
            @click="unban()"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
              <path
                d="M13.485 1.431a1.473 1.473 0 0 1 2.104 2.062l-7.84 9.801a1.473 1.473 0 0 1-2.12.04L.431 8.138a1.473 1.473 0 0 1 2.084-2.083l4.111 4.112 6.82-8.69a.486.486 0 0 1 .04-.045z"
              />
            </svg>
            {{ $store.state.language.unlock }}
          </button>
        </div>
      </div>

      <div class="col-auto mt-4 mt-md-0 text-nowrap">
        <h2>
          <span>{{ user.name }}</span
          ><span v-if="user.role === 'anonim'">-{{ user.puid }}</span>
        </h2>

        <div class="row">
          <div class="col-6 text-pale fw-bold">ID:</div>
          <div class="col-6 text-light">{{ user.id }}</div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.role }}:</div>
          <div class="col-6 text-light">
            <span
              class="badge"
              :class="user.permission && user.permission.indexOf('admin_panel_access') !== -1 ? 'bg-danger' : 'bg-primary'"
              >{{ user.role }}</span
            >
          </div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.count_comments }}:</div>
          <div class="col-6 text-light">{{ user.commentsCount }}</div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.registration_date }}:</div>
          <div class="col-6 text-light">{{ user.datePublished.title }}</div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.public_id }}:</div>
          <div class="col-6 text-light">{{ user.puid }}</div>
        </div>
        <!-- <div class="row">
          <div class="col-3 text-pale fw-bold">{{ $store.state.language.ip }}:</div>
          <div class="col-9 text-light">{{ user.ip }}</div>
        </div> -->
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.email }}:</div>
          <div class="col-6 text-light">{{ user.email || $store.state.language.no }}</div>
        </div>
        <!-- <div class="row">
          <div class="col-3 text-pale fw-bold">{{ $store.state.language.session_id }}:</div>
          <div class="col-9 text-light">{{ user.session_id }}</div>
        </div> -->
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.count_reports }}:</div>
          <div class="col-6" :class="user.reportsCount === 0 ? 'text-light' : 'text-warning'">{{ user.reportsCount }}</div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.ban }}:</div>
          <div class="col-6" :class="user.banned === 0 ? 'text-light' : 'text-danger'">
            {{ user.banned === 0 ? $store.state.language.no : $store.state.language.yes }}
          </div>
        </div>
        <div class="row" v-if="user.banned === 1">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.ban_period }}:</div>
          <div class="col-6 text-light">{{ user.ban_datetime === null ? $store.state.language.permanent : user.ban_datetime_format }}</div>
        </div>
        <div class="row">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.count_bans }}:</div>
          <div class="col-6 text-light">{{ user.ban_count }}</div>
        </div>
        <div class="row" v-if="user.ban_count > 0">
          <div class="col-6 text-pale fw-bold">{{ $store.state.language.note_ban }}:</div>
          <div class="col-6 text-light">{{ user.ban_note }}</div>
        </div>
      </div>
    </div>

    <div class="card bg-box my-4 p-4" v-if="user.signin.length > 0">
      <div class="text-light fw-bold mb-3">{{ $store.state.language.logins_params }}</div>

      <div v-for="(signin, index) in user.signin" :key="'sig' + index">
        <div class="mb-4" :class="user.signin.length > 1 && index > 0 && !loginsShow ? 'd-none' : ''">
          <div class="row">
            <div class="col-3 col-md-2 text-pale fw-bold text-nowrap">{{ $store.state.language.ip }}:</div>
            <div class="col-9 col-md-10 text-light">{{ signin.ip }}</div>
          </div>
          <div class="row">
            <div class="col-3 col-md-2 text-pale fw-bold text-nowrap">{{ $store.state.language.user_agent }}:</div>
            <div class="col-9 col-md-10 text-light">{{ signin.agent }}</div>
          </div>
          <div class="row">
            <div class="col-3 col-md-2 text-pale fw-bold text-nowrap">{{ $store.state.language.session_id }}:</div>
            <div class="col-9 col-md-10 text-light">{{ signin.session_id }}</div>
          </div>
          <div class="row">
            <div class="col-3 col-md-2 text-pale fw-bold text-nowrap">{{ $store.state.language.date }}:</div>
            <div class="col-9 col-md-10 text-light" :title="signin.date_update.title">{{ signin.date_update.title }} - {{ signin.date_update.view }}</div>
          </div>
        </div>

        <div
          class="d-inline-block mt-2 pointer text-blue fw-bold"
          v-if="user.signin.length > 1 && index === user.signin.length - 1"
          @click="loginsShow = loginsShow ? false : true"
        >
          <span v-if="!loginsShow">{{ $store.state.language.show_more }}</span>
          <span v-else>{{ $store.state.language.show_less }}</span>
        </div>
      </div>
    </div>

    <div class="text-pale fs-4 my-4 pb-2 border-bottom border-pale">{{ $store.state.language.comments }}: {{ count }}</div>

    <div class="row my-4">
      <div class="col">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.page }}</span>
          <input type="text me-1" class="form-control" @change="actionFilter()" @keyup.enter="actionFilter()" v-model="filters.page" />
        </div>
      </div>
      <div class="col">
        <div class="input-group">
          <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.bind_id }}</span>
          <input type="text me-1" class="form-control" @change="actionFilter()" @keyup.enter="actionFilter()" v-model="filters.bindId" />
        </div>
      </div>
    </div>

    <div class="row my-4">
      <div class="col-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.moderation" id="moderationSwitch" />
          <label class="form-check-label" for="moderationSwitch">{{ $store.state.language.on_moderation }}</label>
        </div>
      </div>
      <div class="col-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.new" id="newSwitch" />
          <label class="form-check-label" for="newSwitch">{{ $store.state.language.new_ones }}</label>
        </div>
      </div>
      <div class="col-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.posted" id="postedSwitch" />
          <label class="form-check-label" for="postedSwitch">{{ $store.state.language.deleted_items }}</label>
        </div>
      </div>
      <div class="col-auto">
        <div class="form-check mt-1">
          <input class="form-check-input" type="checkbox" v-model="filters.reports" id="reportsSwitch" />
          <label class="form-check-label" for="reportsSwitch">{{ $store.state.language.reports }}</label>
        </div>
      </div>
    </div>

    <div class="row m-0 mb-3 p-2 bg-box" v-if="currentUser.permission && currentUser.permission.indexOf('manage_comments') !== -1">
      <div class="col-auto mt-1">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" v-model="changeSelector" />
        </div>
      </div>
      <div class="col">
        <button
          type="button"
          class="btn btn-dark-blue shadow-sm btn-sm"
          :disabled="checkedId.length > 0 ? false : true"
          v-if="currentUser.permission.indexOf('manage_comments') !== -1 && !filters.moderation"
          @click="readComments()"
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
          :disabled="checkedId.length > 0 ? false : true"
          v-if="currentUser.permission.indexOf('manage_comments') !== -1 && filters.posted"
          @click="recoverCommentsModal = true"
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
          class="btn btn-dark-blue shadow-sm btn-sm"
          :disabled="checkedId.length > 0 ? false : true"
          v-if="currentUser.permission.indexOf('manage_comments') !== -1 && !filters.posted"
          @click="deleteCommentsModal = true"
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
          class="btn btn-dark-blue shadow-sm btn-sm"
          :disabled="checkedId.length > 0 ? false : true"
          v-if="$store.state.config.delete_method === 'unposted' && currentUser.permission.indexOf('remove_comment') !== -1"
          @click="removeCommentsModal = true"
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

    <comment
      v-for="(comment, key) in comments"
      :key="key"
      :comment="comment"
      :checked="checkedId.indexOf(comment.id) !== -1 ? true : false"
      @checked="checkedHandle(comment.id, $event)"
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
      v-if="deleteUserModal"
      :title="$store.state.language.delete_user"
      :body="$store.state.language.confirm_delete_user"
      @click="actionConfirm('deleteUser', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="recoverUserModal"
      :title="$store.state.language.recover_user"
      :body="$store.state.language.confirm_user_recovery"
      @click="actionConfirm('recoverUser', $event)"
    ></modal-confirm>

    <modal-confirm
      v-if="deleteCommentsModal"
      :title="$store.state.language.delete_comments"
      :body="$store.state.language.confirm_delete_comments"
      @click="actionConfirm('deleteComments', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="removeCommentsModal"
      :title="$store.state.language.delete_comments"
      :body="$store.state.language.comments_completely_removed"
      @click="actionConfirm('removeComments', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="recoverCommentsModal"
      :title="$store.state.language.recover_comments"
      :body="$store.state.language.confirm_recover_comments"
      @click="actionConfirm('recoverComments', $event)"
    ></modal-confirm>

    <modal-ban v-if="banModal" :checkedId="[user.id]" @click="actionConfirm('ban', $event)"></modal-ban>
    <!-- <modal-edit-profile v-if="editProfileModal" :user="user" @click="actionConfirm('edit-profile', $event)"></modal-edit-profile> -->
  </div>
</template>

<script>
export default {
  name: "Profile",
  data() {
    return {
      // editProfileModal: false,
      deleteUserModal: false,
      recoverUserModal: false,
      deleteCommentsModal: false,
      removeCommentsModal: false,
      recoverCommentsModal: false,
      banModal: false,
      loginsShow: false,
      user: [],
      comments: [],
      count: "",
      checkedId: [],
      filters: {
        page: localStorage.getItem("filters-page-profile") || "",
        bindId: localStorage.getItem("filters-bindId-profile") || "",
        moderation: localStorage.getItem("filters-moderation-profile") === "true" ? true : false,
        posted: localStorage.getItem("filters-posted-profile") === "true" ? true : false,
        new: localStorage.getItem("filters-new-profile") === "true" ? true : false,
        reports: localStorage.getItem("filters-reports-profile") === "true" ? true : false,
      },
    };
  },
  created() {
    this.getProfile();
  },
  computed: {
    currentUser() {
      return this.$store.state.user;
    },
    limit() {
      return JSON.parse(localStorage.getItem("limitCommentsInPanel")) || 50;
    },
    id() {
      return this.$route.params.id;
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
  methods: {
    getProfile() {
      axios.post(this.$store.state.apiPath, { action: "getProfile", id: this.id }).then((response) => {
        this.user = response.data;
        if (this.user.id) {
          this.getComments();
          this.getCount();
        }
      });
    },
    getComments(listId = 0) {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCommentsUser",
          limit: this.limit,
          listId: listId,
          uid: this.user.id,
          page: this.filters.page ? this.filters.page : false,
          bindId: this.filters.bindId ? this.filters.bindId : false,
          posted: this.filters.posted ? false : true,
          moderation: this.filters.moderation,
          new: this.filters.new,
          reports: this.filters.reports,
        })
        .then((response) => {
          this.comments = response.data;
          this.checkedId = [];
        });
    },
    getCount() {
      axios
        .post(this.$store.state.apiPath, {
          action: "getCountCommentsUser",
          uid: this.user.id,
          page: this.filters.page ? this.filters.page : false,
          bindId: this.filters.bindId ? this.filters.bindId : false,
          posted: this.filters.posted ? false : true,
          moderation: this.filters.moderation,
          new: this.filters.new,
          reports: this.filters.reports,
        })
        .then((response) => {
          this.count = Number(response.data);
          this.checkedId = [];
        });
    },
    deleteUsers() {
      axios
        .post(this.$store.state.apiPath, { action: "deleteUsers", listId: [this.user.id], removeComments: "unposted" })
        .then((response) => {
          if (response.data === true) {
            this.deleteUserModal = false;
            this.getProfile();
          }
        });
    },
    recoverUsers() {
      axios.post(this.$store.state.apiPath, { action: "recoverUsers", listId: [this.user.id] }).then((response) => {
        if (response.data === true) {
          this.recoverUserModal = false;
          this.getProfile();
        }
      });
    },
    unban() {
      axios.post(this.$store.state.apiPath, { action: "unbanUser", idList: [this.user.id] }).then((response) => {
        if (response.data === true) {
          this.banModal = false;
          this.getProfile();
        }
      });
    },
    actionConfirm(action, confirm) {
      if (confirm) {
        // if (action === "edit-profile") {
        //   this.editProfileModal = false;
        //   // this.getProfile();
        // }
        if (action === "deleteUser") {
          this.deleteUsers();
        }
        if (action === "recoverUser") {
          this.recoverUsers();
        }
        if (action === "ban") {
          this.banModal = false;
          this.getProfile();
        }
        if (action === "deleteComments") {
          this.deleteComments();
        }
        if (action === "removeComments") {
          this.removeComments();
        }
        if (action === "recoverComments") {
          this.recoverComments();
        }
      } else {
        // if (action === "edit-profile") {
        //   this.editProfileModal = false;
        // }
        if (action === "deleteUser") {
          this.deleteUserModal = false;
        }
        if (action === "recoverUser") {
          this.recoverUserModal = false;
        }
        if (action === "ban") {
          this.banModal = false;
        }

        if (action === "deleteComments") {
          this.deleteCommentsModal = false;
        }
        if (action === "removeComments") {
          this.removeCommentsModal = false;
        }
        if (action === "recoverComments") {
          this.recoverCommentsModal = false;
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

    recoverComments() {
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
    readComments() {
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
          }
        });
    },
    deleteComments() {
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
    removeComments() {
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
    actionFilter() {
      this.getComments();
      this.getCount();
    },
  },
  watch: {
    id: function () {
      this.getProfile();
    },
    "filters.page": function (val) {
      localStorage.setItem("filters-page-profile", val);
    },
    "filters.bindId": function (val) {
      localStorage.setItem("filters-bindId-profile", val);
    },
    "filters.moderation": function (val) {
      localStorage.setItem("filters-moderation-profile", val);
      this.getComments();
      this.getCount();
    },
    "filters.posted": function (val) {
      localStorage.setItem("filters-posted-profile", val);
      this.getComments();
      this.getCount();
    },
    "filters.new": function (val) {
      localStorage.setItem("filters-new-profile", val);
      this.getComments();
      this.getCount();
    },
    "filters.reports": function (val) {
      localStorage.setItem("filters-reports-profile", val);
      this.getComments();
      this.getCount();
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .bg-profile {
    background-color: $screenComments;
  }
  .profile_params {
    div {
      // padding-top: 1px;
      // padding-bottom: 1px;
      div:nth-child(2) {
        font-size: 0.9em;
        color: $textPale;
      }
      div:first-child {
        font-weight: bold;
        color: $light;
      }
    }
  }
  .user-table {
    color: $light;
    td {
      color: white;
    }
  }
  .avatar-profile {
    width: 200px;
    height: 200px;
    background-color: #202327;
  }
}
</style>