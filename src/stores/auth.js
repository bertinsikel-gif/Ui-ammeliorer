import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

  const login = async (credentials) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await authAPI.login(credentials)
      
      if (response.data.success) {
        user.value = response.data.user
        
        // Stocker dans localStorage
        localStorage.setItem('smartaccess_user', JSON.stringify(user.value))
        return true
      } else {
        throw new Error(response.data.message || 'Erreur de connexion')
      }
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Erreur de connexion'
      return false
    } finally {
      loading.value = false
    }
  }

  const logout = () => {
    user.value = null
    localStorage.removeItem('smartaccess_user')
  }

  const initAuth = () => {
    const savedUser = localStorage.getItem('smartaccess_user')
    if (savedUser) {
      try {
        user.value = JSON.parse(savedUser)
      } catch (error) {
        console.error('Erreur lors de la récupération des données utilisateur:', error)
        localStorage.removeItem('smartaccess_user')
      }
    }
  }

  return {
    user,
    loading,
    error,
    isAuthenticated,
    login,
    logout,
    initAuth
  }
})