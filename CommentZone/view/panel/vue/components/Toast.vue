<template>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 5">
    <div class="toast show" :class="bg">
      <div class="toast-header">
        <strong class="me-auto text-white">{{ $store.state.language.system_message }}</strong>
        <button type="button" class="close ml-2 mb-1">
          <span class="text-white" @click="close">&times;</span>
        </button>
      </div>
      <div class="toast-body text-white">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  props: ["bg", "message"],
  created() {
    setTimeout(() => {
      this.close();
    }, 5000);
  },
  methods: {
    close() {
      this.$emit("close", true);
    },
  },
};
</script>

<style lang="scss" scoped>
.close {
  float: right;
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
  padding: 0;
  background-color: transparent;
  border: 0;
}
.toast-header {
  background-color: transparent !important;
  border: none !important;
}
</style>