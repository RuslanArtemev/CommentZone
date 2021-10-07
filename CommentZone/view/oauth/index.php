<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $language['authorize']; ?></title>
  <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <style>
    body {
      font-family: "Segoe UI", Arial, Helvetica, sans-serif;
      color: #212529;
    }

    .form-box {
      width: 50%;
      margin: auto;
    }

    .form-input {
      margin-bottom: 18px;
    }

    label {
      padding-bottom: 2px;
      display: inline-block;
    }

    input {
      width: 100%;
      padding: 10px;
      box-sizing: border-box;
      border-radius: 4px;
      border: 1px solid #ced4da;
    }

    input:focus {
      outline: 0;
      box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%);
    }

    .is-invalid {
      border-color: red;
    }

    .invalid-feedback {
      display: none;
      color: red;
      font-size: small;
    }

    .is-invalid~.invalid-feedback {
      display: block;
    }

    button {
      padding: 10px;
      border: 1px solid transparent;
      background: #6684a2;
      color: #fff;
      border-radius: 4px;
      font-size: 15px;
    }

    .name {
      font-size: 20px;
      font-weight: 400;
    }

    .email {
      font-size: 14px;
      color: #959ca2;
    }

    .text-moted {
      color: #959ca2;
    }

    .font-small {
      font-size: 14px;
    }

    .text-button {
      color: #2f93f7;
      text-decoration: underline;
      cursor: pointer;
    }

    .text-lowercase {
      text-transform: lowercase;
    }
  </style>
</head>

