<template>
  <v-app>
    <v-main>
      <v-container fluid class="fill-height pa-0">
        <v-row no-gutters class="fill-height">
          <!-- Section gauche - Branding -->
          <v-col
            cols="12"
            md="6"
            class="d-flex align-center justify-center"
            style="background: linear-gradient(135deg, #6750A4 0%, #21005D 100%)"
          >
            <div class="text-center text-white pa-8">
              <v-icon size="120" class="mb-6">mdi-shield-lock</v-icon>
              <h1 class="text-h3 font-weight-bold mb-4">SmartAccess UCB</h1>
              <p class="text-h6 mb-6 opacity-90">
                Système de Gestion d'Accès
              </p>
              <p class="text-body-1 opacity-75 max-width-400">
                Université Catholique de Bukavu<br>
                Contrôlez et gérez les accès aux salles de manière sécurisée et efficace
              </p>
            </div>
          </v-col>

          <!-- Section droite - Formulaire -->
          <v-col
            cols="12"
            md="6"
            class="d-flex align-center justify-center"
          >
            <v-card
              class="pa-8 ma-4"
              max-width="400"
              width="100%"
              elevation="0"
            >
              <div class="text-center mb-8">
                <h2 class="text-h4 font-weight-bold mb-2">Connexion</h2>
                <p class="text-body-1 text-medium-emphasis">
                  Connectez-vous à votre compte administrateur
                </p>
              </div>

              <v-form @submit.prevent="handleLogin" ref="form">
                <v-text-field
                  v-model="credentials.username"
                  label="Nom d'utilisateur"
                  prepend-inner-icon="mdi-account"
                  variant="outlined"
                  :rules="[rules.required]"
                  class="mb-4"
                  autofocus
                ></v-text-field>

                <v-text-field
                  v-model="credentials.password"
                  label="Mot de passe"
                  prepend-inner-icon="mdi-lock"
                  :type="showPassword ? 'text' : 'password'"
                  :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                  @click:append-inner="showPassword = !showPassword"
                  variant="outlined"
                  :rules="[rules.required]"
                  class="mb-6"
                ></v-text-field>

                <v-btn
                  type="submit"
                  color="primary"
                  size="large"
                  block
                  :loading="authStore.loading"
                  class="mb-4"
                >
                  Se connecter
                </v-btn>
              </v-form>

              <!-- Informations de test -->
              <v-alert
                type="info"
                variant="tonal"
                class="mt-6"
              >
                <div class="text-caption">
                  <strong>Compte de test :</strong><br>
                  Utilisateur : <code>admin</code><br>
                  Mot de passe : <code>admin123</code>
                </div>
              </v-alert>

              <!-- Erreur -->
              <v-alert
                v-if="authStore.error"
                type="error"
                variant="tonal"
                class="mt-4"
                closable
                @click:close="authStore.error = null"
              >
                {{ authStore.error }}
              </v-alert>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref(null)
const showPassword = ref(false)
const credentials = ref({
  username: '',
  password: ''
})

const rules = {
  required: value => !!value || 'Ce champ est requis'
}

const handleLogin = async () => {
  const { valid } = await form.value.validate()
  
  if (valid) {
    const success = await authStore.login(credentials.value)
    if (success) {
      router.push('/dashboard')
    }
  }
}

onMounted(() => {
  authStore.initAuth()
})
</script>

<style scoped>
.max-width-400 {
  max-width: 400px;
  margin: 0 auto;
}
</style>