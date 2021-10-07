<template>
  <div v-if="$store.state.user.role === 'admin'" class="p-0">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.languages }}</div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <form>
          <div
            class="row bg-box mx-0 mb-2 pointer"
            :class="languageName === key ? 'active' : ''"
            v-for="(item, key) in languages"
            :key="key"
          >
            <div class="col-md-9 col-10 px-3 py-2" @click="selectLanguage(key)">{{ key }}</div>
            <div class="col-md-3 col-2 px-3 py-2 text-end">
              <div class="d-inline-block btn-dark rounded" @click="actionDeleteLanguage(key)">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" viewBox="0 0 16 18">
                  <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-box p-2 mb-2 pointer" v-for="(item, key) in addLanguage" :key="'A' + key">
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                :class="error.addLanguage ? 'is-invalid' : ''"
                @input="error.addLanguage = ''"
                v-focus
                v-model.trim="addLanguage[key]"
                placeholder="Name"
              />
              <button class="btn btn-dark" type="button" @click="approveAddedLanguage(key)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
                  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                </svg>
              </button>
              <button class="btn btn-dark" type="button" @click="disapproveAddedLanguage(key)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                  <path
                    d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                  />
                </svg>
              </button>
            </div>
            <div class="invalid-feedback" :class="error.addLanguage ? 'd-block' : ''">{{ error.addLanguage ? error.addLanguage : "" }}</div>
          </div>

          <div class="d-grid gap-2 mb-3">
            <button class="btn btn-blue text-white shadow-sm" type="button" @click="actionAddLanguage">
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

          <div class="bg-box p-3">
            <div class="mb-3">
              <input
                type="text"
                class="form-control"
                :class="error.addItem ? 'is-invalid' : ''"
                @input="error.addItem = ''"
                v-model.trim="addLanguageItem.key"
                :placeholder="$store.state.language.key"
              />
              <div class="invalid-feedback">{{ error.addItem ? error.addItem : "" }}</div>
            </div>
            <div class="">
              <textarea class="form-control" rows="3" v-model="addLanguageItem.text" :placeholder="$store.state.language.text"></textarea>
            </div>

            <div class="row">
              <div class="col mt-3 text-end">
                <button type="button" class="btn btn-dark-blue shadow-sm mx-1" @click="addItem()">
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
          </div>
        </form>
      </div>

      <div class="col-md-8">
        <div class="row mb-1">
          <div class="col-md-6 mb-2">
            <div class="input-group">
              <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.key }}</span>
              <input type="text" class="form-control" v-model="filters.key" />
            </div>
          </div>
          <div class="col-md-6 mb-2">
            <div class="input-group">
              <span class="input-group-text bg-dark-blue border-pale text-pale">{{ $store.state.language.text }}</span>
              <input type="text" class="form-control" v-model="filters.text" />
            </div>
          </div>
        </div>

        <div class="bg-box p-2">
          <form>
            <div v-for="(item, key) in languages[languageName]" :key="key">
              <div
                class="row py-2 mx-0 mb-2"
                v-if="
                  (!filters.key || new RegExp(filters.key, 'ig').test(key)) && (!filters.text || new RegExp(filters.text, 'ig').test(item))
                "
              >
                <div class="col mt-2">
                  <div class="row">
                    <div class="col-lg-9 col-xl-10">
                      <label :for="'itemLanguage' + key" class="form-label">{{ key }}</label>
                      <input type="text" class="form-control" :id="'itemLanguage' + key" v-model="languages[languageName][key]" />
                    </div>
                    <div class="col-lg-3 col-xl-2 pt-2 text-end">
                      <button type="button" class="btn btn-dark-blue shadow-sm" @click="deleteItem(key)">
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
                            d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"
                          />
                        </svg>
                        <span>{{ $store.state.language.delete }}</span>
                      </button>
                    </div>
                  </div>

                  <div class="row m-0 mt-1 pointer" :data-bs-target="'#id' + key" data-bs-toggle="collapse">
                    <div class="col mb-4 border-bottom border-pale"></div>
                    <div class="col-auto text-pale">
                      <div>{{ $store.state.language.in_other_languages }}</div>
                      <div class="text-center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          fill="currentColor"
                          class="bi bi-caret-down"
                          viewBox="0 0 16 16"
                        >
                          <path
                            d="M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.796 5.48a1 1 0 0 0 1.506 0l4.796-5.48c.566-.647.106-1.659-.753-1.659H3.204a1 1 0 0 0-.753 1.659z"
                          />
                        </svg>
                      </div>
                    </div>
                    <div class="col mb-4 border-bottom border-pale"></div>
                  </div>

                  <div class="collapse" :id="'id' + key">
                    <div class="px-3" style="font-size: 0.8rem">
                      <div v-for="(i, k) in languages" :key="'B' + k">
                        <div v-if="k !== languageName">
                          <span class="text-pale fw-bold">{{ k }}</span> : {{ i[key] }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
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
        <span>{{ $store.state.language.save }}</span>
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
      languageName: "",
      languages: [],
      addLanguage: [],
      deleteLanguage: [],
      addLanguageItem: {
        key: "",
        text: "",
      },
      filters: {
        key: "",
        text: "",
      },
      toast: {
        show: false,
        bg: "",
        message: "",
      },
      error: {
        language: "",
        addItem: "",
        addLanguage: "",
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
    if (this.$store.state.user.role !== "admin") {
      return false;
    }

    this.getLanguages();
  },
  methods: {
    toastShow: function (message, bg = "bg-primary") {
      this.toast.show = true;
      this.toast.bg = bg;
      this.toast.message = message;
    },
    selectLanguage(name) {
      this.languageName = name;
    },
    actionAddLanguage() {
      this.addLanguage.push("");
    },
    approveAddedLanguage(index) {
      if (this.languages[this.addLanguage[index]] !== undefined) {
        this.error.addLanguage = this.$store.state.language.language_exists;
        return false;
      }

      let cloneLanguages = Object.assign({}, this.languages[this.languageName]);
      this.$set(this.languages, this.addLanguage[index], cloneLanguages);
      this.languageName = this.addLanguage[index];
      this.addLanguage.splice(index, 1);
    },
    disapproveAddedLanguage(index) {
      this.addLanguage.splice(index, 1);
    },
    actionDeleteLanguage(index) {
      this.$delete(this.languages, index);
      this.deleteLanguage.push(index);

      if (this.languageName === index) {
        let langKey = Object.keys(this.languages);

        if (langKey.length > 0) {
          this.selectLanguage(langKey[0]);
        }
      }
    },
    addItem() {
      if (this.languages[this.languageName][this.addLanguageItem.key] !== undefined) {
        this.error.addItem = this.$store.state.language.key_exists;
        return false;
      }

      if (this.addLanguageItem.key === '') {
        this.error.addItem = this.$store.state.language.empty_field;
        return false;
      }

      for (const key in this.languages) {
        if (Object.hasOwnProperty.call(this.languages, key)) {
          const element = this.languages[key];
          this.$set(element, this.addLanguageItem.key, this.addLanguageItem.text);
        }
      }
      this.filters.key = this.addLanguageItem.key;
      this.addLanguageItem = {
        key: "",
        text: "",
      };
    },
    deleteItem(key) {
      for (const i in this.languages) {
        if (Object.hasOwnProperty.call(this.languages, i)) {
          const element = this.languages[i];
          this.$delete(element, key);
        }
      }
    },
    getLanguages() {
      axios.post(this.$store.state.apiPath, { action: "getLanguages" }).then((response) => {
        this.languageName = response.data.languageName;
        this.languages = response.data.languages;
      });
    },
    save() {
      axios
        .post(this.$store.state.apiPath, { action: "setLanguage", languages: this.languages, deleteItems: this.deleteLanguage })
        .then((response) => {
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
  background-color: #434e5a !important;
}
</style>