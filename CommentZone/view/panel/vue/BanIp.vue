<template>
  <div class="p-0 ms-1">
    <div class="text-pale fs-4 mb-4 border-bottom border-pale pb-2">{{ $store.state.language.banned_ip }}</div>

    <div class="row">
      <div class="col-xl-4 mb-3">
        <form>
          <div class="bg-box p-3">
            <div class="mb-3">
              <input type="text" class="form-control" :class="error.ip ? 'is-invalid' : ''" v-model.trim="addIp.ip" placeholder="0.0.0.0" />
              <div class="invalid-feedback">{{ error.ip ? error.ip : "" }}</div>
            </div>
            <div class="">
              <textarea class="form-control" rows="3" v-model="addIp.note" :placeholder="$store.state.language.note"></textarea>
            </div>

            <div class="row">
              <div class="col-2 fs-small" :class="countNote > 255 ? 'text-danger' : 'text-pale'">{{ countNote }}/255</div>
              <div class="col-10 mt-3 text-end">
                <button type="button" class="btn btn-dark-blue shadow-sm mx-1" @click="saveBanIp">
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
        <div class="row mb-4">
          <div class="col">
            <div class="input-group">
              <span class="input-group-text bg-dark-blue border-pale text-pale text-uppercase">{{ $store.state.language.ip }}</span>
              <input type="text" class="form-control" v-model="filters.ip" @change="getBanIp()" @keyup.enter="getBanIp()" />
            </div>
          </div>
        </div>
        <div v-if="ip.length > 0" class="bg-box p-2">
          <div class="row py-2 mx-0 mb-2" v-for="(item, key) in ip" :key="key">
            <div class="col-9 mt-2">
              <div class="row">
                <div class="col-md-3">{{ item.ip }}</div>
                <div class="col-md-6 text-pale fs-small">{{ item.note ? item.note : "-" }}</div>
                <div class="col-md-3 text-pale fs-small">{{ item.date_create }}</div>
              </div>
            </div>
            <div class="col-3 mt-4 mt-md-2 text-end">
              <button type="button" class="btn btn-sm btn-dark-blue shadow-sm" @click="unbanIp(key)">
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
    <pagination class="my-3" :countItems="count" :limitItems="limit" pageKey="p" countButtons="8" @click="getBanIp($event)"></pagination>
  </div>
</template>

<script>
export default {
  data() {
    return {
      ip: [],
      count: 0,
      addIp: {
        ip: "",
        note: "",
      },
      countNote: 0,
      filters: {
        ip: localStorage.getItem("filters-ip-BanIp") || '',
      },
      error: {
        ip: "",
      },
    };
  },
  created() {
    this.getBanIp();
  },
  computed: {
    limit() {
      return JSON.parse(localStorage.getItem("limitBanIp")) || 50;
    },
    getFilters() {
      let filters = {
        ip: this.filters.ip ? this.filters.ip : false,
      };

      return filters;
    },
  },
  methods: {
    saveBanIp() {
      if (this.addIp.ip === "") {
        this.error.ip = this.$store.state.language.empty_field;
        return false;
      } else if (!/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/g.test(this.addIp.ip)) {
        this.error.ip = this.$store.state.language.invalid_format;
        return false;
      } else if (this.countNote > 255) {
        this.error.note = this.$store.state.language.long_text;
        return false;
      }

      axios.post(this.$store.state.apiPath, { action: "setBanIp", ip: this.addIp.ip, note: this.addIp.note }).then((response) => {
        if (response.data === "ip_exists") {
          this.error.ip = this.$store.state.language.ip_blocked;
          return false;
        }

        if (response.data === true) {
          this.addIp.date_create = this.$store.state.language.just_now;
          this.ip.unshift(this.addIp);
          this.addIp = {
            ip: "",
            note: "",
          };
        }
      });
    },
    unbanIp(key) {
      axios.post(this.$store.state.apiPath, { action: "unbanIp", id: this.ip[key]["id"] }).then((response) => {
        if (response.data === true) {
          this.ip.splice(key, 1);
        }
      });
    },
    getCountBanIp() {
      axios.post(this.$store.state.apiPath, { action: "getCountBanIp", filters: this.getFilters }).then((response) => {
        this.count = response.data;
      });
    },
    getBanIp(listId = 0) {
      axios.post(this.$store.state.apiPath, { action: "getBanIp", limit: this.limit, listId, filters: this.getFilters }).then((response) => {
        this.ip = response.data;
        this.getCountBanIp();
      });
    },
  },
  watch: {
    'filters.ip': function (val) {
      localStorage.setItem("filters-ip-BanIp", val);
    },
    "addIp.ip": function () {
      this.error.ip = "";
    },
    "addIp.note": function () {
      this.countNote = this.addIp.note.length;
    },
  },
};
</script>

<style lang="scss" scoped>
</style>