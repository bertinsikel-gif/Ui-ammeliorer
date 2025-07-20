<template>
  <v-navigation-drawer
    v-model="appStore.drawer"
    app
    permanent
    :rail="$vuetify.display.mdAndDown"
    :width="280"
    color="surface"
    elevation="1"
  >
    <!-- En-tête du drawer -->
    <v-list-item
      class="pa-4"
      :class="{ 'px-2': $vuetify.display.mdAndDown }"
    >
      <template v-slot:prepend>
        <v-avatar color="primary" size="40">
          <v-icon color="white">mdi-shield-lock</v-icon>
        </v-avatar>
      </template>
      
      <v-list-item-title class="text-h6 font-weight-bold">
        SmartAccess UCB
      </v-list-item-title>
      <v-list-item-subtitle class="text-caption">
        Université Catholique de Bukavu
      </v-list-item-subtitle>
    </v-list-item>

    <v-divider></v-divider>

    <!-- Menu de navigation -->
    <v-list nav density="comfortable" class="pa-2">
      <v-list-item
        v-for="item in menuItems"
        :key="item.title"
        :to="item.to"
        :prepend-icon="item.icon"
        :title="item.title"
        :subtitle="item.subtitle"
        rounded="xl"
        class="mb-1"
        color="primary"
      >
        <template v-slot:append v-if="item.badge">
          <v-badge
            :content="item.badge"
            color="error"
            inline
          ></v-badge>
        </template>
      </v-list-item>
    </v-list>

    <!-- Informations utilisateur -->
    <template v-slot:append>
      <v-divider></v-divider>
      <v-list density="compact" class="pa-2">
        <v-list-item
          :prepend-avatar="userAvatar"
          :title="authStore.user?.nom + ' ' + authStore.user?.prenom"
          :subtitle="authStore.user?.email"
          rounded="xl"
        >
          <template v-slot:append>
            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn
                  icon="mdi-dots-vertical"
                  variant="text"
                  size="small"
                  v-bind="props"
                ></v-btn>
              </template>
              <v-list>
                <v-list-item @click="logout">
                  <template v-slot:prepend>
                    <v-icon>mdi-logout</v-icon>
                  </template>
                  <v-list-item-title>Déconnexion</v-list-item-title>
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
        </v-list-item>
      </v-list>
    </template>
  </v-navigation-drawer>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'

const router = useRouter()
const authStore = useAuthStore()
const appStore = useAppStore()

const menuItems = [
  {
    title: 'Tableau de bord',
    subtitle: 'Vue d\'ensemble',
    icon: 'mdi-view-dashboard',
    to: '/dashboard'
  },
  {
    title: 'Étudiants',
    subtitle: 'Gestion des étudiants',
    icon: 'mdi-account-group',
    to: '/etudiants'
  },
  {
    title: 'Salles',
    subtitle: 'Gestion des salles',
    icon: 'mdi-domain',
    to: '/salles'
  },
  {
    title: 'Accès',
    subtitle: 'Attribution d\'accès',
    icon: 'mdi-key-variant',
    to: '/acces'
  },
  {
    title: 'Historique',
    subtitle: 'Historique des accès',
    icon: 'mdi-history',
    to: '/historique'
  }
]

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