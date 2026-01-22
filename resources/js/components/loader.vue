<template>
  <div v-if="visible" class="drop-loader">
    <div class="droplet"></div>
  </div>
</template>

<script>
export default {
  name: "WaterDropletLoader",
  props: {
    visible: { type: Boolean, required: true }
  }
};
</script>

<style scoped>
.drop-loader {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.droplet {
  width: 35px;
  height: 35px;
  background: #007bff;
  border-radius: 50% 50% 60% 60%;
  position: relative;
  animation: drop 0.8s infinite ease-in-out;
}

@keyframes drop {
  0% { transform: translateY(-15px) scale(1); opacity: 0.9; }
  50% { transform: translateY(0px) scale(0.85); opacity: 1; }
  100% { transform: translateY(15px) scale(1); opacity: 0.9; }
}
</style>
