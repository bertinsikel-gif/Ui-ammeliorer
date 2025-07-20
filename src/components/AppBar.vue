<template>
  <v-app-bar
    app
    elevation="1"
    color="surface"
    height="64"
  >
    <v-app-bar-nav-icon
      @click="appStore.toggleDrawer"
      v-if="$vuetify.display.mdAndDown"
    ></v-app-bar-nav-icon>

    <v-app-bar-title class="text-h6 font-weight-medium">
      {{ pageTitle }}
    </v-app-bar-title>

    <v-spacer></v-spacer>

    <!-- Actions de la barre d'outils -->
    <v-btn
      icon="mdi-bell-outline"
      variant="text"
      class="me-2"
    >
      <v-icon>mdi-bell-outline</v-icon>
      <v-badge
        content="3"
        color="error"
        floating
      ></v-badge>
    </v-btn>

    <v-btn
      icon="mdi-help-circle-outline"
      variant="text"
      class="me-2"
    ></v-btn>

    <!-- Menu utilisateur -->
    <v-menu>
      <template v-slot:activator="{ props }">
        <v-btn
          v-bind="props"
          variant="text"
          class="text-none"
        >
          <v-avatar size="32" class="me-2">
            <v-img :src="userAvatar"></v-img>
          </v-avatar>
          <span class="d-none d-sm-flex">
            {{ authStore.user?.prenom }}
          </span>
          <v-icon end>mdi-chevron-down</v-icon>
        </v-btn>
      </template>

      <v-list min-width="200">
        <v-list-item>
          <template v-slot:prepend>
            <v-avatar size="40">
              <v-img :src="userAvatar"></v-img>
            </v-avatar>
          </template>
          <v-list-item-title>{{ authStore.user?.nom }} {{ authStore.user?.prenom }}</v-list-item-title>
          <v-list-item-subtitle>{{ authStore.user?.email }}</v-list-item-subtitle>
        </v-list-item>

        <v-divider></v-divider>

        <v-list-item @click="logout">
          <template v-slot:prepend>
            <v-icon>mdi-logout</v-icon>
          </template>
          <v-list-item-title>Déconnexion</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

const pageTitle = computed(() => {
  const titles = {
    'Dashboard': 'Tableau de bord',
    'Etudiants': 'Gestion des étudiants',
    'Salles': 'Gestion des salles',
    'Acces': 'Gestion des accès',
    'Historique': 'Historique des accès'
  }
  return titles[route.name] || 'SmartAccess UCB'
})

const userAvatar = computed(() => {
  const user = authStore.user
  if (user) {
    return `https://ui-avatars.com/api/?name=${user.prenom}+${user.nom}&background=6750A4&color=fff`
  }
  return null
})

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>