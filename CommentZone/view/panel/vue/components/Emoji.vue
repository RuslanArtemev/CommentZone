<template>
  <div class="col px-2 emoji-block">
    <div
      v-for="(item, key) in $store.state.config.emojiView"
      :key="key"
      class="emoji-element"
      @click="insertEmoji(key)"
    >
      <span
        class="emoji-item"
        :style="
          'background-image: url(' +
          $store.state.config.resource +
          '/img/emoji/emoji-many.png); background-position: -' +
          item['position'][0] +
          'px -' +
          item['position'][1] +
          'px;'
        "
      ></span>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {};
  },
  props: ["prefixId"],
  methods: {
    insertEmoji: function (key) {
      let el = this.$parent.$refs["text-" + this.prefixId];
      let start = el.selectionStart;
      let end = el.selectionEnd;
      let emoji = key;

      el.value = el.value.substring(0, start) + emoji + el.value.substring(end);
      el.focus();
      el.selectionEnd = start + emoji.length;

      this.$set(this.$parent.answer, this.prefixId, el.value);
      if (this.$parent.action === "answer") {
        localStorage.setItem(
          "answer-panel",
          JSON.stringify(this.$parent.answer)
        );
      }
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .emoji-block {
    min-width: 16rem;
    .emoji-element {
      padding: 2px 3px;
      margin: 1px;
      display: inline-block;
      border-radius: 10%;
      cursor: pointer;
      .emoji-item {
        width: 32px;
        height: 32px;
        display: inline-block;
        // background-image: url("../../img/emoji/emoji-many.png");
        background-size: 1344px;
        background-repeat: no-repeat;
        vertical-align: text-bottom;
      }
      &:hover {
        background-color: #797979;
      }
    }
  }
}
</style>