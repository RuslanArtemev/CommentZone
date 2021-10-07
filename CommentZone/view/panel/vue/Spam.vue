<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.spam_list }}</div>

    <div class="row">
      <div class="col-xl-4 mb-3">
        <form>
          <div class="bg-box p-3">
            <div class="">
              <textarea
                v-model="addSpam"
                class="form-control"
                :class="error.spam ? 'is-invalid' : ''"
                @input="error.spam = ''"
                rows="3"
                :placeholder="$store.state.language.spam_text"
              ></textarea>
              <div class="invalid-feedback">{{ error.spam ? error.spam : "" }}</div>
            </div>

            <div class="row">
              <div class="col mt-3 text-end">
                <button type="button" class="btn btn-dark-blue shadow-sm mx-1" @click="saveSpam">
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
      <div class="col-xl-8">
        <div v-if="spam.length > 0" class="bg-box p-2">
          <div class="row py-2 mx-0 mb-2" v-for="(item, key) in spam" :key="key">
            <div class="col-9 mt-2">
              <div class="row">
                <div class="col">{{ item.text }}</div>
                <div class="col-md-3 text-pale fs-small">{{ item.ip }}</div>
                <div class="col-md-3 text-pale fs-small">{{ item.date_create }}</div>
              </div>
            </div>
            <div class="col-3 mt-4 mt-md-2 text-end">
              <button type="button" class="btn btn-sm btn-dark-blue shadow-sm" @click="deleteSpam(key)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
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
        </div>
        <div v-else class="fs-3 text-center text-pale">{{ $store.state.language.empty }}</div>
      </div>
    </div>
    <pagination class="my-3" :countItems="count" :limitItems="limit" pageKey="p" countButtons="8" @click="getSpam($event)"></pagination>
  </div>
</template>

<script>
export default {
  data() {
    return {
      spam: [],
      addSpam: "",
      count: 0,
      error: {
        spam: "",
      },
    };
  },
  created() {
    this.getCountSpam();
    this.getSpam();
  },
  computed: {
    limit() {
      return JSON.parse(localStorage.getItem("limitSpam")) || 50;
    },
  },
  methods: {
    deleteSpam(key) {
      if (!this.spam[key]["id"]) {
        return false;
      }

      axios.post(this.$store.state.apiPath, { action: "deleteSpam", id: this.spam[key]["id"] }).then((response) => {
        if (response.data === true) {
          this.spam.splice(key, 1);
        }
      });
    },
    saveSpam() {
      if (this.addSpam === "") {
        this.error.spam = this.$store.state.language.empty_field;
        return false;
      }

      axios.post(this.$store.state.apiPath, { action: "setSpam", spam: this.addSpam }).then((response) => {
        if (response.data === "spam_exists") {
          this.error.spam = this.$store.state.language.text_exists;
          return false;
        }

        if (response.data > 0) {
          this.spam.unshift({
            id: response.data,
            text: this.addSpam,
            date_create: this.$store.state.language.just_now,
          });
          this.addSpam = "";
        }
      });
    },
    getCountSpam() {
      axios.post(this.$store.state.apiPath, { action: "getCountSpam" }).then((response) => {
        this.count = response.data;
      });
    },
    getSpam(listId = 0) {
      axios.post(this.$store.state.apiPath, { action: "getSpam", limit: this.limit, listId }).then((response) => {
        this.spam = response.data;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
</style>