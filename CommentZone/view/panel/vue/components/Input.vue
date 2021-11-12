<template>
  <div class="p-3">
    <div class="row border bg-input py-2 mt-2 mx-0 rounded" :class="errors.text[id] ? 'is-invalid' : ''">
      <div class="input-block col-12">
        <textarea
          :ref="'text-' + id"
          v-auto-height
          v-focus
          v-model="answer[id]"
          @input="inputComment(id)"
          :placeholder="$store.state.language.enter_comment"
        ></textarea>
        <div v-if="attach" class="attach row px-2">
          <div v-for="(item, key) in attach[id]" :key="key" class="col-auto m-1 p-0 attach-img-mini">
            <div v-if="item">
              <span class="attach-img-delete" @click="actionDeleteAttach(key)">&times;</span>
              <img
                class="border"
                :src="(item.type === 'image' || item.resource === 'tiktok' ? $store.state.config.resource : '') + item.preview"
                width="50px"
                height="50px"
                alt=""
              />
            </div>
          </div>
        </div>
      </div>
      <div class="col col-sm-12 my-2">
        <div class="row align-items-end">
          <div class="col-auto">
            <div class="row row-cols-3 m-0">
              <div class="col-sm-auto ps-0 pe-2">
                <div>
                  <label class="icon-control-to-form" :for="'upload-images-' + id">
                    <span
                      class="icon-image-upload"
                      :style="'background: url(' + $store.state.config.resource + '/img/icons/image-upload.png) right / cover no-repeat;'"
                    ></span>
                  </label>
                  <input
                    class="d-none"
                    type="file"
                    name="images"
                    accept="image/jpeg,image/png"
                    multiple
                    :id="'upload-images-' + id"
                    @change="actionUploadImages($event)"
                  />
                </div>
              </div>
              <div class="col-sm-auto ps-0 pe-2">
                <div class="icon-control-to-form" @click="addVideoModal = true">
                  <div
                    class="icon-video"
                    :style="'background: url(' + $store.state.config.resource + '/img/icons/video.png) right / cover no-repeat;'"
                  ></div>
                </div>

                <div v-if="addVideoModal" :class="addVideoModal ? 'show' : ''" class="modal fade">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header border-0">
                        <h5 class="modal-title">{{ $store.state.language.add_video }}</h5>
                        <button type="button" class="btn-close btn-close-white" @click="addVideoModal = false"></button>
                      </div>
                      <div class="modal-body">
                        <form>
                          <div class="form-group">
                            <label :for="'link-video-input' + id">{{ $store.state.language.enter_link }}</label>
                            <input
                              v-model="linkVideo"
                              @input="inputVideoLink(id)"
                              type="text"
                              class="form-control"
                              :class="errors.videoLink[id] ? 'is-invalid' : ''"
                              :id="'link-video-input' + id"
                            />
                            <small v-if="!errors.videoLink[id]" class="form-text text-pale">{{
                              $store.state.language.video_service_name_list
                            }}</small>
                            <div class="invalid-feedback">{{ errors.videoLink[id] ? errors.videoLink[id] : "" }}</div>
                          </div>
                        </form>
                      </div>
                      <div class="modal-footer border-0">
                        <button type="button" class="btn btn-dark-blue shadow-sm" @click="actionAddVideo()">
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
                          {{ $store.state.language.send }}
                        </button>
                        <button type="button" class="btn btn-outline-light shadow-sm" @click="addVideoModal = false">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="16"
                            height="16"
                            fill="currentColor"
                            class="bi bi-x-circle"
                            viewBox="0 0 16 16"
                          >
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

              <div class="col-sm-auto ps-0 pe-2 dropdown emoji-block" @mouseenter="emojiBlockPosition($event)">
                <div class="icon-control-to-form">
                  <div
                    class="icon-smiles"
                    :style="'background: url(' + $store.state.config.resource + '/img/icons/emoji.png) right / cover no-repeat;'"
                  ></div>
                </div>
                <div class="dropdown-menu emoji-box">
                  <emoji :prefix-id="comment.prefixId + '-' + action"></emoji>
                </div>
              </div>
            </div>
          </div>
          <div class="col text-end">
            <button type="button" class="btn btn-outline-light btn-sm" @click="action === 'edit' ? updateComment() : setAnswerComment()">
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
              {{ $store.state.language.send }}
            </button>
            <button
              type="button"
              class="btn btn-outline-light btn-sm"
              @click="
                action === 'answer' ? $parent.ActionInputOpenedToggle(comment.prefixId) : $parent.ActionEditOpenedToggle(comment.prefixId)
              "
            >
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
    <div class="invalid-feedback">{{ errors.text[id] ? errors.text[id] : "" }}</div>
  </div>
