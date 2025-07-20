<template>
  <v-app>
    <AppNavigation />
    <AppBar />
    
    <v-main>
      <v-container fluid class="pa-6">
        <!-- En-tête -->
        <div class="mb-6">
          <h1 class="text-h4 font-weight-bold mb-2">Tableau de bord</h1>
          <p class="text-body-1 text-medium-emphasis">
            Vue d'ensemble du système SmartAccess UCB
          </p>
        </div>

        <!-- Cartes de statistiques -->
        <v-row class="mb-6">
          <v-col
            v-for="stat in stats"
            :key="stat.title"
            cols="12"
            sm="6"
            lg="3"
          >
            <v-card
              :color="stat.color"
              variant="tonal"
              class="pa-4"
              height="120"
            >
              <div class="d-flex align-center justify-space-between">
                <div>
                  <div class="text-h3 font-weight-bold">{{ stat.value }}</div>
                  <div class="text-body-2 font-weight-medium">{{ stat.title }}</div>
                  <div class="text-caption text-medium-emphasis">{{ stat.subtitle }}</div>
                </div>
                <v-avatar :color="stat.color" size="56" variant="flat">
                  <v-icon :icon="stat.icon" size="28"></v-icon>
                </v-avatar>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <!-- Actions rapides -->
        <v-row class="mb-6">
          <v-col cols="12">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon class="me-2">mdi-lightning-bolt</v-icon>
                Actions rapides
              </v-card-title>
              <v-card-text>
                <v-row>
                  <v-col
                    v-for="action in quickActions"
                    :key="action.title"
                    cols="12"
                    sm="6"
                    md="3"
                  >
                    <v-btn
                      :to="action.to"
                      :color="action.color"
                      variant="tonal"
                      size="large"
                      block
                      class="text-none"
                    >
                      <v-icon start>{{ action.icon }}</v-icon>
                      {{ action.title }}
                    </v-btn>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Contenu principal -->
        <v-row>
          <!-- Derniers accès -->
          <v-col cols="12" lg="8">
            <v-card>
              <v-card-title class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <v-icon class="me-2">mdi-history</v-icon>
                  Derniers accès
                </div>
                <v-btn
                  icon="mdi-refresh"
                  variant="text"
                  size="small"
                  @click="loadRecentAccess"
                ></v-btn>
              </v-card-title>
              
              <v-data-table
                :headers="accessHeaders"
                :items="recentAccess"
                :loading="loadingAccess"
                no-data-text="Aucun accès enregistré"
                loading-text="Chargement des accès..."
                class="elevation-0"
              >
                <template v-slot:item.statut="{ item }">
                  <v-chip
                    :color="item.statut === 'AUTORISE' ? 'success' : 'error'"
                    size="small"
                    variant="tonal"
                  >
                    <v-icon start size="16">
                      {{ item.statut === 'AUTORISE' ? 'mdi-check-circle' : 'mdi-close-circle' }}
                    </v-icon>
                    {{ item.statut === 'AUTORISE' ? 'Autorisé' : 'Refusé' }}
                  </v-chip>
                </template>
                
                <template v-slot:item.date_entree="{ item }">
                  <div class="text-caption">
                    {{ formatDate(item.date_entree) }}
                  </div>
                </template>
              </v-data-table>
            </v-card>
          </v-col>

          <!-- Autorisations récentes -->
          <v-col cols="12" lg="4">
            <v-card>
              <v-card-title class="d-flex align-center">
                <v-icon class="me-2">mdi-key-variant</v-icon>
                Autorisations récentes
              </v-card-title>
              
              <v-list>
                <v-list-item
                  v-for="auth in recentAuthorizations"
                  :key="auth.id"
                  class="px-4"
                >
                  <template v-slot:prepend>
                    <v-avatar color="primary" size="40">
                      <v-icon color="white">mdi-account</v-icon>
                    </v-avatar>
                  </template>
                  
                  <v-list-item-title class="font-weight-medium">
                    {{ auth.nom }} {{ auth.prenom }}
                  </v-list-item-title>
                  <v-list-item-subtitle>
                    {{ auth.nom_salle }}
                  </v-list-item-subtitle>
                  <v-list-item-subtitle class="text-caption">
                    {{ formatDate(auth.date_creation) }}
                  </v-list-item-subtitle>
                  
                  <template v-slot:append>
                    <v-chip
                      color="success"
                      size="small"
                      variant="tonal"
                    >
                      {{ auth.niveau_acces }}
                    </v-chip>
                  </template>
                </v-list-item>
                
                <v-list-item v-if="recentAuthorizations.length === 0">
                  <v-list-item-title class="text-center text-medium-emphasis">
                    Aucune autorisation récente
                  </v-list-item-title>
                </v-list-item>
              </v-list>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <!-- Snackbar global -->
    <v-snackbar
      v-model="appStore.snackbar.show"
      :color="appStore.snackbar.color"
      :timeout="appStore.snackbar.timeout"
      location="top right"
    >
      {{ appStore.snackbar.message }}
      <template v-slot:actions>
        <v-btn
          variant="text"
          @click="appStore.hideSnackbar"
        >
          Fermer
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import AppNavigation from '@/components/AppNavigation.vue'
import AppBar from '@/components/AppBar.vue'

