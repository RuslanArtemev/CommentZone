<template>
  <div v-if="this.$store.state.user.id" class="container">
    <div class="row">
      <div class="col p-0">
        <nav class="navbar navbar-dark bg-dark-blue shadow-sm rounded">
          <div class="container-fluid">
            <div class="pb-2 pt-1">
              <span class="me-1 pointer menu-show-button" @click="menuBoxToggle()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="3 3 11 11">
                  <path
                    fill-rule="evenodd"
                    d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"
                  />
                </svg>
              </span>
              <span class="">
                <a class="navbar-brand" href="#/">CommentZone</a>
              </span>
            </div>

            <div class="btn-group d-flex">
              <div type="button" class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                {{ $store.state.user.name }}
              </div>
              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                <li>
                  <router-link class="dropdown-item" :to="'/profile/' + $store.state.user.id">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-person-bounding-box"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"
                      />
                      <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                    </svg>
                    {{ $store.state.language.profile }}
                  </router-link>
                </li>
                <li>
                  <button class="dropdown-item" type="button" @click="logout">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-door-open"
                      viewBox="0 0 16 16"
                    >
                      <path d="M8.5 10c-.276 0-.5-.448-.5-1s.224-1 .5-1 .5.448.5 1-.224 1-.5 1z" />
                      <path
                        d="M10.828.122A.5.5 0 0 1 11 .5V1h.5A1.5 1.5 0 0 1 13 2.5V15h1.5a.5.5 0 0 1 0 1h-13a.5.5 0 0 1 0-1H3V1.5a.5.5 0 0 1 .43-.495l7-1a.5.5 0 0 1 .398.117zM11.5 2H11v13h1V2.5a.5.5 0 0 0-.5-.5zM4 1.934V15h6V1.077l-6 .857z"
                      />
                    </svg>
                    {{ $store.state.language.exit }}
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </div>
    </div>

    <div class="row my-2">
      <div class="col-auto col-lg-3 col-xl-3 col-xxl-2 p-0 sidebar" :class="menuBoxShow ? 'd-block' : 'd-none'">
        <div class="pe-4 pe-lg-2 p-2 me-lg-2 bg-dark-blue shadow-sm rounded">
          <nav class="nav flex-column">
            <router-link class="nav-link" :class="$route.path === '/' ? 'active' : ''" to="/">
              <span class="me-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-chat-text"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M2.678 11.894a1 1 0 0 1 .287.801 10.97 10.97 0 0 1-.398 2c1.395-.323 2.247-.697 2.634-.893a1 1 0 0 1 .71-.074A8.06 8.06 0 0 0 8 14c3.996 0 7-2.807 7-6 0-3.192-3.004-6-7-6S1 4.808 1 8c0 1.468.617 2.83 1.678 3.894zm-.493 3.905a21.682 21.682 0 0 1-.713.129c-.2.032-.352-.176-.273-.362a9.68 9.68 0 0 0 .244-.637l.003-.01c.248-.72.45-1.548.524-2.319C.743 11.37 0 9.76 0 8c0-3.866 3.582-7 8-7s8 3.134 8 7-3.582 7-8 7a9.06 9.06 0 0 1-2.347-.306c-.52.263-1.639.742-3.468 1.105z"
                  />
                  <path
                    d="M4 5.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zM4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8zm0 2.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5z"
                  />
                </svg>
              </span>
              <span>{{ $store.state.language.comments }}</span>
            </router-link>
            <router-link class="nav-link" :class="$route.path === '/pages' ? 'active' : ''" to="/pages">
              <span class="me-1">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="currentColor"
                  class="bi bi-journals"
                  viewBox="0 0 16 16"
                >
                  <path
                    d="M5 0h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2 2 2 0 0 1-2 2H3a2 2 0 0 1-2-2h1a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1H1a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v9a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1H3a2 2 0 0 1 2-2z"
                  />
                  <path
                    d="M1 6v-.5a.5.5 0 0 1 1 0V6h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V9h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 2.5v.5H.5a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1H2v-.5a.5.5 0 0 0-1 0z"
                  />
                </svg>
              </span>
              <span>{{ $store.state.language.pages }}</span>
            </router-link>
            <div class="nav-item">
              <div class="nav-link pointer" @click="menuTogle('users')">
                <span class="me-1">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-people"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"
                    />
                  </svg>
                </span>
                <span>{{ $store.state.language.users }}</span>
                <span class="menu-arrow" :class="menuUsersShow || /profiles?|banip/g.test($route.path) ? 'arrow-down' : ''">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-chevron-right"
                    viewBox="0 0 16 16"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"
                    />
                  </svg>
                </span>
              </div>
              <div class="collapse ms-3" :class="menuUsersShow || /profiles?|banip/g.test($route.path) ? 'show' : ''">
                <router-link class="nav-link py-1" :class="/profiles?/g.test($route.path) ? 'active' : ''" to="/profiles">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-person-bounding-box"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1h-3zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5zM.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5zm15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5z"
                      />
                      <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm8-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.profiles }}</span>
                </router-link>
                <router-link
                  v-if="$store.state.user.permission.indexOf('manage_users') !== -1"
                  class="nav-link py-1"
                  :class="$route.path === '/banip' ? 'active' : ''"
                  to="/banip"
                >
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-person-bounding-box"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M8 0c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zM8 2c1.3 0 2.5 0.4 3.5 1.1l-8.4 8.4c-0.7-1-1.1-2.2-1.1-3.5 0-3.3 2.7-6 6-6zM8 14c-1.3 0-2.5-0.4-3.5-1.1l8.4-8.4c0.7 1 1.1 2.2 1.1 3.5 0 3.3-2.7 6-6 6z"
                      ></path>
                    </svg>
                  </span>
                  <span>{{ $store.state.language.ban_ip }}</span>
                </router-link>
              </div>
            </div>
            <div class="nav-item">
              <div class="nav-link pointer" @click="menuTogle('setting')">
                <span class="me-1">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                    <path
                      d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"
                    />
                    <path
                      d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"
                    />
                  </svg>
                </span>
                <span>{{ $store.state.language.system }}</span>
                <span class="menu-arrow" :class="menuSettingShow || /setting/g.test($route.path) ? 'arrow-down' : ''">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-chevron-right"
                    viewBox="0 0 16 16"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"
                    />
                  </svg>
                </span>
              </div>
              <div class="collapse ms-3" :class="menuSettingShow || /setting/g.test($route.path) ? 'show' : ''">
                <router-link class="nav-link py-1" :class="$route.path === '/setting' ? 'active' : ''" to="/setting">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-sliders"
                      viewBox="0 0 16 16"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.setting }}</span>
                </router-link>
                <router-link class="nav-link py-1" :class="$route.path === '/setting/authorize' ? 'active' : ''" to="/setting/authorize">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-view-stacked"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M3 0h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3zm0 8h10a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1H3z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.authorize }}</span>
                </router-link>
                <router-link class="nav-link py-1" :class="$route.path === '/setting/antiflood' ? 'active' : ''" to="/setting/antiflood">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-water"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M.036 3.314a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 3.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 6.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0L.314 9.964a.5.5 0 0 1-.278-.65zm0 3a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.antiflood }}</span>
                </router-link>
                <router-link class="nav-link py-1" :class="$route.path === '/setting/stopwords' ? 'active' : ''" to="/setting/stopwords">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-input-cursor-text"
                      viewBox="0 0 16 16"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M5 2a.5.5 0 0 1 .5-.5c.862 0 1.573.287 2.06.566.174.099.321.198.44.286.119-.088.266-.187.44-.286A4.165 4.165 0 0 1 10.5 1.5a.5.5 0 0 1 0 1c-.638 0-1.177.213-1.564.434a3.49 3.49 0 0 0-.436.294V7.5H9a.5.5 0 0 1 0 1h-.5v4.272c.1.08.248.187.436.294.387.221.926.434 1.564.434a.5.5 0 0 1 0 1 4.165 4.165 0 0 1-2.06-.566A4.561 4.561 0 0 1 8 13.65a4.561 4.561 0 0 1-.44.285 4.165 4.165 0 0 1-2.06.566.5.5 0 0 1 0-1c.638 0 1.177-.213 1.564-.434.188-.107.335-.214.436-.294V8.5H7a.5.5 0 0 1 0-1h.5V3.228a3.49 3.49 0 0 0-.436-.294A3.166 3.166 0 0 0 5.5 2.5.5.5 0 0 1 5 2z"
                      />
                      <path
                        d="M10 5h4a1 1 0 0 1 1 1v4a1 1 0 0 1-1 1h-4v1h4a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-4v1zM6 5V4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h4v-1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.stop_string }}</span>
                </router-link>
                <router-link class="nav-link py-1" :class="$route.path === '/setting/spam' ? 'active' : ''" to="/setting/spam">
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-exclamation-octagon"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M4.54.146A.5.5 0 0 1 4.893 0h6.214a.5.5 0 0 1 .353.146l4.394 4.394a.5.5 0 0 1 .146.353v6.214a.5.5 0 0 1-.146.353l-4.394 4.394a.5.5 0 0 1-.353.146H4.893a.5.5 0 0 1-.353-.146L.146 11.46A.5.5 0 0 1 0 11.107V4.893a.5.5 0 0 1 .146-.353L4.54.146zM5.1 1 1 5.1v5.8L5.1 15h5.8l4.1-4.1V5.1L10.9 1H5.1z"
                      />
                      <path
                        d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.spam_list }}</span>
                </router-link>
                <router-link
                  v-if="$store.state.user.permission.indexOf('manage_role') !== -1"
                  class="nav-link py-1"
                  :class="$route.path === '/setting/permissions' ? 'active' : ''"
                  to="/setting/permissions"
                >
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-shield-plus"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 62.456 62.456 0 0 1 5.072.56z"
                      />
                      <path
                        d="M8 4.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V9a.5.5 0 0 1-1 0V7.5H6a.5.5 0 0 1 0-1h1.5V5a.5.5 0 0 1 .5-.5z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.permissions }}</span>
                </router-link>
                <router-link
                  v-if="$store.state.user.permission.indexOf('manage_role') !== -1"
                  class="nav-link py-1"
                  :class="$route.path === '/setting/roles' ? 'active' : ''"
                  to="/setting/roles"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi"
                    viewBox="0 0 433.429 433.429"
                  >
                    <path
                      id="XMLID_359_"
                      d="M140.64,175.767c-2.53,11.075-2.602,22.381-0.214,33.607c0.052,0.244,0.094,0.49,0.125,0.738
	c0.752,5.918,5.83,10.382,11.812,10.382c6.252,0,11.394-4.839,11.876-10.967c-0.033-0.31-0.05-0.625-0.05-0.944
	c0-0.911-0.109-1.833-0.324-2.738c-2.264-9.482-1.915-19.423,1.013-28.738c1.491-4.742,6.542-7.379,11.284-5.888
	c4.742,1.49,7.378,6.542,5.888,11.284c-1.952,6.212-2.186,12.842-0.674,19.171c0.425,1.79,0.685,3.606,0.776,5.424
	c0.082,0.483,0.125,0.979,0.125,1.484c0,16.494-13.419,29.911-29.913,29.911c-14.889,0-27.55-11.028-29.615-25.718
	c-2.862-13.689-2.746-27.488,0.345-41.018c1.107-4.845,5.934-7.876,10.778-6.77C138.716,166.096,141.747,170.922,140.64,175.767z
	 M273.506,365.338c-4.899-0.826-9.545,2.472-10.375,7.374c-0.829,4.901,2.472,9.546,7.374,10.375
	c27.79,4.7,38.333,11.082,40.743,13.773c-1.615,1.742-8.041,6.945-29.924,11.756c-19.985,4.394-45.893,6.813-72.951,6.813
	c-30.992,0-60.543-3.208-81.077-8.802c-15.458-4.211-20.416-8.269-21.802-9.767c2.408-2.691,12.949-9.074,40.745-13.774
	c4.901-0.829,8.202-5.474,7.374-10.375s-5.478-8.205-10.375-7.374c-37.234,6.296-56.113,16.913-56.113,31.556
	c0,11.74,11.593,20.604,35.44,27.101c22.01,5.996,53.286,9.435,85.808,9.435c30.683,0,59.632-2.956,81.513-8.324
	c26.735-6.559,39.731-15.787,39.731-28.211C329.619,382.252,310.74,371.635,273.506,365.338z M238.282,369.806v1.489
	c0,16.493-13.417,29.91-29.91,29.91c-16.492,0-29.91-13.417-29.91-29.91v-1.489c0-16.492,13.417-29.91,29.91-29.91
	C224.864,339.896,238.282,353.314,238.282,369.806z M220.282,369.806c0-6.567-5.343-11.91-11.91-11.91
	c-6.567,0-11.91,5.343-11.91,11.91v1.489c0,6.567,5.343,11.91,11.91,11.91c6.567,0,11.91-5.343,11.91-11.91V369.806z
	 M247.217,212.903c-5.072,7.9-12.844,13.438-21.968,15.636c-0.025,0.006-0.05,0.012-0.075,0.018
	c-16.154,3.864-29.796,13.717-38.411,27.742c-8.66,14.094-11.317,30.705-7.486,46.761c3.256,13.733,15.557,23.007,29.096,23.007
	c2.275,0,4.586-0.262,6.894-0.809c7.771-1.841,14.36-6.599,18.553-13.397c4.192-6.797,5.487-14.82,3.643-22.604
	c-0.176-0.739,0.038-1.303,0.248-1.646c0.196-0.319,0.624-0.877,1.286-1.036c0.047-0.011,0.105-0.026,0.164-0.042
	c24.575-5.913,45.44-20.899,58.86-42.25c0.192-0.249,0.371-0.509,0.537-0.779c13.229-21.533,17.387-47.012,11.706-71.742
	c-1.113-4.844-5.937-7.867-10.787-6.756c-4.844,1.113-7.869,5.942-6.756,10.787c4.554,19.824,1.336,40.231-9.059,57.563
	c-0.169,0.224-0.327,0.456-0.476,0.697c-10.902,17.72-28.064,30.15-48.325,35.001c-0.049,0.012-0.11,0.027-0.172,0.043
	c-5.113,1.256-9.482,4.48-12.315,9.089c-2.814,4.579-3.673,9.993-2.424,15.232c0.733,3.093,0.218,6.287-1.451,8.993
	c-1.668,2.706-4.29,4.599-7.381,5.331c-6.385,1.513-12.813-2.451-14.329-8.848c-2.719-11.394-0.833-23.174,5.312-33.174
	c6.087-9.91,15.739-16.883,27.187-19.641c0.025-0.006,0.051-0.012,0.076-0.018c14.069-3.361,25.996-12.014,33.584-24.364
	c0.005-0.009,0.047-0.077,0.052-0.086c0.164-0.27,0.313-0.544,0.445-0.823c7.167-12.128,9.365-26.267,6.165-40.003l-0.109-0.449
	c-0.252-1.042-0.547-2.113-0.875-3.182c-1.461-4.751-6.494-7.419-11.248-5.958c-4.751,1.46-7.418,6.496-5.958,11.247
	c0.222,0.723,0.419,1.44,0.589,2.14l0.091,0.371c2.17,9.318,0.576,18.991-4.49,27.237
	C247.467,212.425,247.337,212.662,247.217,212.903z M365.777,111.273c0,14.865-16.049,26.688-47.702,35.139
	c-27.19,7.26-63.187,11.258-101.361,11.258c-38.173,0-74.17-3.999-101.36-11.258c-31.653-8.452-47.702-20.274-47.702-35.139
	c0-13.53,13.328-24.553,39.633-32.802l10.019-58.316c1.508-8.772,8.459-19.302,33.011-19.659
	c13.676-0.199,29.738,2.458,43.914,4.802c9.165,1.515,17.822,2.946,22.485,2.946c5.637,0,15.032-1.636,24.979-3.368
	c24.051-4.188,51.311-8.936,65.479,1.056c4.845,3.416,7.941,8.334,8.953,14.223l10.019,58.317
	C352.449,86.721,365.777,97.744,365.777,111.273z M347.777,111.273c0-1.542-4.384-8.101-24.545-14.826
	c-27.387,8.091-65.972,12.71-106.518,12.71c-25.325,0-50.242-1.802-72.056-5.212c-4.911-0.768-8.27-5.371-7.502-10.282
	c0.768-4.912,5.37-8.275,10.282-7.502c20.91,3.269,44.865,4.997,69.275,4.997c34.264,0,66.85-3.374,91.734-9.383l-10.063-58.572
	c-0.206-1.196-0.635-1.89-1.585-2.56c-8.066-5.688-34.517-1.082-52.02,1.967c-11.201,1.95-20.874,3.635-28.066,3.635
	c-6.141,0-15.075-1.477-25.421-3.188c-12.987-2.147-27.649-4.571-39.508-4.571c-0.407,0-0.809,0.003-1.208,0.009
	c-3.462,0.05-14.815,0.539-15.533,4.709l-10.966,63.828c-0.588,3.423-3.093,6.202-6.438,7.141
	c-26.56,7.456-31.988,15.386-31.988,17.101c0,1.812,5.829,10.134,34.345,17.749c25.719,6.867,60.067,10.649,96.717,10.649
	c36.65,0,70.999-3.782,96.717-10.649C341.948,121.407,347.777,113.085,347.777,111.273z"
                    />
                  </svg>
                  <span>{{ $store.state.language.roles }}</span>
                </router-link>
                <router-link
                  v-if="$store.state.user.role === 'admin'"
                  class="nav-link py-1"
                  :class="$route.path === '/setting/languages' ? 'active' : ''"
                  to="/setting/languages"
                >
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-globe2"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm7.5-6.923c-.67.204-1.335.82-1.887 1.855-.143.268-.276.56-.395.872.705.157 1.472.257 2.282.287V1.077zM4.249 3.539c.142-.384.304-.744.481-1.078a6.7 6.7 0 0 1 .597-.933A7.01 7.01 0 0 0 3.051 3.05c.362.184.763.349 1.198.49zM3.509 7.5c.036-1.07.188-2.087.436-3.008a9.124 9.124 0 0 1-1.565-.667A6.964 6.964 0 0 0 1.018 7.5h2.49zm1.4-2.741a12.344 12.344 0 0 0-.4 2.741H7.5V5.091c-.91-.03-1.783-.145-2.591-.332zM8.5 5.09V7.5h2.99a12.342 12.342 0 0 0-.399-2.741c-.808.187-1.681.301-2.591.332zM4.51 8.5c.035.987.176 1.914.399 2.741A13.612 13.612 0 0 1 7.5 10.91V8.5H4.51zm3.99 0v2.409c.91.03 1.783.145 2.591.332.223-.827.364-1.754.4-2.741H8.5zm-3.282 3.696c.12.312.252.604.395.872.552 1.035 1.218 1.65 1.887 1.855V11.91c-.81.03-1.577.13-2.282.287zm.11 2.276a6.696 6.696 0 0 1-.598-.933 8.853 8.853 0 0 1-.481-1.079 8.38 8.38 0 0 0-1.198.49 7.01 7.01 0 0 0 2.276 1.522zm-1.383-2.964A13.36 13.36 0 0 1 3.508 8.5h-2.49a6.963 6.963 0 0 0 1.362 3.675c.47-.258.995-.482 1.565-.667zm6.728 2.964a7.009 7.009 0 0 0 2.275-1.521 8.376 8.376 0 0 0-1.197-.49 8.853 8.853 0 0 1-.481 1.078 6.688 6.688 0 0 1-.597.933zM8.5 11.909v3.014c.67-.204 1.335-.82 1.887-1.855.143-.268.276-.56.395-.872A12.63 12.63 0 0 0 8.5 11.91zm3.555-.401c.57.185 1.095.409 1.565.667A6.963 6.963 0 0 0 14.982 8.5h-2.49a13.36 13.36 0 0 1-.437 3.008zM14.982 7.5a6.963 6.963 0 0 0-1.362-3.675c-.47.258-.995.482-1.565.667.248.92.4 1.938.437 3.008h2.49zM11.27 2.461c.177.334.339.694.482 1.078a8.368 8.368 0 0 0 1.196-.49 7.01 7.01 0 0 0-2.275-1.52c.218.283.418.597.597.932zm-.488 1.343a7.765 7.765 0 0 0-.395-.872C9.835 1.897 9.17 1.282 8.5 1.077V4.09c.81-.03 1.577-.13 2.282-.287z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.languages }}</span>
                </router-link>
                <router-link
                  v-if="$store.state.user.role === 'admin'"
                  class="nav-link py-1"
                  :class="$route.path === '/setting/smtp' ? 'active' : ''"
                  to="/setting/smtp"
                >
                  <span class="me-1">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="16"
                      height="16"
                      fill="currentColor"
                      class="bi bi-envelope"
                      viewBox="0 0 16 16"
                    >
                      <path
                        d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.758 2.855L15 11.114v-5.73zm-.034 6.878L9.271 8.82 8 9.583 6.728 8.82l-5.694 3.44A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.739zM1 11.114l4.758-2.876L1 5.383v5.73z"
                      />
                    </svg>
                  </span>
                  <span>{{ $store.state.language.smtp }}</span>
                </router-link>
              </div>
            </div>
          </nav>
        </div>
      </div>
      <div class="content p-4 bg-dark-blue" :class="menuBoxShow ? 'col-lg-9 col-xl-9 col-xxl-10' : 'col-12'">
        <router-view></router-view>
      </div>
    </div>

    <toast v-if="this.$store.state.notice.show" :type="this.$store.state.notice.type" :message="this.$store.state.notice.message"></toast>
  </div>
