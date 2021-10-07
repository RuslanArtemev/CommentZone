<template>
  <div v-if="images && images[selected]" class="cz-images-view-block">
    <div class="cz-images-view-close rounded" @click="close">×</div>
    <div class="row h-100">
      <div class="col-2 align-self-center">
        <div class="cz-images-view-btn mx-2 my-3 rounded" @click="minus">&lang;</div>
      </div>
      <div class="col-8 p-0 text-center modal-dialog-centered">
        <img v-if="images[selected].type === 'image'" class="m-auto" v-imageSize :src="$store.state.config.resource + images[selected].long" alt="" />
        <iframe v-if="images[selected].type === 'video'" class="m-auto" :width="images[selected].resource === 'tiktok' ? '325' : '100%'" :height="images[selected].resource === 'tiktok' ? '576' : '75%'" :src="images[selected].link" frameborder="0" allowfullscreen=""></iframe>
      </div>
      <div class="col-2 align-self-center">
        <div class="cz-images-view-btn mx-2 my-3 rounded cz-right" @click="plus">&rang;</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data: function () {
    return {
      selected: 0,
    };
  },
  props: ["images", "index"],
  directives: {
    imageSize: {
      inserted: function (el) {
        function resizeImage(width, height) {
          if (el.parentElement.offsetWidth < width || window.innerHeight < height) {
            var ratio = width / height;
            var w = window.innerHeight * ratio;
            var h = el.parentElement.offsetWidth / ratio;

            if (w / ratio > h) {
              w = h * ratio;
            } else {
              h = w / ratio;
            }

            el.width = w;
            el.height = h;
          } else {
            el.width = width;
            el.height = height;
          }
        }

        var width;
        var height;

        var img = new Image();
        img.src = el.src;
        img.onload = function () {
          width = this.width;
          height = this.height;

          resizeImage(width, height);
        };

        window.addEventListener(
          "resize",
          function () {
            resizeImage(width, height);
          },
          true
        );
      },
      update: function (el) {
        function resizeImage(width, height) {
          if (el.parentElement.offsetWidth < width || window.innerHeight < height) {
            var ratio = width / height;
            var w = window.innerHeight * ratio;
            var h = el.parentElement.offsetWidth / ratio;

            if (w / ratio > h) {
              w = h * ratio;
            } else {
              h = w / ratio;
            }

            el.width = w;
            el.height = h;
          } else {
            el.width = width;
            el.height = height;
          }
        }

        el.width = 0;
        el.height = 0;
        var width;
        var height;

        var img = new Image();
        img.src = el.src;
        img.onload = function () {
          width = this.width;
          height = this.height;

          resizeImage(width, height);
        };

        window.addEventListener(
          "resize",
          function () {
            resizeImage(width, height);
          },
          true
        );
      },
    },
  },
  created() {
    this.selected = this.index;
  },
  methods: {
    minus: function () {
      if (this.images[this.selected - 1]) {
        this.selected--;
      } else {
        this.selected = this.images.length - 1;
      }
    },
    plus: function () {
      if (this.images[this.selected + 1]) {
        this.selected++;
      } else {
        this.selected = 0;
      }
    },
    close: function () {
      this.$emit("click", "close");
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .cz-images-view-block {
    width: 100%;
    height: 100%;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #00000066;
    z-index: 999;
    .cz-images-view-panel-control {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      .cz-btn {
        font-size: 36px;
        line-height: 1.45;
        width: 60px;
        height: 60px;
        background: #101010;
        color: #fff;
        text-align: center;
        cursor: pointer;
        user-select: none;
      }
    }
    .cz-images-view-close {
      font-size: 34px;
      line-height: 1.55;
      width: 60px;
      height: 60px;
      background: #101010;
      color: #fff;
      text-align: center;
      position: absolute;
      right: 0;
      top: 0;
      margin: 10px;
      cursor: pointer;
      user-select: none;
      opacity: 0.5;
      &:hover {
        opacity: 1;
      }
    }
    .cz-images-view-btn {
      margin: 0 8%;
      font-size: 30px;
      line-height: 1.75;
      width: 60px;
      height: 60px;
      background: #101010;
      color: #fff;
      text-align: center;
      cursor: pointer;
      user-select: none;
      opacity: 0.5;
      &:hover {
        opacity: 1;
      }
      &.cz-right {
        float: right;
      }
    }
  }
}
</style>