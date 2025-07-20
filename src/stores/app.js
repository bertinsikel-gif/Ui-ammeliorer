import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useAppStore = defineStore('app', () => {
  const drawer = ref(true)
  const snackbar = ref({
    show: false,
    message: '',
    color: 'success',
    timeout: 4000
  })

  const toggleDrawer = () => {
    drawer.value = !drawer.value
  }

  const showSnackbar = (message, color = 'success', timeout = 4000) => {
    snackbar.value = {
      show: true,
      message,
      color,
      timeout
    }
  }

  const hideSnackbar = () => {
    snackbar.value.show = false
  }

  return {
    drawer,
    snackbar,
    toggleDrawer,
    showSnackbar,
    hideSnackbar
  }
})