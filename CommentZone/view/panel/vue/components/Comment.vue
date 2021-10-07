<template>
  <div class="card p-2 mb-4 border-0 shadow-sm bg-box">
    <div class="card-header bg-transparent border-0">
      <div class="row">
        <div class="col-auto mt-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" :checked="checked" @change="$emit('checked', $event.target.checked)" />
          </div>
        </div>
        <div class="col-auto">
          <img
            width="50"
            height="50"
            :src="$store.state.config.resource + (comment.authorAvatar ? comment.authorAvatar.small : '/img/avatars/default.jpg')"
          />
        </div>
        <div class="col-auto p-0">
          <div class="row">
            <div class="col-auto fw-bold">
              <span
                ><router-link :to="'/profile/' + comment.authorId">{{
                  comment.authorName + (comment.authorRole === "anonim" ? "-" + comment.authorPuid : "")
                }}</router-link></span
              >
            </div>
            <div class="col-auto text-pale">
              <span :title="comment.datePublished.title">{{ comment.datePublished.view }}</span>
            </div>
          </div>

          <div class="row">
            <div class="col-auto">
              <a class="text-decoration-none text-pale" :href="comment.pageUrl" target="_blank">{{ comment.pageTitle }}</a>
            </div>
          </div>
        </div>

        <div class="col px-4">
          <div class="row justify-content-end">
            <div v-if="comment.moderation === 1" class="col-auto me-2 p-0 text-danger">{{ $store.state.language.on_moderation }}</div>
            <div v-if="comment.posted === 0" class="col-auto text-primary me-2 p-0">{{ $store.state.language.deleted }}</div>
            <div v-if="comment.moderation === 0 && comment.posted === 1 && comment.new === 1" class="col-auto text-success me-2 p-0">
              {{ $store.state.language.new }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-body ms-5">
      <figure v-if="comment.parentText || comment.parentAuthorName" class="blockquote-box ms-4 px-2 text-end">
        <blockquote class="blockquote text-quote">
          <i v-html="comment.parentText.substr(-comment.parentText.length, 280) + (comment.parentText.length > 280 ? '...' : '')"></i>
        </blockquote>
        <figcaption class="blockquote-footer text-pale">{{ comment.parentAuthorName }}</figcaption>
      </figure>

      <div v-if="!EditShow(comment.prefixId)" v-blockSize>
        <p class="card-text text-white text-view" v-html="comment.text"></p>

        <div v-if="comment.attach && comment.attach.length > 0" class="row my-2 mx-1">
          <div
            v-for="(image, key) in comment.attach"
            :key="key"
            class="col-auto p-1"
            :class="image && image.type === 'video' ? 'video-play' : ''"
            @click="imagePreview(comment.id, key)"
          >
            <div
              class="video-play-icon"
              :style="'background: url(' + $store.state.config.resource + '/img/icons/play_video.png) center / cover no-repeat;'"
            ></div>
            <img
              v-if="image"
              height="100"
              :src="
                image.type === 'image'
                  ? $store.state.config.resource + image.middle
                  : (image.resource === 'tiktok' ? $store.state.config.resource : '') + image.preview
              "
              alt=""
            />
          </div>
        </div>
      </div>

      <comment-input
        v-if="EditShow(comment.prefixId)"
        :id="comment.prefixId + '-edit'"
        :comment="comment"
        action="edit"
        v-model="editElements"
      ></comment-input>
    </div>

    <comment-input
      v-if="InputShow(comment.prefixId)"
      :id="comment.prefixId + '-answer'"
      :comment="comment"
      action="answer"
      v-model="writeComment"
    ></comment-input>

    <div class="card-footer bg-transparent border-0 ms-5">
      <router-link
        class="btn shadow-sm btn-sm text-dark my-1"
        :class="comment.newReportsCount > 0 ? 'btn-warning' : 'btn-dark-blue'"
        v-if="currentUser.permission.indexOf('manage_comments') !== -1 && comment.newReportsCount > 0"
        :to="'/reports/' + comment.id"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          fill="currentColor"
          class="bi bi-exclamation-octagon"
          viewBox="0 0 16 16"
        >
          <path
            d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z"
          />
          <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
        </svg>
        <span>{{ $store.state.language.reports }}</span>
        <span v-if="comment.newReportsCount > 0" class="badge bg-secondary">{{ comment.newReportsCount }}</span>
      </router-link>
      <button
        type="button"
        class="btn btn-danger text-white shadow-sm btn-sm my-1"
        v-if="currentUser.permission.indexOf('manage_comments') !== -1 && comment.moderation === 1"
        @click="approve"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16">
          <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
          <path
            d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"
          />
        </svg>
        {{ $store.state.language.approve }}
      </button>
      <button
        type="button"
        class="btn btn-primary text-white shadow-sm btn-sm my-1"
        v-if="currentUser.permission.indexOf('manage_comments') !== -1 && comment.posted === 0"
        @click="recoverConfirm"
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
        class="btn btn-success text-white shadow-sm btn-sm my-1"
        v-if="
          currentUser.permission.indexOf('manage_comments') !== -1 && comment.new === 1 && comment.posted === 1 && comment.moderation === 0
        "
        @click="read"
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
      <router-link class="btn btn-dark-blue shadow-sm btn-sm my-1" :to="'/chain/' + (comment.mid === 0 ? comment.id : comment.mid)">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3" viewBox="0 0 16 16">
          <path
            fill-rule="evenodd"
            d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5v-1zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"
          />
        </svg>
        {{ $store.state.language.chain }}
      </router-link>
      <button
        type="button"
        class="btn btn-dark-blue shadow-sm btn-sm my-1"
        v-if="currentUser.permission.indexOf('answer_comment') !== -1 && comment.moderation !== 1"
        @click="ActionInputOpenedToggle(comment.prefixId)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-reply" viewBox="0 0 16 16">
          <path
            d="M6.598 5.013a.144.144 0 0 1 .202.134V6.3a.5.5 0 0 0 .5.5c.667 0 2.013.005 3.3.822.984.624 1.99 1.76 2.595 3.876-1.02-.983-2.185-1.516-3.205-1.799a8.74 8.74 0 0 0-1.921-.306 7.404 7.404 0 0 0-.798.008h-.013l-.005.001h-.001L7.3 9.9l-.05-.498a.5.5 0 0 0-.45.498v1.153c0 .108-.11.176-.202.134L2.614 8.254a.503.503 0 0 0-.042-.028.147.147 0 0 1 0-.252.499.499 0 0 0 .042-.028l3.984-2.933zM7.8 10.386c.068 0 .143.003.223.006.434.02 1.034.086 1.7.271 1.326.368 2.896 1.202 3.94 3.08a.5.5 0 0 0 .933-.305c-.464-3.71-1.886-5.662-3.46-6.66-1.245-.79-2.527-.942-3.336-.971v-.66a1.144 1.144 0 0 0-1.767-.96l-3.994 2.94a1.147 1.147 0 0 0 0 1.946l3.994 2.94a1.144 1.144 0 0 0 1.767-.96v-.667z"
          />
        </svg>
        {{ $store.state.language.reply }}
      </button>
      <button
        type="button"
        class="btn btn-dark-blue shadow-sm btn-sm my-1"
        v-if="
          currentUser.permission.indexOf('manage_comments') !== -1 ||
          (comment.authorId === currentUser.id &&
            currentUser.permission.indexOf('update_comment') !== -1 &&
            $store.state.config.currentTime - comment.datePublished.seconds < $store.state.config.editTime * 60 &&
            $store.state.config.edit)
        "
        @click="ActionEditOpenedToggle(comment.prefixId)"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
          <path
            d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"
          />
        </svg>
        {{ $store.state.language.edit }}
      </button>
      <button
        type="button"
        class="btn btn-dark-blue shadow-sm btn-sm my-1"
        v-if="
          comment.posted === 1 &&
          (currentUser.permission.indexOf('manage_comments') !== -1 ||
            (comment.authorId === currentUser.id && currentUser.permission.indexOf('delete_comment') !== -1))
        "
        @click="deleteConfirm"
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
      <span class="dropdown">
        <button class="dropdown-toggle btn btn-dark-blue shadow-sm btn-sm my-1" type="button" data-bs-toggle="dropdown"></button>
        <ul class="dropdown-menu dropdown-menu-dark">
          <li v-if="currentUser.permission.indexOf('manage_comments') !== -1 && comment.reportsCount > 0 && comment.newReportsCount === 0">
            <router-link type="button" class="dropdown-item" :to="'/reports/' + comment.id">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="currentColor"
                class="bi bi-exclamation-octagon"
                viewBox="0 0 16 16"
              >
                <path
                  d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z"
                />
                <path
                  d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"
                />
              </svg>
              {{ $store.state.language.reports }}
            </router-link>
          </li>
          <li v-if="$store.state.config.delete_method === 'unposted' && currentUser.permission.indexOf('remove_comment') !== -1">
            <button class="dropdown-item" type="button" @click="removeConfirm">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash2" viewBox="0 0 16 16">
                <path
                  d="M14 3a.702.702 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671L2.037 3.225A.703.703 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2zM3.215 4.207l1.493 8.957a1 1 0 0 0 .986.836h4.612a1 1 0 0 0 .986-.836l1.493-8.957C11.69 4.689 9.954 5 8 5c-1.954 0-3.69-.311-4.785-.793z"
                />
              </svg>
              {{ $store.state.language.delete_from_database }}
            </button>
          </li>
        </ul>
      </span>
    </div>

    <modal-confirm
      v-if="deleteModal.indexOf(comment.id) !== -1"
      :title="$store.state.language.delete_comment"
      :body="$store.state.language.confirm_delete_comment"
      @click="ActionConfirm('delete', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="removeModal.indexOf(comment.id) !== -1"
      :title="$store.state.language.delete_comment"
      :body="$store.state.language.comment_completely_removed"
      @click="ActionConfirm('remove', $event)"
    ></modal-confirm>
    <modal-confirm
      v-if="recoverModal.indexOf(comment.id) !== -1"
      :title="$store.state.language.recover_comment"
      :body="$store.state.language.confirm_recover_comment"
      @click="ActionConfirm('recover', $event)"
    ></modal-confirm>

    <images-view
      v-if="showImages.indexOf(comment.id) !== -1"
      :images="comment.attach"
      :index="imageSelected"
      @click="imagePreviewClose($event)"
    ></images-view>
  </div>
</template>

<script>
import CommentInput from "./Input";
export default {
  data() {
    return {
      deleteModal: [],
      removeModal: [],
      recoverModal: [],
      inputOpened: [],
      editOpened: [],
      writeComment: [],
      editElements: {},
      imageSelected: 0,
      showImages: [],
    };
  },
  components: {
    CommentInput,
  },
  props: ["comment", "checked"],
  directives: {
    blockSize: {
      inserted: function (el, binding, vnode) {
        if (el.offsetHeight > 260) {
          el.style.overflow = "hidden";
          el.style.height = 260 + "px";
          el.style.cursor = "pointer";
          el.classList.add("block_size");

          el.addEventListener("click", (event) => {
            el.style = null;
            el.classList.remove("block_size");
          });
        }
      },
      componentUpdated: function (el, binding, vnode) {
        el.style = null;
        el.classList.remove("block_size");
        if (el.offsetHeight > 260) {
          el.style.overflow = "hidden";
          el.style.height = 260 + "px";
          el.style.cursor = "pointer";
          el.classList.add("block_size");

          el.addEventListener("click", (event) => {
            el.style = null;
            el.classList.remove("block_size");
          });
        }
      },
    },
  },
  computed: {
    currentUser() {
      return this.$store.state.user;
    },
  },
  methods: {
    imagePreview(idComment, indexImage) {
      this.showImages.push(idComment);
      this.imageSelected = indexImage;
    },
    imagePreviewClose(event) {
      if (event === "close") {
        this.showImages = [];
        this.imageSelected = 0;
      }
    },
    read() {
      if (this.$store.state.user.id === undefined) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "readComment",
          id: this.comment.id,
        })
        .then((response) => {
          if (response.data) {
            this.$emit("updateNewComment", 0);
          }
        });
    },
    approve() {
      if (this.$store.state.user.id === undefined) {
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "approveComment",
          id: this.comment.id,
          type: this.comment.type,
        })
        .then((response) => {
          if (response.data) {
            this.$emit("updateModeration", 0);
            this.$emit("updateNewComment", 0);
          }
        });
    },
    delete() {
      if (this.$store.state.user.id === undefined) {
        return false;
      }
      axios
        .post(this.$store.state.apiPath, {
          action: "deleteComment",
          id: this.comment.id,
          type: this.comment.type,
        })
        .then((response) => {
          if (response.data.delete === true) {
            if (this.$store.state.config.delete_method === "unposted") {
              this.$emit("updatePosted", 0);
            }
            if (this.$store.state.config.delete_method === "delete") {
              this.$parent.getComments();
              this.$parent.getCount();
            }

            this.deleteClose();
          }
        });
    },
    remove() {
      if (this.$store.state.user.id === undefined) {
        return false;
      }
      axios
        .post(this.$store.state.apiPath, {
          action: "removeComment",
          id: this.comment.id,
          type: this.comment.type,
        })
        .then((response) => {
          if (response.data.delete === true) {
            this.$parent.getComments();
            this.$parent.getCount();
            this.deleteClose();
          }
        });
    },
    recover() {
      if (this.$store.state.user.id === undefined) {
        return false;
      }
      axios
        .post(this.$store.state.apiPath, {
          action: "recoverComment",
          id: this.comment.id,
          type: this.comment.type,
        })
        .then((response) => {
          if (response.data.recover === true) {
            this.$emit("updatePosted", 1);
            this.$emit("updateNewComment", 0);
            this.recoverClose();
          }
        });
    },
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
          this.deleteClose();
        }
        if (action === "remove") {
          this.removeClose();
        }
        if (action === "recover") {
          this.recoverClose();
        }
      }
    },
    deleteConfirm() {
      this.deleteModal.push(this.comment.id);
    },
    deleteClose() {
      this.deleteModal.splice(this.deleteModal.indexOf(this.comment.id));
    },
    removeConfirm() {
      this.removeModal.push(this.comment.id);
    },
    removeClose() {
      this.removeModal.splice(this.removeModal.indexOf(this.comment.id));
    },
    recoverConfirm() {
      this.recoverModal.push(this.comment.id);
    },
    recoverClose() {
      this.recoverModal.splice(this.recoverModal.indexOf(this.comment.id));
    },
    InputShow(id) {
      if (this.inputOpened.indexOf(id) === -1) {
        return false;
      } else {
        return true;
      }
    },
    EditShow(id) {
      if (this.editOpened.indexOf(id) === -1) {
        return false;
      } else {
        return true;
      }
    },
    ActionEditOpenedToggle(id) {
      let key = this.editOpened.indexOf(id);

      if (key === -1) {
        this.editOpened.push(id);
      } else {
        this.editOpened.splice(key, 1);
      }
    },
    ActionInputOpenedToggle(id) {
      let key = this.inputOpened.indexOf(id);

      if (key === -1) {
        this.inputOpened.push(id);
      } else {
        this.inputOpened.splice(key, 1);
      }
    },
  },
  watch: {
    writeComment: function (val) {
      this.$parent.comments.unshift(val);
    },
    editElements: function (val) {
      this.$emit("updateTextOrigin", val.textOrigin);
      this.$emit("updateText", val.text);
      this.$emit("updateAttach", val.attach);
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .blockquote-box {
    border-right: $borderPale solid 3px;
    .blockquote {
      font-size: 12px;
    }
    .blockquote-footer {
      font-size: 13px;
    }
  }
  .video-play {
    position: relative;
    .video-play-icon {
      // content: "";
      display: block;
      width: 50px;
      height: 50px;
      margin-top: -25px;
      margin-left: -25px;
      position: absolute;
      top: 50%;
      left: 50%;
    }
  }
  .dropdown-item {
    color: $light;
  }
  .block_size {
    position: relative;
    &::after {
      content: "";
      position: absolute;
      background: linear-gradient(0deg, $screenComments 0%, rgba(255, 255, 255, 0) 100%);
      width: 100%;
      height: 100px;
      bottom: 0;
    }
  }
  .text-quote {
    color: #7f8b9a;
  }
}
</style>
<style lang="scss">
#commentzone {
  .blockquote {
    .cz-emoji-view {
      width: 14px;
      height: 14px;
      background-size: 140px;
    }
  }
  .cz-emoji-view {
    width: 20px;
    height: 20px;
    display: inline-block;
    background-repeat: no-repeat;
    vertical-align: text-bottom;
  }
}
</style>