</template>

<script>
export default {
  data() {
    return {
      menuBoxShow: true,
      menuUsersShow: false,
      menuSettingShow: false,
    };
  },
  directives: {},
  created() {
    if (window.innerWidth < 991.98) {
      this.menuBoxShow = false;
    }
    window.addEventListener("resize", (event) => {
      if (event.target.innerWidth < 991.98) {
        this.menuBoxShow = false;
      } else {
        this.menuBoxShow = true;
      }
    });

    document.addEventListener("click", (e) => {
      if (window.innerWidth < 991.98 && !this.cssExists(e.target, "sidebar") && !this.cssExists(e.target, "menu-show-button")) {
        this.menuBoxShow = false;
      }
    });
  },
  methods: {
    menuBoxToggle() {
      this.menuBoxShow = this.menuBoxShow ? false : true;
    },
    menuTogle(menu) {
      if (menu === "users") {
        this.menuUsersShow = this.menuUsersShow ? false : true;
      }
      if (menu === "setting") {
        this.menuSettingShow = this.menuSettingShow ? false : true;
      }
    },
    logout() {
      axios.post(this.$store.state.apiPath, { action: "logout" }).then((respons) => {
        if (respons.data === true) {
          this.$store.state.user = [];
          if (this.$route.name !== "login") {
            this.$router.push("/login");
          }
        }
      });
    },
  },
};
</script>

<style lang="scss" scoped>
#commentzone {
  .menu-arrow {
    font-size: 0.7rem;
    &.arrow-down {
      transform: rotate(90deg);
      display: inline-block;
    }
  }
  .menu-show-button {
    display: none;
  }
  @media (max-width: 991.98px) {
    .menu-show-button {
      display: inline-block;
    }
    .sidebar {
      position: absolute;
      display: none;
      box-shadow: #1e2125bf 3px 4px 14px 0px;
      z-index: 9999;
    }
  }
}
</style>