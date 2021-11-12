export default {
  install(Vue, options) {
    Vue.prototype.printf = function (str, replace) {
      if (typeof replace === 'string' || typeof replace === 'number') {
        replace = [replace];
      }

      let index = 0;
      let newStr = str.replace(/%d|%s/g, function () {
        return replace[index++];
      });

      return newStr;
    };

    Vue.prototype.cssExists = function (node, element) {
      if (node) {
        if (node.classList && Object.values(node.classList).indexOf(element) !== -1) {
          return true;
        } else {
          return this.cssExists(node.parentNode, element);
        }
      } else {
        return false;
      }
    };

    Vue.directive('menuHorizontal', {
      inserted: function (el, binding, vnode) {
        const translateElement = () => {
          let isMouseDown = false;
          let widthElement = 0;
          let widthParent = 0;
          let mouseStart = 0;
          let mouseLR = 0;
          let translate = 0;
          let diff = 0;
          let pageX = 0;

          el.style.transform = "translateX(" + translate + "px)";

          const ActionMouseDown = (e) => {
            if ((e.type === "mousedown" && e.buttons === 1) || e.type === "touchstart") {
              const styleElement = getComputedStyle(el);
              const styleParent = getComputedStyle(el.parentElement);
              widthElement = el.clientWidth - parseFloat(styleElement.paddingLeft) - parseFloat(styleElement.paddingRight);
              widthParent = el.parentElement.clientWidth - parseFloat(styleParent.paddingLeft) - parseFloat(styleParent.paddingRight);
              diff = widthElement > widthParent ? widthElement - widthParent : 0;

              if (e.type === "mousedown") {
                pageX = e.pageX;
              }
              if (e.type === "touchstart") {
                let touchobj = e.changedTouches[0];
                pageX = parseInt(touchobj.pageX);

                document.addEventListener("touchend", ActionMouseUp);
                document.addEventListener("touchmove", ActionMouseMove);
              }

              el.parentElement.onselectstart = function () {
                return false;
              };

              isMouseDown = true;
              if (translate === 0) {
                mouseStart = pageX;
              } else {
                mouseStart = pageX - translate;
              }
            }
          };
          const ActionMouseUp = (e) => {
            isMouseDown = false;
            const rectElement = el.getBoundingClientRect();
            const rectParent = el.parentElement.getBoundingClientRect();

            if (rectElement.left > rectParent.left) {
              translate = 0;
              el.style.transform = "translateX(" + translate + "px)";
            }
            if (rectElement.right < rectParent.right) {
              translate = -diff;
              el.style.transform = "translateX(" + translate + "px)";
            }

            document.removeEventListener("touchend", ActionMouseUp);
            document.removeEventListener("touchmove", ActionMouseMove);
          };
          const ActionMouseMove = (e) => {
            if (isMouseDown && widthElement > widthParent) {
              if (e.type === "mousemove") {
                pageX = e.pageX;
              }
              if (e.type === "touchmove") {
                const touchobj = e.changedTouches[0];
                pageX = parseInt(touchobj.pageX);
              }

              mouseLR = mouseStart - pageX;

              translate = -mouseLR;

              el.style.transform = "translateX(" + translate + "px)";
            }
          };

          el.addEventListener("touchstart", ActionMouseDown);
        };

        translateElement();
        window.addEventListener("resize", translateElement);
      },
    });

    Vue.directive('menuHidden', {
      inserted: function (el, binding, vnode) {
        const menuHidden = () => {
          const metricsParent = el.parentElement.getBoundingClientRect();

          if (el.children.length > 0) {
            for (const key in el.children) {
              if (Object.hasOwnProperty.call(el.children, key)) {
                const element = el.children[key];
                const metricsElement = element.getBoundingClientRect();
                const menuHiddenItem = el.parentElement.nextSibling.nextSibling.querySelector('.' + element.getAttribute('data-menu-class'));

                if (metricsElement.right > metricsParent.right) {
                  element.style.visibility = 'hidden';
                  menuHiddenItem.classList.remove('d-none');
                } else {
                  element.style.visibility = null;
                  menuHiddenItem.classList.add('d-none');
                }
              }
            }
          }
        }

        menuHidden();
        window.addEventListener('resize', menuHidden)
      }
    });
  }
};