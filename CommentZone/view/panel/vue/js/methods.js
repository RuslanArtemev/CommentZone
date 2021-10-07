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
    }

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
    }
  }
};