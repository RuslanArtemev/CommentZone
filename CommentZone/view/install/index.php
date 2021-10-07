<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <title>Comment Zone Install</title>
</head>

<body class="bg-light">

  <div id="install" class="container" :class="'d-block'" style="display: none;">
    <div class="p-5 col-sm-12 col-md-10 col-lg-8 m-auto bg-white">
      <div class="col-sm-12 col-md-11 col-lg-10 col-xl-9 m-auto">
        <div class="fs-3 pb-4 text-center">
          <div class="border-bottom pb-3 text-muted">{{ language.params.install_system }}</div>
        </div>

        <form v-if="beginShow">
          <div class="fs-5 pb-4">{{ language.params.start_install }}</div>

          <div class="mb-3">
            <label for="selectLanguage" class="form-label">{{ language.params.select_language }}</label>
            <select id="selectLanguage" class="form-select" v-model="language.current" @change="selectLanguages()">
              <option v-for="(lang, index) in language.list" :value="lang" :key="index">{{ lang }}</option>
            </select>
          </div>

          <button type="button" class="btn btn-primary mt-3" @click="start()">{{ language.params.next }}</button>
        </form>

        <form v-if="mysqlShow">
          <div class="fs-5 pb-4">{{ language.params.install_db_mysql }}</div>
          <div class="mb-3">
            <label for="dbHost" class="form-label">{{ language.params.db_host }}</label>
            <input v-model="mysql.host" type="text" class="form-control" id="dbHost">
          </div>
          <div class="mb-3">
            <label for="dbUser" class="form-label">{{ language.params.user }}</label>
            <input v-model="mysql.user" type="text" class="form-control" id="dbUser">
          </div>
          <div class="mb-3">
            <label for="dbPassword" class="form-label">{{ language.params.password }}</label>
            <input v-model="mysql.password" type="text" class="form-control" id="dbPassword">
          </div>
          <div class="mb-3">
            <label for="dbName" class="form-label">{{ language.params.db_name }}</label>
            <input v-model="mysql.base_name" @input="error.common = ''" type="text" class="form-control" id="dbName">
          </div>
          <div class="mb-3">
            <label for="dbPrefix" class="form-label">{{ language.params.prefix }}</label>
            <input v-model="mysql.prefix" type="text" class="form-control" id="dbPrefix">
          </div>

          <div v-if="error.common" class="alert alert-danger d-flex align-items-center mt-4" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
            <span>{{ error.common }}</span>
          </div>

          <button type="button" class="btn btn-primary mt-3" @click="setMysqlConfig()">{{ language.params.install_db }}</button>
        </form>

        <form v-if="createAdminShow">
          <div class="fs-5 pb-4">{{ language.params.create_admin }}</div>

          <div class="mb-3">
            <label for="adminName" class="form-label">{{ language.params.name }}</label>
            <input v-model="admin.name" @input="error.name = ''" type="text" class="form-control" :class="error.name ? 'is-invalid' : ''" id="adminName">
            <div v-if="error.email" class="invalid-feedback">{{ error.name }}</div>
          </div>

          <div class="mb-3">
            <label for="adminEmail" class="form-label">{{ language.params.email }}</label>
            <input v-model="admin.email" @input="error.email = ''" type="text" class="form-control" :class="error.email ? 'is-invalid' : ''" id="adminEmail">
            <div v-if="error.email" class="invalid-feedback">{{ error.email }}</div>
          </div>

          <div class="mb-3">
            <label for="adminPassword" class="form-label">{{ language.params.password }}</label>
            <input v-model="admin.password" @input="error.password = ''" type="password" class="form-control" :class="error.password ? 'is-invalid' : ''" id="adminPassword">
            <div v-if="error.password" class="invalid-feedback">{{ error.password }}</div>
          </div>

          <div v-if="error.common" class="alert alert-danger d-flex align-items-center mt-4" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
              <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </svg>
            <span>{{ error.common }}</span>
          </div>

          <button type="button" class="btn btn-primary mt-3" @click="createAdmin()">{{ language.params.create }}</button>
          <button v-if="adminExists" type="button" class="btn btn-primary mt-3" @click="skipAdmin()">{{ language.params.skip }}</button>
        </form>

        <div v-if="afterShow" class="text-center" style="color: #1fd6b4;">
          <div class="fs-4">{{ language.params.install_complite }}</div>
        </div>
      </div>
    </div>
  </div>

</body>

<script>
  var install = new Vue({
    el: '#install',
    data: {
      apiPath: window.location.href,
      beginShow: true,
      languageShow: false,
      mysqlShow: false,
      createAdminShow: false,
      afterShow: false,
      adminExists: false,
      language: {
        current: '',
        list: [],
        params: [],
      },
      mysql: {
        host: '',
        user: '',
        password: '',
        base_name: '',
        prefix: '',
      },
      admin: {
        name: 'Admin',
        email: '',
        password: '',
      },
      error: {
        common: '',
        name: '',
        email: '',
        password: '',
      }
    },
    created: function() {
      this.getLanguages();
    },
    methods: {
      skipAdmin: function() {
        this.createAdminShow = false;
        this.afterShow = true;
      },
      start: function() {
        axios.post(this.apiPath, {
          action: 'start',
          language: this.language.current,
        }).then(response => {
          if (response.data.success === true) {
            this.beginShow = false;
            this.mysqlShow = true;

            this.getMysqlConfig();
          } else {
            this.error.common = response.data.error;
          }
        });
      },
      getLanguages: function() {
        axios.post(this.apiPath, {
          action: 'getLanguages'
        }).then(response => {
          if (response.data.success === true) {
            this.language.current = response.data.current;
            this.language.list = response.data.list;
            this.language.params = response.data.params;

            if (Object.keys(response.data.list).length > 1) {
              this.languageShow = true;
            }
          } else {
            this.error.common = response.data.error;
          }
        });
      },
      selectLanguages: function() {
        axios.post(this.apiPath, {
          action: 'selectLanguages',
          language: this.language.current
        }).then(response => {
          this.language.params = response.data;
        });
      },
      setMysqlConfig: function() {
        axios.post(this.apiPath, {
          action: 'installDtabase',
          mysql: this.mysql
        }).then(response => {
          if (response.data.success === true) {
            this.mysqlShow = false;
            this.createAdminShow = true;
          } else {
            this.error.common = response.data.error;
          }
        });
      },
      getMysqlConfig: function() {
        axios.post(this.apiPath, {
          action: 'getMysqlConfig'
        }).then(response => {
          this.mysql.host = response.data.host;
          this.mysql.user = response.data.user;
          this.mysql.password = response.data.password;
          this.mysql.base_name = response.data.base_name;
          this.mysql.prefix = response.data.prefix;
        });
      },
      createAdmin: function() {
        if (this.admin.name.trim().length === 0) {
          this.error.name = this.language.params.empty_field;
          return false;
        }
        if (this.admin.email.trim().length === 0) {
          this.error.email = this.language.params.empty_field;
          return false;
        }
        if (this.admin.password.trim().length === 0) {
          this.error.password = this.language.params.empty_field;
          return false;
        }

        axios.post(this.apiPath, {
          action: 'createAdmin',
          name: this.admin.name,
          email: this.admin.email,
          password: this.admin.password,
        }).then(response => {
          if (response.data.success === true) {
            this.createAdminShow = false;
            this.afterShow = true;
          } else {
            if (response.data.error === 'admin-exists') {
              this.adminExists = true;
              this.error.common = this.language.params.admin_exists;
            }
          }
        });
      }
    }
  });
</script>

</html>