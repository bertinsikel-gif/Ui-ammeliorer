import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!user.value)

  const login = async (credentials) => {
    loading.value = true
    error.value = null
    
    try {
      // Simulation d'authentification (à remplacer par votre API)
      if (credentials.username === 'admin' && credentials.password === 'admin123') {
        user.value = {
          id: 1,
          username: 'admin',
          nom: 'Administrateur',
          prenom: 'Système',
          email: 'admin@ucbukavu.ac.cd'
        }
        
        // Stocker dans localStorage
        localStorage.setItem('smartaccess_user', JSON.stringify(user.value))
        return true
      } else {
        throw new Error('Identifiants incorrects')
      }
    } catch (err) {
      error.value = err.message
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
      user.value = JSON.parse(savedUser)
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