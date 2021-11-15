<script>
  var bootstrap = document.createElement('link');
  bootstrap.rel = 'stylesheet';
  bootstrap.href = '<?php echo $config['resource']; ?>/style/bootstrap.min.css';
  document.head.insertAdjacentElement('afterbegin', bootstrap);

  var vueLoadStyle = document.createElement('link');
  vueLoadStyle.rel = 'stylesheet';
  vueLoadStyle.href = '<?php echo $config['resource']; ?>/style/<?php echo $config['theme']; ?>/main.css';
  document.head.insertAdjacentElement('afterbegin', vueLoadStyle);
</script>
<script src="<?php echo $config['resource']; ?>/js/vue@2.js"></script>
<script src="<?php echo $config['resource']; ?>/js/axios.min.js"></script>
<?php if ($config['recaptcha']) : ?>
  <script src="https://www.google.com/recaptcha/api.js<?php echo $config['recaptchaVersion'] === 'v3' ? '?render=' . $config['recaptchaKeyV3'] : ''; ?>"></script>
<?php endif; ?>


<div id="CommentZone" class="container" :class="'d-block'" style="display: none;" :style="wait ? 'cursor: wait' : ''">
  <div v-if="config.authorize" class="row my-2">
    <div class="p-0 text-end">
      <div v-if="user.id" class="dropdown">
        <div class="dropdown-toggle pointer" @click.stop="dropdownToggle">
          <span v-if="(!user.avatar || !user.avatar.small) && config.avatarSimbol" class="me-1 align-text-top cz-avatar-min cz-simbol" :style="'background:' + user.avatar" v-html="user.name.substring(0, 1)"></span>
          <span v-else><img class="me-1 align-text-top cz-avatar-min" :src="user.avatar && user.avatar.small ? config.resource + user.avatar.small : config.resource + '/img/avatars/default.jpg'" alt=""></span>
          <span>{{ user.name }}</span><span v-if="user.role === 'anonim'">{{ '-' + user.puid  }}</span>
        </div>
        <div class="row dropdown-menu dropdown-menu-end end-0 cz-author-name-menu" :class="dropdown ? 'show' : ''">
          <div v-if="user.email !== ''" class="dropdown-item">{{ user.email }}</div>
          <div class="dropdown-item pointer" @click="logout">{{ language.exit }}</div>
        </div>
      </div>
      <div v-else class="">
        <button type="button" class="btn cz-btn-grey btn-sm pointer" @click.stop="authorizeModal = true">{{ language.login }}</button>
      </div>
    </div>
  </div>

  <div class="row border py-2" :class="comments[0].error.text ? 'cz-is-invalid is-invalid' : ''">
    <div class="col-12" style="max-height: 200px;overflow: auto;">
      <textarea ref="text-0" @input="clearError" v-auto-height="200" id="csm-0" rows="2" class="w-100 border-0 p-0 cz-main-text" style="resize: none;outline: none;overflow: hidden;" :placeholder="language.enter_comment" v-model="mainText"></textarea>
      <div v-if="mainAttach.length > 0" class="cz-attach row px-2">
        <div v-for="(item, key) in mainAttach" :key="key" class="col-auto m-1 p-0 cz-img-mini">
          <span class="cz-close pointer" @click="actionDeleteAttach(key)">&times;</span>
          <img class="border" :src="(item.type === 'image' || item.resource === 'tiktok' ? config.resource : '') + item.preview" width="50px" height="50px" alt="">
        </div>
      </div>
    </div>
    <div class="col col-sm-12 my-2">
      <div class="row align-items-end">
        <div class="col-auto">
          <div v-if="user.id && user.role !== 'anonim'" class="row row-cols-3 m-0">
            <div v-if="config.images" class="col-sm-auto ps-0 pe-2">
              <div class="">
                <label class="cz-icon-control-to-form cz-icon-image-upload" for="cz-upload-images"></label>
                <input @change="actionUploadImages" class="d-none" type="file" name="images" accept="image/jpeg,image/png" multiple id="cz-upload-images">
              </div>
            </div>
            <div v-if="config.video" class="col-sm-auto ps-0 pe-2">
              <div class="cz-icon-control-to-form cz-icon-video" @click="addVideoModal = true"></div>
              <div :class="addVideoModal ? 'fade show' : ''" class="modal">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header border-0">
                      <h5 class="modal-title">{{ language.add_video }}</h5>
                      <button type="button" class="btn-close" @click="addVideoModal = false"></button>
                    </div>
                    <div class="modal-body">
                      <form>
                        <div class="form-group">
                          <label for="cz-link-video-input">{{ language.enter_link }}</label>
                          <input v-model="linkVideo" type="text" class="form-control" :class="addVideoError ? 'cz-is-invalid is-invalid' : ''" v-on:input="addVideoError = ''" id="cz-link-video-input">
                          <small v-if="addVideoError" class="invalid-feedback">{{ addVideoError }}</small>
                          <small v-else class="form-text text-muted">{{ language.video_service_name_list }}</small>
                        </div>
                      </form>
                    </div>
                    <div class="modal-footer border-0">
                      <button type="button" class="btn btn-primary" @click="actionAddVideo">{{ language.send }}</button>
                      <button type="button" class="btn btn-secondary" @click="addVideoModal = false">{{ language.close }}</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="config.emoji" class="col-sm-auto ps-0 pe-2 dropdown cz-emoji-block">
              <div class="cz-icon-control-to-form cz-icon-smiles"></div>
              <div class="dropdown-menu cz-emoji-box">
                <comment-emoji prefix-id="0"></comment-emoji>
              </div>
            </div>
          </div>
        </div>
        <div class="col text-end">
          <span class="cz-count-text font-weight-light me-2" :class="mainText.length > config.text_length ? 'text-danger' : 'text-black-50'">
            {{mainText.length}}/{{config.text_length}}
          </span>
          <button id="csMainSubmit" type="button" class="btn cz-btn-grey btn-sm" :disabled="sentDisabled" v-on:click="setMainComment()">
            <span>{{ language.send }}</span>
            <span v-if="user.id === undefined && config.anonimus"> {{ language.send_anonymous }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="invalid-feedback">{{ comments[0].error.text }}</div>

  <div class="row my-4 border-bottom cz-comments-body-header">
    <div class="col-auto"><span class="row">{{ language.comments }} ({{ count.main + count.answer }})</span></div>
    <div class="col position-relative" v-dropdown="sortLess">
      <div class="col-auto text-end cz-sort-btn dropdown-toggle" :class="!sortLess ? 'd-none' : ''" @click="sortLessShow = sortLessShow ? false : true">{{ language.sort_by }}</div>
      <div class="row justify-content-end cz-sort-box" :class="sortBoxClass()">
        <div class="col-auto cz-sort-item" :class="sortItemClass('new')" :style="wait ? 'cursor: wait' : ''" @click="actionSort('new')">{{ language.new_ones }}</div>
        <div class="col-auto cz-sort-item" :class="sortItemClass('old')" :style="wait ? 'cursor: wait' : ''" @click="actionSort('old')">{{ language.old }}</div>
        <div v-if="config.rating" class="col-auto cz-sort-item" :class="sortItemClass('pop')" :style="wait ? 'cursor: wait' : ''" @click="actionSort('pop')">{{ language.popular }}</div>
      </div>
    </div>
  </div>

  <comments-view :comments="commentsMain"></comments-view>
  <div v-if="commentsMain.length > 0 && (listId * config.limit) + config.limit < count.main" class="d-grid gap-2 col-6 mx-auto my-4">
    <button type="button" class="btn cz-btn-grey" @click="actionMoreComment($event)">{{ language.more_comments }}</button>
  </div>
  <comment-delete v-if="deleteComment.id !== undefined" :disabled="deleteDisabled" @delete="actionDeleteComment()" @close="deleteComment = {}"></comment-delete>

  <comment-report v-if="idCommentReport !== 0" :cid="idCommentReport"></comment-report>
  <comment-authorize v-if="config.authorize && authorizeModal" @show-reset-password="showResetPasswordModal()" @close="authorizeModal = false"></comment-authorize>
  <recover-password v-if="resetPasswordModal" @close="resetPasswordModal = false"></recover-password>
  <toast-view v-if="toast.show" :bg="toast.bg" :message="toast.message" @close="$emit ? toast.show = false : ''"></toast-view>
  <recaptcha-modal :show="recaptchaModalShow" @close="recaptchaModalShow = false" @click="setCommentByType"></recaptcha-modal>
</div>

<?php require_once __DIR__ . '/components/comment-emoji.html' ?>
<?php require_once __DIR__ . '/components/comment-input.html' ?>
<?php require_once __DIR__ . '/components/comment-views.html' ?>
<?php require_once __DIR__ . '/components/comment-delete.html' ?>
<?php require_once __DIR__ . '/components/comment-images.html' ?>
<?php require_once __DIR__ . '/components/comment-authorize.html' ?>
<?php require_once __DIR__ . '/components/recover-password.html' ?>
<?php require_once __DIR__ . '/components/comment-report.html' ?>
<?php require_once __DIR__ . '/components/recaptcha-modal.html' ?>
<?php require_once __DIR__ . '/components/toasts.html' ?>

<script>
  Vue.directive('dropdown', {
    inserted: function(el, binding, vnode) {
      function resize() {
        if (window.screen.width <= 614) {
          vnode.context.sortLess = true;
        } else {
          vnode.context.sortLess = false;
        }
      }

      resize();

      window.addEventListener('resize', resize);
    }
  });
  Vue.directive('focus', {
    inserted: function(e) {
      e.focus();
    }
  });
  Vue.directive('auto-height', {
    inserted: function(el, binding, vnode) {
      el.style.height = 'auto';

      function timeOutAutoHeight() {
        setTimeout(function() {
          autoHeight();
        }, 10);
      }

      function autoHeight() {
        if (el.scrollHeight > 0) {
          el.style.height = el.scrollHeight + (el.scrollHeight > 200 ? 20 : 0) + 'px';
        } else {
          timeOutAutoHeight()
        }
      }

      autoHeight();
    },
    update: function(el, binding, vnode) {
      el.style.height = 'auto';

      if (el.scrollHeight > 0) {
        el.style.height = el.scrollHeight + (el.scrollHeight > 200 ? 20 : 0) + 'px';
      }
    },
  });

  var CommentScript = new Vue({
    el: '#CommentZone',
    data: {
      csrfToken: '<?php echo isset($csrf['token']) ? $csrf['token'] : ''; ?>',
      currentTime: <?php echo isset($currentTime) ? $currentTime : 0; ?>,
      url: '<?php echo $url; ?>',
      bindId: '<?php echo $bindId; ?>',
      uid: '<?php echo isset($user['id']) && !empty($user['id']) ? $user['id'] : 0; ?>',
      sort: '<?php echo $sort; ?>',
      listId: 0,
      language: <?php echo json_encode($language); ?>,
      emoji: <?php echo json_encode($emoji); ?>,
      user: <?php echo json_encode($user); ?>,
      config: <?php echo json_encode($config); ?>,
      apiPath: '<?php echo $config['api_path']; ?>',
      commentsMain: <?php echo json_encode($comments['main']); ?>,
      commentsAnswer: <?php echo json_encode($comments['answer']); ?>,
      mainText: '',
      selectText: {
        strat: 0,
        end: 0
      },
      linkVideo: '',
      mainAttach: [],
      count: <?php echo json_encode($count); ?>,
      dropdown: false,
      more: false,
      authorizeModal: false,
      resetPasswordModal: false,
      emojiBox: false,
      addVideoModal: false,
      moreComment: true,
      timeout: 0,
      comments: {
        0: {
          error: {
            text: ''
          }
        }
      },
      idCommentReport: 0,
      deleteComment: {},
      toast: {
        show: false,
        bg: '',
        message: ''
      },
      addVideoError: '',
      recaptchaToken: '',
      recaptchaVersion: 'v3',
      recaptchaModalShow: false,
      recaptcaContext: {},
      sentDisabled: false,
      deleteDisabled: false,
      wait: false,
      sortLess: false,
      sortLessShow: false,
    },
    created() {
      axios.defaults.headers.common = {
        'Cz-Csrf-Token': this.csrfToken
      };
      document.addEventListener('click', e => {
        var targetClass = Object.values(e.target.classList);
        if (targetClass.indexOf('cz-author-name-menu') === -1) {
          this.dropdownClose();
        }
        if (targetClass.indexOf('cz-sort-btn') === -1) {
          this.sortLessShow = false;
        }
      }, false);
      this.recaptchaVersion = this.config.recaptchaVersion;
    },
    mounted: function() {
      this.mainText = localStorage.getItem('main-' + this.url) || '';
      this.mainAttach = JSON.parse(localStorage.getItem('mainAttach-' + this.url)) || [];

      // axios.post(this.apiPath, {
      //   action: 'getComments',
      //   url: this.url,
      //   bindId: this.bindId,
      //   sort: this.sort,
      //   listId: this.listId,
      // }).then(response => {
      //   this.commentsMain = response.data.main;
      //   this.commentsAnswer = response.data.answer;
      // });

      // axios.post(this.apiPath, {
      //   action: 'getCount',
      //   url: this.url,
      //   bindId: this.bindId
      // }).then(response => {
      //   this.count = response.data;
      // });
    },
    methods: {
      sortBoxClass() {
        var strClass = '';

        if (this.sortLess) {
          strClass += 'dropdown-menu end-0';
        }
        if (this.sortLessShow) {
          strClass += ' show';
        }

        return strClass;
      },
      sortItemClass(val) {
        var strClass = '';

        if (this.sortLess) {
          strClass += 'dropdown-item';

          if (this.sort === val) {
            strClass += ' active';
          }
        } else {
          if (this.sort === val) {
            strClass += 'border-bottom border-primary';
          } else {
            strClass += 'pointer';
          }
        }

        return strClass;
      },
      clearError: function() {
        this.comments[0].error.text = '';
        this.timeout = 0;
      },
      toastShow: function(message, bg = 'bg-primary') {
        this.toast.show = true;
        this.toast.bg = bg;
        this.toast.message = message;
      },
      showResetPasswordModal: function() {
        this.resetPasswordModal = true;
        this.authorizeModal = false;
      },
      setCommentByType: function() {
        if (this.recaptcaContext.type) {
          if (this.recaptcaContext.type === 'main') {
            this.setMainComment();
          } else if (this.recaptcaContext.type === 'answer') {
            this.recaptcaContext.node.setAnswerComment(this.recaptcaContext.id);
          }
        }
      },
      recaptchaVerify: function(fn) {
        axios.post(this.apiPath, {
          action: 'recaptchaVerify',
          recaptchaToken: this.recaptchaToken,
          recaptchaVersion: this.recaptchaVersion
        }, {
          headers: {
            'Cz-Csrf-Token': this.csrfToken
          }
        }).then(response => {
          fn(response.data.success, response.data.error || '');
        });
      },
      recaptchaValidate: function(context, fn) {
        this.recaptcaContext.node = context.node || null;
        this.recaptcaContext.id = context.id || null;

        if (this.config.recaptcha && this.user.id === undefined) {
          if (!this.recaptchaToken) {
            if (this.recaptchaVersion === 'v3') {
              grecaptcha.execute(this.config.recaptchaKeyV3).then((token) => {
                this.recaptchaToken = token;

                this.recaptchaVerify((success, error) => {
                  if (success) {
                    this.recaptchaModalShow = false;
                    this.recaptcaContext = {};
                    this.recaptchaToken = '';
                    this.recaptchaVersion = this.config.recaptchaVersion;

                    fn(true);
                  } else {
                    if (this.config.recaptchaVersion === 'v3') {
                      this.recaptchaModalShow = true;
                      this.recaptcaContext = context;
                      grecaptcha.render("czRecaptchaAnon", {
                        "sitekey": this.config.recaptchaKeyV2,
                        'callback': (token) => {
                          this.recaptchaToken = token;
                        }
                      });
                    }

                    fn(false);
                    return false;
                  }
                })
              });
            } else {
              this.recaptchaModalShow = true;
              this.recaptcaContext = context;

              try {
                grecaptcha.render("czRecaptchaAnon", {
                  "sitekey": this.config.recaptchaKeyV2,
                  'callback': (token) => {
                    this.recaptchaToken = token;
                  }
                });
              } catch (error) {
                grecaptcha.reset();
              }
            }
          } else {
            this.recaptchaVersion = 'v2';

            this.recaptchaVerify((success, error) => {
              if (success) {
                this.recaptchaModalShow = false;
                this.recaptcaContext = {};
                this.recaptchaToken = '';
                this.recaptchaVersion = this.config.recaptchaVersion;
                grecaptcha.reset();

                fn(true);
              } else {
                fn(false);
                return false;
              }
            });
          }
        } else {
          fn(true);
        }
      },
      setMainComment: function() {
        this.comments[0].error.text = '';
        this.mainText = this.mainText.trim();

        if (!this.config.anonimus && this.user.id === undefined) {
          return false;
        }

        if (this.mainText === '' && this.mainAttach.length === 0) {
          this.comments[0].error.text = this.language.empty_field;
          return false;
        }

        if (this.mainText.length > this.config.text_length) {
          this.comments[0].error.text = this.language.character_limit_exceeded;
          return false;
        }

        this.recaptchaValidate({
          type: 'main'
        }, (success) => {
          if (success) {
            this.sentDisabled = true;
            this.wait = true;

            axios.post(this.apiPath, {
              action: 'setMainComment',
              url: this.url,
              bindId: this.bindId,
              text: this.mainText,
              attach: this.mainAttach,
              title: document.head.querySelector('title').innerText,
            }).then(response => {
              this.sentDisabled = false;
              this.wait = false;

              if (response.data.length === 0 || !response.data.status) {
                return false;
              }

              if (response.data.status === 'stop_words') {
                this.comments[0].error.text = this.language.forbidden_words + ' - (' + response.data.words + ')';
                return false;
              }

              if (response.data.status === 'spam') {
                this.comments[0].error.text = this.language.message_like_spam;
                return false;
              }

              if (response.data.status === 'timeout') {
                var title = this.language.timeout + ': ';
                this.timeout = response.data.timer;

                this.comments[0].error.text = title + this.timeout;

                var timerId = setInterval(() => {
                  if (this.timeout === 0) {
                    clearInterval(timerId);
                    this.comments[0].error.text = '';
                    return false;
                  }

                  this.timeout--

                  this.comments[0].error.text = title + this.timeout;
                }, 1000);

                return false;
              }

              if (response.data.status === 'moderation') {
                this.toastShow(this.language.comment_sent_moderation);
              }

              if (response.data.status === 'success') {
                this.commentsMain.unshift(response.data.main);
                this.count.main++;
              }

              this.mainText = '';
              this.mainAttach = [];
              this.user = response.data.currentUser;

              localStorage.removeItem('mainAttach-' + this.url);
            });
          }
        });
      },
      actionUploadImages: function() {
        var lastIndex = this.mainAttach.length;
        var lenfiles = event.target.files.length;
        var countAttempts = 0;

        this.sentDisabled = true;

        for (const key in event.target.files) {
          if (Object.hasOwnProperty.call(event.target.files, key)) {
            const element = event.target.files[key];
            var data = new FormData;
            data.append('action', 'uploadImages');
            data.append('type', 'main');
            data.append('file', element);

            this.mainAttach.push({
              preview: '/img/icons/spinner.gif',
              type: 'image'
            });

            axios.post(this.apiPath, data).then(response => {
              countAttempts++;
              if (response.data === "limit_size") {
                this.comments[0].error.text = lenfiles > 1 ? this.language.some_images_size_long : this.language.images_size_long;
                this.mainAttach.splice(lastIndex, 1);
                localStorage.setItem('mainAttach-' + this.url, JSON.stringify(this.mainAttach));
              } else if (response.data !== false && typeof response.data === 'object') {
                for (const key in response.data) {
                  if (Object.hasOwnProperty.call(response.data, key)) {
                    const element = response.data[key];
                    this.mainAttach.splice(lastIndex++, 1, element);
                  }
                }
              } else {
                this.mainAttach.splice(lastIndex, 1);
              }

              localStorage.setItem('mainAttach-' + this.url, JSON.stringify(this.mainAttach));

              if (countAttempts === lenfiles) {
                this.sentDisabled = false;
              }
            });
          }
        }
      },
      actionAddVideo: function() {
        if (this.linkVideo === '') {
          this.addVideoError = this.$root.language.empty_field;
          return false;
        }
        var lastIndex = this.mainAttach.length;
        this.mainAttach.push({
          preview: '/img/icons/spinner.gif',
          type: 'image'
        });

        axios.post(this.apiPath, {
          action: 'addVideo',
          link: this.linkVideo
        }).then(response => {
          if (!response.data) {
            this.$delete(this.mainAttach, lastIndex);
            return false;
          }

          this.mainAttach.splice(lastIndex, 1, response.data);
          this.addVideoModal = false;
          this.linkVideo = '';

          localStorage.setItem('mainAttach-' + this.url, JSON.stringify(this.mainAttach));
        });
      },
      actionDeleteAttach: function(index) {
        if (this.mainAttach[index].type === 'image') {
          axios.post(this.apiPath, {
            action: 'deleteImages',
            attach: this.mainAttach[index] || null
          }).then(response => {
            this.mainAttach.splice(index, 1);
            localStorage.setItem('mainAttach-' + this.url, JSON.stringify(this.mainAttach));
          });
        } else {
          this.mainAttach.splice(index, 1);
          localStorage.setItem('mainAttach-' + this.url, JSON.stringify(this.mainAttach));
        }
      },
      logout: function() {
        axios.post(this.apiPath, {
          action: 'logout'
        }).then(response => {
          if (response.data === true) {
            this.user = [];
          }
        });
      },
      actionDeleteComment: function() {
        if (this.user.id === undefined) {
          return false;
        }

        this.deleteDisabled = true;
        this.wait = true;

        axios.post(this.apiPath, {
          action: 'deleteComment',
          id: this.deleteComment.id,
          type: this.deleteComment.type,
          url: this.url,
          bindId: this.bindId,
        }).then(response => {
          this.deleteDisabled = false;
          this.wait = false;

          if (response.data.delete === true) {
            if (this.deleteComment.type === 'main') {
              if (this.config.delete_method === 'unposted') {
                this.$set(this.commentsMain[this.deleteComment['path']], 'text', response.data['main'].text);
                this.$set(this.commentsMain[this.deleteComment['path']], 'posted', response.data['main'].posted);
              }
              if (this.config.delete_method === 'delete') {
                this.commentsMain.splice(this.deleteComment['path'], 1);
              }
            }
            if (this.deleteComment.type === 'answer') {
              if (this.config.delete_method === 'unposted') {
                this.$set(this.commentsAnswer[this.deleteComment['path']][this.deleteComment.id], 'text', response.data['answer'].text);
                this.$set(this.commentsAnswer[this.deleteComment['path']][this.deleteComment.id], 'posted', response.data['answer'].posted);
              }
              if (this.config.delete_method === 'delete') {
                this.$delete(this.commentsAnswer, this.deleteComment['path']);
              }
            }

            this.count = response.data.count;
            this.deleteComment = {};
          }
        });
      },
      dropdownToggle: function() {
        this.dropdown = this.dropdown ? false : true;
      },
      dropdownClose: function() {
        this.dropdown = false;
      },
      emojiBoxToggle: function() {
        this.emojiBox = this.emojiBox ? false : true;
      },
      actionSort: function(sort) {
        if (sort === this.sort) {
          return false;
        }

        this.$root.wait = true;

        axios.post(this.apiPath, {
          action: 'getComments',
          url: this.url,
          bindId: this.bindId,
          sort: sort,
          listId: 0
        }).then(response => {
          this.$root.wait = false;

          this.sort = sort;
          localStorage.setItem('commentSort', sort);

          this.commentsMain = response.data.main;
          this.commentsAnswer = response.data.answer;
        });
      },
      actionMoreComment: function() {
        this.$root.wait = true;

        axios.post(this.apiPath, {
          action: 'getComments',
          url: this.url,
          bindId: this.bindId,
          sort: this.sort,
          listId: this.listId + 1
        }).then(response => {
          this.$root.wait = false;

          if (response.data.main.length === 0) {
            return false;
          }

          this.listId++;

          for (const key in response.data.main) {
            if (Object.hasOwnProperty.call(response.data.main, key)) {
              const element = response.data.main[key];
              this.commentsMain.push(element);
            }
          }

          Object.assign(this.commentsAnswer, response.data.answer);
        });
      }
    },
    watch: {
      mainText: function(val) {
        localStorage.setItem('main-' + this.url, val);
      }
    }
  })
</script>