const authStore = useAuthStore()
const appStore = useAppStore()

const loadingAccess = ref(false)
const recentAccess = ref([])
const recentAuthorizations = ref([])

const stats = ref([
  {
    title: 'Étudiants',
    subtitle: 'Inscrits dans le système',
    value: '1,247',
    icon: 'mdi-account-group',
    color: 'primary'
  },
  {
    title: 'Salles',
    subtitle: 'Disponibles',
    value: '24',
    icon: 'mdi-domain',
    color: 'success'
  },
  {
    title: 'Autorisations',
    subtitle: 'Actives',
    value: '856',
    icon: 'mdi-key-variant',
    color: 'warning'
  },
  {
    title: 'Accès aujourd\'hui',
    subtitle: 'Tentatives d\'accès',
    value: '142',
    icon: 'mdi-clock-outline',
    color: 'info'
  }
])

const quickActions = [
  {
    title: 'Ajouter Étudiant',
    icon: 'mdi-account-plus',
    color: 'primary',
    to: '/etudiants'
  },
  {
    title: 'Ajouter Salle',
    icon: 'mdi-domain-plus',
    color: 'success',
    to: '/salles'
  },
  {
    title: 'Gérer Accès',
    icon: 'mdi-key-plus',
    color: 'warning',
    to: '/acces'
  },
  {
    title: 'Voir Historique',
    icon: 'mdi-history',
    color: 'info',
    to: '/historique'
  }
]

const accessHeaders = [
  { title: 'Étudiant', key: 'etudiant', sortable: false },
  { title: 'Salle', key: 'nom_salle', sortable: false },
  { title: 'Statut', key: 'statut', sortable: false },
  { title: 'Date/Heure', key: 'date_entree', sortable: false }
]

const loadRecentAccess = async () => {
  loadingAccess.value = true
  try {
    // Simulation de données (à remplacer par votre API)
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    recentAccess.value = [
      {
        id: 1,
        etudiant: 'MUKAMBA Jean',
        nom_salle: 'Salle Informatique A',
        statut: 'AUTORISE',
        date_entree: new Date().toISOString()
      },
      {
        id: 2,
        etudiant: 'NABINTU Marie',
        nom_salle: 'Bibliothèque',
        statut: 'AUTORISE',
        date_entree: new Date(Date.now() - 3600000).toISOString()
      },
      {
        id: 3,
        etudiant: 'BAHATI Pierre',
        nom_salle: 'Amphithéâtre',
        statut: 'REFUSE',
        date_entree: new Date(Date.now() - 7200000).toISOString()
      }
    ]
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement des accès', 'error')
  } finally {
    loadingAccess.value = false
  }
}

const loadRecentAuthorizations = async () => {
  try {
    // Simulation de données
    recentAuthorizations.value = [
      {
        id: 1,
        nom: 'MUKAMBA',
        prenom: 'Jean',
        nom_salle: 'Salle Informatique A',
        niveau_acces: 'LECTURE',
        date_creation: new Date().toISOString()
      },
      {
        id: 2,
        nom: 'NABINTU',
        prenom: 'Marie',
        nom_salle: 'Bibliothèque',
        niveau_acces: 'LECTURE',
        date_creation: new Date(Date.now() - 3600000).toISOString()
      }
    ]
  } catch (error) {
    console.error('Erreur chargement autorisations:', error)
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  authStore.initAuth()
  loadRecentAccess()
  loadRecentAuthorizations()
})
</script>