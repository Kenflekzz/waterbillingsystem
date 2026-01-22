<template>
  <header class="custom-header py-3">
    <div class="container d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center">
        <img
          :src="homepage.logo || '/images/MAGALLANES_LOGO.png'"
          alt="Logo"
          class="me-3"
          style="height: 50px;"
        />
        <h1 class="h3 mb-0 text-white">
          {{ homepage.header_title || 'Magallanes Water Billing System' }}
        </h1>
      </div>

      <nav>
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link text-white" href="/">
              <i class="bi bi-house-door"></i> {{ homepage.nav_home || 'Home' }}
            </a>
          </li>
         <li v-if="!hideContactUs" class="nav-item">
           <a class="nav-link text-white" href="#contact" @click.prevent="scrollToContact">
            <i class="bi bi-envelope me-1"></i>{{ homepage.nav_contact ?? 'Contact Us' }}
          </a>
          </li>

          <!-- Hide or show Sign In button depending on prop -->
          <li class="nav-item" v-if="showSignIn">
            <button
              class="btn btn-light"
              @click="redirectToLogin"
            >
              {{ homepage.sign_in_text || 'Sign In' }}
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </header>
</template>

<script>
export default {
  name: "Header",
  props: {
    homepage: {
      type: Object,
      required: true
    },
    showSignIn: {
      type: Boolean,
      default: true
    },
    hideContactUs:{
      type: Boolean,
      default:true
    }
  },
  methods: {
    redirectToLogin() {
      window.location.href = "/user/login";
    },
    scrollToContact() {
    document.querySelector('#contact').scrollIntoView({ behavior: 'smooth' });
  }
  }
};
</script>

<!-- 🔧 Use a global style instead of scoped so background color always applies -->
<style>
.custom-header {
  width: 100%;
  background-color: #0C6170 !important; /* same as homepage */
  color: white !important;
  backdrop-filter: blur(10px);
}
</style>
