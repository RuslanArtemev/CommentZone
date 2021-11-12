<template>
  <div v-if="images && images[selected]" class="cz-images-view-block">
    <div class="cz-images-view-close rounded row align-items-center" @click="close">
      <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi-x" viewBox="0 0 16 16">
        <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
      </svg>
    </div>
    <div class="row h-100">
      <div class="col-2 align-self-center">
        <div class="cz-images-view-btn mx-2 my-3 rounded row align-items-center" @click="minus">
          <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi-chevron-compact-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z"/>
          </svg>
        </div>
      </div>
      <div class="col-8 p-0 text-center modal-dialog-centered justify-content-center">
        <img v-if="images[selected].type === 'image'" class="m-auto" v-imageSize :src="$store.state.config.resource + images[selected].long" alt="" />
        <iframe v-if="images[selected].type === 'video'" class="m-auto" :width="images[selected].resource === 'tiktok' ? '325' : '100%'" :height="images[selected].resource === 'tiktok' ? '576' : '75%'" :src="images[selected].link" frameborder="0" allowfullscreen=""></iframe>
      </div>
      <div class="col-2 align-self-center">
        <div class="cz-images-view-btn mx-2 my-3 rounded cz-right row align-items-center" @click="plus">
          <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="currentColor" class="bi-chevron-compact-right" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z"/>
          </svg>
        </div>
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

@media (max-width: 575.98px) {
  #commentzone {
    .cz-images-view-block {
      .cz-images-view-btn {
        width: 100%;
      }
    }
  }
}

</style>