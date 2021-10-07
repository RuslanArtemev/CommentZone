function CommentZone() {
  this.url = document.location.pathname + document.location.search;
  this.bindId = '';
  this.contain = '';

  this.connect = function (urlApi) {
    var commentScript = document.getElementById(this.contain),
      xhr = new XMLHttpRequest(),
      body = JSON.stringify({ action: 'getComment', url: this.url, bind_id: this.bindId });

    xhr.open('POST', urlApi, true);
    xhr.responseType = 'document';
    xhr.send(body);
    xhr.onload = function () {
      if (xhr.status === 200) {
        var scripts = xhr.response.querySelectorAll('script');
        var headScripts = [];
        var bodyScripts = [];

        for (var index = 0; index < scripts.length; index++) {
          var element = scripts[index];
          var script = document.createElement("script");
          script.text = element.innerHTML;

          for (var i = element.attributes.length - 1; i >= 0; i--) {
            var attributes = element.attributes[i];
            script.setAttribute(attributes.name, attributes.value);
          }

          if (element.attributes.src) {
            headScripts.push(script);
          } else {
            bodyScripts.push(script);
          }
        }

        for (var index = 0; index < headScripts.length; index++) {
          var script = headScripts[index];
          document.head.insertAdjacentElement('beforeend', script);
        }

        var loadVue = function () {
          setTimeout(function () {
            if (typeof Vue !== 'undefined') {
              commentScript.insertAdjacentHTML('afterbegin', xhr.response.querySelector('#CommentZone').outerHTML);
              for (var index = 0; index < bodyScripts.length; index++) {
                var script = bodyScripts[index];
                commentScript.insertAdjacentElement('afterend', script);
              }
            } else {
              loadVue();
            }
          }, 100);
        }
        
        loadVue()
      }
    }
  }
}
