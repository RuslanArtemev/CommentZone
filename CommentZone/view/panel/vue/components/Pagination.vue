<template>
  <nav v-if="countItems > 0 && countItems > limitItems">
    <ul class="pagination m-0">
      <li class="page-item">
        <span class="page-link" @click="pageIndexMinus">
          <span aria-hidden="true">&laquo;</span>
        </span>
      </li>
      <li v-for="(item, index) in buttons" :key="index" class="page-item" :class="activeButton === index ? 'active' : ''">
        <span class="page-link" @click="loadComment(index)">{{ item + 1 }}</span>
      </li>
      <li class="page-item">
        <span class="page-link" @click="pageIndexPlus">
          <span aria-hidden="true">&raquo;</span>
        </span>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: "Pagination",
  data() {
    return {
      ci: 0,
      cb: 0,
      buttons: [],
      activeButton: 0,
    };
  },
  props: ["countItems", "limitItems", "pageKey", "countButtons"],
  created() {
    this.create();
  },
  methods: {
    create() {
      (this.activeButton = 0), (this.ci = Math.ceil(this.countItems / this.limitItems));
      this.cb = this.countButtons % 2 === 0 ? this.countButtons - 1 : this.countButtons;

      let countButtonsView = this.cb < this.ci ? this.cb : this.ci;
      
      this.buttons = [];
      for (let i = 0; i < countButtonsView; i++) {
        this.buttons[i] = i;
      }
    },
    loadComment(i) {
      if (this.buttons[i] !== undefined) {
        this.activeButton = i;
        let cbHalf = Math.floor(this.cb / 2);
        let countRight = this.activeButton - cbHalf;
        let countLeft = cbHalf - this.activeButton;

        if (this.activeButton - cbHalf > 0) {
          for (let index = 0; index < countRight; index++) {
            if (this.buttons[this.buttons.length - 1] + 1 < this.ci) {
              this.buttons.push(this.buttons[this.buttons.length - 1] + 1);
              this.buttons.shift();
              this.activeButton--;
            }
          }
        } else if (this.activeButton - cbHalf < 0) {
          for (let index = 0; index < countLeft; index++) {
            if (this.buttons[0] > 0) {
              this.buttons.unshift(this.buttons[0] - 1);
              this.buttons.pop();
              this.activeButton++;
            }
          }
        }
      }

      this.$emit("click", this.buttons[this.activeButton]);
    },
    pageIndexPlus() {
      if (this.buttons[this.activeButton + 1] !== undefined && this.activeButton < this.ci) {
        this.loadComment(this.activeButton + 1);
      }
    },
    pageIndexMinus() {
      if (this.buttons[this.activeButton - 1] !== undefined && this.activeButton > 0) {
        this.loadComment(this.activeButton - 1);
      }
    },
  },
  watch: {
    countItems() {
      this.create();
    },
    limitItems() {
      this.create();
    },
  },
};
</script>

<style lang="scss" scoped>
.page-item {
  .page-link {
    background-color: $buttons;
    border-color: $block;
    color: $light;
    -ms-user-select: none;
    -moz-user-select: none;
    -khtml-user-select: none;
    -webkit-user-select: none;
    user-select: none;
    cursor: pointer;
    &:hover {
      background-color: $buttonsHover;
    }
  }
  &.active .page-link {
    background-color: lighten($buttons, 10%);
    color: $light;
  }
}
</style>