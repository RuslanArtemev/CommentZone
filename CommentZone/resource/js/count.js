function CommentCount() {
  this.contain = '';

  this.getCount = function (urlApi) {
    document.addEventListener('DOMContentLoaded', () => {
      var params = {
        url: [],
        bindId: [],
      };

      var items = document.querySelectorAll('.' + this.contain);

      for (var i = 0; i < items.length; i++) {
        var bindId = items[i].getAttribute('data-bind-id');
        var url = items[i].getAttribute('data-url');
        if (bindId) {
          params.bindId.push(bindId);
        } else if (url) {
          url = url.replace(/(?:https?:\/\/)?(?:[^\/]+)?(.+)/g, `$1`);
          params.url.push(url);
        }
      }

      var commentZone = document.getElementById(this.contain),
        xhr = new XMLHttpRequest(),
        body = JSON.stringify({ action: 'getCountList', params: params });

      xhr.open('POST', urlApi, true);
      xhr.responseType = 'json';
      xhr.send(body);
      xhr.onload = function () {
        if (xhr.status === 200) {
          var data = xhr.response;

          for (var i = 0; i < items.length; i++) {
            var bindId = items[i].getAttribute('data-bind-id');
            var url = items[i].getAttribute('data-url');
            if (bindId) {
              items[i].innerText = data.bindId[bindId];
            } else if (url) {
              items[i].innerText = data.url[url];
            }
          }
        }
      }
    });
  }
}