<body>
  <div id="oauth">

    <form class="form-box">
      <div class="form-input">
        <div class="name">{{ form.name }}</div>
        <div class="email" v-if="socAuth !== 'email-empty'">{{form.email}}</div>
      </div>

      <div class="form-input" v-if="typeForm === 'accaunt-not-found'">
        <div class="text-moted font-small">
          <p>{{ language.account_not_found }}</p>
          <p>{{ language.reason }}: {{ language.social_not_linked }}, {{ language.not_get_email }}</p>
          <p v-html="printf(language.actions_unsuccessful_authorization, authRegButtons)"></p>
        </div>
      </div>

      <div v-if="typeForm === 'registration'">
        <div class="form-input" v-if="socAuth === 'email-empty'">
          <label for="email">{{ language.email }}</label>
          <input type="email" name="email" id="email" :class="error.email ? 'is-invalid' : ''" @input="error.email = ''" v-model="form.email">
          <div class="invalid-feedback">{{ error.email }}. <span v-if="emailAuthMessage">{{ language.want }} <span class="text-button text-lowercase" @click="actionTypeForm('login-email')">{{ language.sign_in }}</span>?<span></div>
        </div>
        <div class="form-input">
          <label for="password1">{{ language.password }}</label>
          <input type="password" name="password1" id="password1" :class="error.password1 ? 'is-invalid' : ''" @input="error.password1 = ''" v-model="form.password1">
          <div class="invalid-feedback">{{ error.password1 }}</div>
        </div>
        <div class="form-input">
          <label for="password2">{{ language.confirm_password }}</label>
          <input type="password" name="password2" id="password2" :class="error.password2 ? 'is-invalid' : ''" @input="error.password2 = ''" v-model="form.password2">
          <div class="invalid-feedback">{{ error.password2 }}</div>
        </div>
        <div class="form-input">
          <button type="button" @click="sendReg()">{{ language.continue }}</button>
        </div>
      </div>

      <div v-if="typeForm === 'login-email'">
        <div class="form-input">
          <label for="email">{{ language.email }}</label>
          <input type="email" name="email" id="email" :class="error.email ? 'is-invalid' : ''" @input="error.email = ''" v-model="form.email">
          <div class="invalid-feedback">{{ error.email }}</div>
        </div>
        <div class="form-input">
          <label for="password1">{{ language.password }}</label>
          <input type="password" name="password1" id="password1" :class="error.password1 ? 'is-invalid' : ''" @input="error.password1 = ''" v-model="form.password1">
          <div class="invalid-feedback">{{ error.password1 }}</div>
        </div>
        <div class="form-input">
          <button type="button" @click="sendLogin()">{{ language.continue }}</button>
        </div>
      </div>

      <div v-if="typeForm === 'login'">
        <div class="form-input">
          <label for="password1">{{ language.password }}</label>
          <input type="password" name="password1" id="password1" :class="error.password1 ? 'is-invalid' : ''" @input="error.password1 = ''" v-model="form.password1">
          <div class="invalid-feedback">{{ error.password1 }}</div>
        </div>
        <div class="form-input">
          <button type="button" @click="sendLogin()">{{ language.continue }}</button>
        </div>
      </div>
    </form>

  </div>

  <script>
    var oauth = new Vue({
      el: '#oauth',
      data: {
        csrfToken: '<?php echo isset($csrf['token']) ? $csrf['token'] : ''; ?>',
        authRegButtons: [],
        typeForm: '',
        emailAuthMessage: false,
        socAuth: '<?php echo $socAuth; ?>',
        config: <?php echo json_encode($config); ?>,
        language: <?php echo json_encode($language); ?>,
        form: {
          sid: '<?php echo $user['sid']; ?>',
          email: '<?php echo $user['email']; ?>',
          name: '<?php echo $user['name']; ?>',
          avatar: '<?php echo $user['avatar']; ?>',
          avatar: '<?php echo $user['avatar']; ?>',
          link: '<?php echo $user['link']; ?>',
          provider: '<?php echo $user['provider']; ?>',
          password1: '',
          password2: '',
        },
        error: {
          email: '',
          password1: '',
          password2: '',
        },
        errorMessage: '',
      },
      created() {
        axios.defaults.headers.common = {
          'Cz-Csrf-Token': this.csrfToken
        };
        if (this.socAuth == 1) {
          this.windowClose();
        }
        this.authRegButtons = [
          '<span id="authButton" class="text-button text-lowercase">\"' + this.language.sign_in + '\"</span>',
          '<span id="regButton" class="text-button text-lowercase">\"' + this.language.sign_up + '\"</span>'
        ];

        if (this.socAuth === 'email-not-exists') {
          this.actionTypeForm('registration');
        }
        if (this.socAuth === 'email-exists') {
          this.actionTypeForm('login');
        }
        if (this.socAuth === 'email-empty') {
          this.actionTypeForm('accaunt-not-found');
        }
      },
      mounted() {
        let authButton = document.getElementById('authButton');
        let regButton = document.getElementById('regButton');
        
        if (authButton) {
          authButton.addEventListener('click', () => {
            this.actionTypeForm("login-email");
          });
        }
        if (regButton) {
          regButton.addEventListener('click', () => {
            this.actionTypeForm("registration")
          });
        }
      },
      methods: {
        printf(str, replace) {
          if (typeof replace === 'string' || typeof replace === 'number') {
            replace = [replace];
          }

          let index = 0;
          let newStr = str.replace(/%d|%s/g, function() {
            return replace[index++];
          });

          return newStr;
        },
        actionTypeForm(type) {
          this.typeForm = type;
          this.error = {
            email: '',
            password1: '',
            password2: '',
          };
          this.errorMessage = '';
        },
        sendReg() {
          if (!this.validateEmail(this.form.email, true)) {
            this.error.email = this.errorMessage;
            return false;
          }

          if (!this.validatePassword(this.form.password1, true)) {
            this.error.password1 = this.errorMessage;
            return false;
          }

          if (this.form.password1 !== this.form.password2) {
            this.error.password2 = this.language.different_passwords;
            return false;
          }

          axios.post(this.config.api_path, {
            action: 'oauthReg',
            data: this.form
          }).then((response) => {
            if (response.data.auth === true) {
              this.windowClose();
            }
            if (response.data === 'email-exists') {
              this.error.email = this.language.email_exists;
              this.emailAuthMessage = true;
            }
          })
        },
        sendLogin() {
          if (!this.validatePassword(this.form.password1, true)) {
            this.error.password1 = this.errorMessage;
            return false;
          }

          axios.post(this.config.api_path, {
            action: 'oauthLogin',
            data: this.form
          }).then((response) => {
            if (response.data.auth === true) {
              this.windowClose();
            }
            if (response.data === 'email-not-exists') {
              this.error.email = this.language.email_not_exists;
            }
            if (response.data === 'error-data-authorize') {
              this.error.email = this.language.invalid_data;
              this.error.password1 = this.language.invalid_data;
            }
          })
        },
        validateEmail: function(email, require = false) {
          if (require) {
            if (email === '') {
              this.errorMessage = this.language.empty_field;
              return false;
            }
          }

          if (email !== '' && !/^[^@]+@[^@\.]+\.[^@\.]+$/g.test(email)) {
            this.errorMessage = this.language.invalid_email;
            return false;
          }

          return true;
        },
        validatePassword: function(password, require = false) {
          if (require) {
            if (password === '') {
              this.errorMessage = this.language.empty_field;
              return false;
            }
          }

          if (password !== '') {
            if (!/^[\wа-яА-ЯёЁ%*)?@#$~]+$/g.test(password)) {
              this.errorMessage = this.language.only_letters_numbers_signs;
              return false;
            } else if (password.length < this.config.minPass) {
              this.errorMessage = this.printf(this.language.min_characters, this.config.minPass);
              return false;
            } else if (password.length > this.config.maxPass) {
              this.errorMessage = this.printf(this.language.max_characters, this.config.maxPass);
              return false;
            }
          }

          return true;
        },
        windowClose() {
          window.opener.document.location.reload();
          window.close()
        },
      }
    })
  </script>
</body>

</html>