</template>

<script>
import Emoji from "./Emoji";
export default {
  data() {
    return {
      addVideoModal: false,
      linkVideo: "",
      answer: {},
      attach: {
        edit: {},
        answer: {},
      },
      errors: {
        text: {},
        videoLink: {},
      },
    };
  },
  components: {
    Emoji,
  },
  props: ["id", "comment", "action"],
  directives: {
    autoHeight: {
      inserted: function (el) {
        el.style.height = "auto";
        el.style.height = el.scrollHeight + (el.scrollHeight > 200 ? 20 : 0) + "px";
      },
      update: function (el) {
        el.style.height = "auto";
        el.style.height = el.scrollHeight + (el.scrollHeight > 200 ? 20 : 0) + "px";
      },
    },
    focus: {
      inserted: function (e) {
        e.focus();
      },
    },
  },
  created() {
    if (this.action === "answer") {
      this.answer = JSON.parse(localStorage.getItem("answer-panel")) || {};
      this.attach = JSON.parse(localStorage.getItem("answer-attach-panel")) || {};
    }
    if (this.action === "edit") {
      this.$set(this.answer, this.id, this.comment.textOrigin);
      this.$set(this.attach, this.id, this.comment.attach);
    }
  },
  methods: {
    emojiBlockPosition(event) {
      let emojiBox = event.target.getElementsByClassName("emoji-box")[0];
      let screenWindowHeight = window.innerHeight;

      let emojiBoxCoordinates = emojiBox.getBoundingClientRect();
      let emojiBlockHeight = emojiBoxCoordinates.height;
      let emojiBlockTop = emojiBoxCoordinates.top;

      if (emojiBlockTop > screenWindowHeight - emojiBlockHeight) {
        emojiBox.style.bottom = "30px";
        emojiBox.style.top = "auto";
      } else if (emojiBlockTop <= 0) {
        emojiBox.removeAttribute("style");
      }
    },
    setAnswerComment: function () {
      if (this.$store.state.config.anonimus === "off" && this.$store.state.user.id === undefined) {
        return false;
      }

      if (this.$store.state.user.id === undefined) {
        return false;
      }

      if (!this.answer[this.id] && (!this.attach[this.id] || this.attach[this.id].length === 0)) {
        this.$set(this.errors.text, this.id, this.$store.state.language.empty_field);
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "setAnswerComment",
          pid: this.comment.id,
          parentType: this.comment.type,
          url: this.comment.pageUrl,
          bindId: this.comment.pageBindId,
          text: this.answer[this.id],
          attach: this.attach[this.id],
          title: this.comment.pageTitle,
        })
        .then((response) => {
          if (response.data.status !== "success") {
            return false;
          }

          this.$parent.ActionInputOpenedToggle(this.comment.prefixId);

          this.$emit("input", response.data.answer);

          delete this.answer[this.id];
          delete this.attach[this.id];
          this.inputComment(this.id);
          this.$parent.$parent.count++;

          if (this.action === "answer") {
            localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
          }
        });
    },
    updateComment: function () {
      if (this.$store.state.user.id === undefined) {
        return false;
      }

      if (!this.answer[this.id] && (!this.attach[this.id] || this.attach[this.id].length === 0)) {
        this.$set(this.errors.text, this.id, this.$store.state.language.empty_field);
        return false;
      }

      axios
        .post(this.$store.state.apiPath, {
          action: "updateComment",
          id: this.comment.id,
          type: this.comment.type,
          url: this.comment.pageUrl,
          bindId: this.comment.pageBindId,
          text: this.answer[this.id],
          attach: this.attach[this.id],
          title: this.comment.pageTitle,
        })
        .then((response) => {
          if (response.data.status !== "success") {
            return false;
          }

          this.$emit("input", { textOrigin: response.data.textOrigin, text: response.data.text, attach: response.data.attach });

          delete this.answer[this.id];
          delete this.attach[this.id];

          this.$parent.ActionEditOpenedToggle(this.comment.prefixId);
        });
    },
    actionUploadImages(e) {
      if (!this.attach[this.id]) {
        this.$set(this.attach, this.id, []);
      }
      let lastIndex = this.attach[this.id].length;
      let lenfiles = e.target.files.length;

      for (const key in e.target.files) {
        if (Object.hasOwnProperty.call(e.target.files, key)) {
          const element = e.target.files[key];

          let data = new FormData();
          data.append("action", "uploadImages");
          data.append("type", "answer");
          data.append("file", element);

          this.attach[this.id].push({
            preview: "/img/icons/spinner.gif",
            type: "image",
          });

          axios.post(this.$store.state.apiPath, data).then((response) => {
            if (response.data === "limit_size") {
              this.$set(
                this.errors.text,
                this.id,
                lenfiles > 1 ? this.$store.state.language.some_images_size_long : this.$store.state.language.images_size_long
              );
              this.attach[this.id].splice(lastIndex, 1);

              if (this.action === "answer") {
                localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
              }

              return false;
            }

            if (response.data !== false && typeof response.data === 'object') {
              for (const key in response.data) {
                if (Object.hasOwnProperty.call(response.data, key)) {
                  const element = response.data[key];

                  this.attach[this.id].splice(lastIndex++, 1, element);
                }
              }
            } else {
              this.attach[this.id].splice(lastIndex, 1);
            }
            if (this.action === "answer") {
              localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
            }
          });
        }
      }
    },
    actionAddVideo() {
      if (!this.linkVideo) {
        this.$set(this.errors.videoLink, this.id, this.$store.state.language.empty_field);
        return false;
      }

      if (!this.attach[this.id]) {
        this.$set(this.attach, this.id, []);
      }
      var lastIndex = this.attach[this.id].length;

      this.attach[this.id].push({
        preview: "/img/icons/spinner.gif",
        type: "video",
      });

      axios
        .post(this.$store.state.apiPath, {
          action: "addVideo",
          link: this.linkVideo,
        })
        .then((response) => {
          if (response.data === false) {
            this.$set(this.errors.videoLink, this.id, this.$store.state.language.invalid_link);
            this.attach[this.id].splice(lastIndex, 1);
            return false;
          }

          this.attach[this.id].splice(lastIndex, 1, response.data);
          this.addVideoModal = false;
          this.linkVideo = "";

          if (this.action === "answer") {
            localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
          }
        });
    },
    actionDeleteAttach: function (index) {
      if (this.attach[this.id][index].type === "image") {
        axios
          .post(this.$store.state.apiPath, {
            action: "deleteImages",
            attach: this.attach[this.id][index] || null,
          })
          .then((response) => {
            this.attach[this.id].splice(index, 1);
            if (this.attach[this.id].length === 0) {
              this.$delete(this.attach, this.id);
            }

            if (this.action === "answer") {
              localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
            }
          });
      } else {
        this.attach[this.id].splice(index, 1);
        if (this.attach[this.id].length === 0) {
          this.$delete(this.attach, this.id);
        }

        if (this.action === "answer") {
          localStorage.setItem("answer-attach-panel", JSON.stringify(this.attach));
        }
      }
    },
    inputComment(id) {
      this.$delete(this.errors.text, id);

      if (this.action === "answer") {
        localStorage.setItem("answer-panel", JSON.stringify(this.answer));
      }
    },
    inputVideoLink(id) {
      this.$delete(this.errors.videoLink, id);
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .dropdown-menu {
    background-color: $buttonsHover !important;
  }
  .is-invalid {
    border-color: $form-feedback-invalid-color !important;
  }
  .input-block {
    max-height: 200px;
    overflow: auto;
    textarea {
      width: 100%;
      border: none;
      resize: none;
      outline: none;
      overflow: hidden;
      background-color: transparent;
      color: #fff;
      &::placeholder {
        color: #fff;
      }
    }
    .attach {
      .attach-img-mini {
        position: relative;
        &:hover .attach-img-delete {
          display: block;
        }
        .attach-img-delete {
          position: absolute;
          background-color: #868fa2;
          color: #fff;
          padding: 0px 3px;
          font-size: 18px;
          line-height: 19px;
          top: 1px;
          right: 1px;
          display: none;
          cursor: pointer;
        }
      }
    }
  }
  .icon-control-to-form {
    width: 31px;
    height: 31px;
    display: block;
    background: $buttonsHover;
    border-radius: 50%;
    position: relative;
    margin: 0;
    cursor: pointer;
    .icon-smiles,
    .icon-video,
    .icon-image-upload {
      content: "";
      width: 18px;
      height: 18px;
      position: absolute;
      top: 50%;
      left: 50%;
      margin-top: -9px;
      margin-left: -9px;
    }
  }
  .icon-image-upload {
    opacity: 0.4;
  }
  .icon-video {
    opacity: 0.4;
  }
  .icon-smiles {
    opacity: 0.8;
  }

  .emoji-block {
    &:hover {
      & .emoji-box {
        display: block;
      }
    }
    .emoji-box {
      margin-top: 0;
    }
  }
}
</style>