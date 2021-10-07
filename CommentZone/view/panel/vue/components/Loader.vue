<template>
  <div class="position-fixed bottom-0 end-0 p-3" style="width: 200px; z-index: 1051">
    <div class="toast show align-items-center text-white bg-primary border-0">
      <div class="d-flex">
        <div class="toast-body">
          <span class="spinner-border spinner-border-sm"></span>
          <span>{{ $store.state.language.loading }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {};
</script>

<style lang="scss" scoped>
</style>