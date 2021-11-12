<template>
  <div id="commentzone">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: "Index",
  data() {
    return {};
  },
  created() {
    this.$store.dispatch("getConfig");
    this.getCurrentUser();
  },
  methods: {
    getCurrentUser() {
      axios.post(this.$store.state.apiPath, { action: "getCurrentUser" }).then((respons) => {
        if (respons.data.id === undefined || respons.data === "no_authorize") {
          if (this.$route.name !== "login") {
            this.$router.push("/login");
          }
        } else {
          this.$store.commit("changeUser", respons.data);
          if (this.$route.name === "login") {
            this.$router.push("/");
          }
        }
      });
    },
  },
  watch: {
    $route(to, from) {
      this.getCurrentUser();
    },
  },
};
</script>

<style lang="scss">

</style>