<template>
  <v-app>
    <AppNavigation />
    <AppBar />
    
    <v-main>
      <v-container fluid class="pa-6">
        <!-- En-tête -->
        <div class="mb-6">
          <h1 class="text-h4 font-weight-bold mb-2">Historique des accès</h1>
          <p class="text-body-1 text-medium-emphasis">
            Consultez l'historique complet des tentatives d'accès aux salles
          </p>
        </div>

        <!-- Filtres -->
        <v-card class="mb-6">
          <v-card-title>
            <v-icon class="me-2">mdi-filter</v-icon>
            Filtres
          </v-card-title>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="3">
                <v-text-field
                  v-model="filters.search"
                  prepend-inner-icon="mdi-magnify"
                  label="Rechercher..."
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="2">
                <v-select
                  v-model="filters.statut"
                  :items="statutOptions"
                  label="Statut"
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                ></v-select>
              </v-col>
              <v-col cols="12" md="2">
                <v-text-field
                  v-model="filters.dateDebut"
                  label="Date début"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="2">
                <v-text-field
                  v-model="filters.dateFin"
                  label="Date fin"
                  type="date"
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>
              </v-col>
              <v-col cols="12" md="3">
                <v-btn
                  color="primary"
                  @click="applyFilters"
                  class="me-2"
                >
                  <v-icon start>mdi-filter</v-icon>
                  Appliquer
                </v-btn>
                <v-btn
                  variant="outlined"
                  @click="resetFilters"
                >
                  <v-icon start>mdi-filter-off</v-icon>
                  Réinitialiser
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <!-- Statistiques rapides -->
        <v-row class="mb-6">
          <v-col
            v-for="stat in stats"
            :key="stat.title"
            cols="12"
            sm="6"
            lg="3"
          >
            <v-card :color="stat.color" variant="tonal">
              <v-card-text class="d-flex align-center">
                <div class="flex-grow-1">
                  <div class="text-h4 font-weight-bold">{{ stat.value }}</div>
                  <div class="text-body-2">{{ stat.title }}</div>
                </div>
                <v-avatar :color="stat.color" size="48">
                  <v-icon :icon="stat.icon" size="24"></v-icon>
                </v-avatar>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>

        <!-- Tableau de l'historique -->
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <v-icon class="me-2">mdi-history</v-icon>
              Historique des accès ({{ filteredHistorique.length }})
            </div>
            <div>
              <v-btn
                icon="mdi-download"
                variant="text"
                @click="exportData"
                class="me-2"
              ></v-btn>
              <v-btn
                icon="mdi-refresh"
                variant="text"
                @click="loadHistorique"
              ></v-btn>
            </div>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="filteredHistorique"
            :loading="loading"
            :items-per-page="itemsPerPage"
            :items-per-page-options="itemsPerPageOptions"
            no-data-text="Aucun accès trouvé"
            loading-text="Chargement de l'historique..."
            class="elevation-0"
          >
            <template v-slot:item.etudiant="{ item }">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="32" class="me-3">
                  <span class="text-white text-caption">
                    {{ item.nom?.charAt(0) || '?' }}{{ item.prenom?.charAt(0) || '?' }}
                  </span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">
                    {{ item.nom || 'Inconnu' }} {{ item.prenom || '' }}
                  </div>
                  <v-chip color="primary" size="x-small" variant="tonal">
                    {{ item.matricule_utilise }}
                  </v-chip>
                </div>
              </div>
            </template>

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

            <template v-slot:item.type_acces="{ item }">
              <v-chip
                :color="item.type_acces === 'ENTREE' ? 'info' : 'warning'"
                size="small"
                variant="tonal"
              >
                <v-icon start size="16">
                  {{ item.type_acces === 'ENTREE' ? 'mdi-login' : 'mdi-logout' }}
                </v-icon>
                {{ item.type_acces }}
              </v-chip>
            </template>

            <template v-slot:item.date_entree="{ item }">
              <div class="text-caption">
                {{ formatDateTime(item.date_entree) }}
              </div>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn
                icon="mdi-eye"
                variant="text"
                size="small"
                @click="viewDetails(item)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>

        <!-- Dialog de détails -->
        <v-dialog v-model="detailsDialog" max-width="600px">
          <v-card v-if="selectedItem">
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2">mdi-information</v-icon>
              Détails de l'accès
            </v-card-title>

            <v-card-text>
              <v-row>
                <v-col cols="12" md="6">
                  <v-list density="compact">
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Étudiant</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">
                        {{ selectedItem.nom || 'Inconnu' }} {{ selectedItem.prenom || '' }}
                      </v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Matricule utilisé</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">{{ selectedItem.matricule_utilise }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Salle</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">{{ selectedItem.nom_salle || 'Inconnue' }}</v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-col>
                <v-col cols="12" md="6">
                  <v-list density="compact">
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Type d'accès</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">{{ selectedItem.type_acces }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Statut</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">{{ selectedItem.statut }}</v-list-item-subtitle>
                    </v-list-item>
                    <v-list-item>
                      <v-list-item-title class="text-caption text-medium-emphasis">Date/Heure</v-list-item-title>
                      <v-list-item-subtitle class="text-body-2">{{ formatDateTime(selectedItem.date_entree) }}</v-list-item-subtitle>
                    </v-list-item>
                  </v-list>
                </v-col>
              </v-row>

              <v-divider class="my-4"></v-divider>

              <div class="text-caption text-medium-emphasis mb-2">Informations techniques</div>
              <v-list density="compact">
                <v-list-item v-if="selectedItem.ip_address">
                  <v-list-item-title class="text-caption text-medium-emphasis">Adresse IP</v-list-item-title>
                  <v-list-item-subtitle class="text-body-2">{{ selectedItem.ip_address }}</v-list-item-subtitle>
                </v-list-item>
                <v-list-item v-if="selectedItem.user_agent">
                  <v-list-item-title class="text-caption text-medium-emphasis">User Agent</v-list-item-title>
                  <v-list-item-subtitle class="text-body-2 text-truncate">{{ selectedItem.user_agent }}</v-list-item-subtitle>
                </v-list-item>
              </v-list>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="text" @click="detailsDialog = false">Fermer</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-container>
    </v-main>

    <!-- Snackbar -->
    <v-snackbar
      v-model="appStore.snackbar.show"
      :color="appStore.snackbar.color"
      :timeout="appStore.snackbar.timeout"
      location="top right"
    >
      {{ appStore.snackbar.message }}
      <template v-slot:actions>
        <v-btn variant="text" @click="appStore.hideSnackbar">Fermer</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import AppNavigation from '@/components/AppNavigation.vue'
import AppBar from '@/components/AppBar.vue'

const appStore = useAppStore()

const loading = ref(false)
const detailsDialog = ref(false)
const selectedItem = ref(null)
const historique = ref([])
const itemsPerPage = ref(25)

const filters = ref({
  search: '',
  statut: '',
  dateDebut: '',
  dateFin: ''
})

const headers = [
  { title: 'Étudiant', key: 'etudiant', sortable: false },
  { title: 'Salle', key: 'nom_salle', sortable: true },
  { title: 'Type', key: 'type_acces', sortable: true },
  { title: 'Statut', key: 'statut', sortable: true },
  { title: 'Date/Heure', key: 'date_entree', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: '100px' }
]

const statutOptions = [
  { title: 'Autorisé', value: 'AUTORISE' },
  { title: 'Refusé', value: 'REFUSE' }
]

const itemsPerPageOptions = [
  { value: 10, title: '10' },
  { value: 25, title: '25' },
  { value: 50, title: '50' },
  { value: 100, title: '100' }
]

const stats = computed(() => [
  {
    title: 'Total accès',
    value: historique.value.length,
    icon: 'mdi-counter',
    color: 'primary'
  },
  {
    title: 'Accès autorisés',
    value: historique.value.filter(h => h.statut === 'AUTORISE').length,
    icon: 'mdi-check-circle',
    color: 'success'
  },
  {
    title: 'Accès refusés',
    value: historique.value.filter(h => h.statut === 'REFUSE').length,
    icon: 'mdi-close-circle',
    color: 'error'
  },
  {
    title: 'Aujourd\'hui',
    value: historique.value.filter(h => {
      const today = new Date().toDateString()
      return new Date(h.date_entree).toDateString() === today
    }).length,
    icon: 'mdi-calendar-today',
    color: 'info'
  }
])

const filteredHistorique = computed(() => {
  let filtered = [...historique.value]

  // Filtre par recherche
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(item =>
      (item.nom && item.nom.toLowerCase().includes(search)) ||
      (item.prenom && item.prenom.toLowerCase().includes(search)) ||
      (item.matricule_utilise && item.matricule_utilise.toLowerCase().includes(search)) ||
      (item.nom_salle && item.nom_salle.toLowerCase().includes(search))
    )
  }

  // Filtre par statut
  if (filters.value.statut) {
    filtered = filtered.filter(item => item.statut === filters.value.statut)
  }

  // Filtre par date de début
  if (filters.value.dateDebut) {
    const dateDebut = new Date(filters.value.dateDebut)
    filtered = filtered.filter(item => new Date(item.date_entree) >= dateDebut)
  }

  // Filtre par date de fin
  if (filters.value.dateFin) {
    const dateFin = new Date(filters.value.dateFin)
    dateFin.setHours(23, 59, 59, 999) // Fin de journée
    filtered = filtered.filter(item => new Date(item.date_entree) <= dateFin)
  }

  return filtered
})

const loadHistorique = async () => {
  loading.value = true
  try {
    // Simulation de données (remplacer par l'API réelle)
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    historique.value = [
      {
        id: 1,
        etudiant_id: 1,
        nom: 'MUKAMBA',
        prenom: 'Jean',
        matricule_utilise: '05/23.09319',
        salle_id: 1,
        nom_salle: 'Salle Informatique A',
        type_acces: 'ENTREE',
        statut: 'AUTORISE',
        date_entree: new Date().toISOString(),
        ip_address: '192.168.1.100',
        user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      },
      {
        id: 2,
        etudiant_id: 2,
        nom: 'NABINTU',
        prenom: 'Marie',
        matricule_utilise: '05/23.09320',
        salle_id: 2,
        nom_salle: 'Bibliothèque',
        type_acces: 'ENTREE',
        statut: 'AUTORISE',
        date_entree: new Date(Date.now() - 3600000).toISOString(),
        ip_address: '192.168.1.101',
        user_agent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
      },
      {
        id: 3,
        etudiant_id: null,
        nom: null,
        prenom: null,
        matricule_utilise: '05/23.99999',
        salle_id: 1,
        nom_salle: 'Salle Informatique A',
        type_acces: 'ENTREE',
        statut: 'REFUSE',
        date_entree: new Date(Date.now() - 7200000).toISOString(),
        ip_address: '192.168.1.102',
        user_agent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X) AppleWebKit/605.1.15'
      },
      {
        id: 4,
        etudiant_id: 1,
        nom: 'MUKAMBA',
        prenom: 'Jean',
        matricule_utilise: '05/23.09319',
        salle_id: 1,
        nom_salle: 'Salle Informatique A',
        type_acces: 'SORTIE',
        statut: 'AUTORISE',
        date_entree: new Date(Date.now() - 10800000).toISOString(),
        ip_address: '192.168.1.100',
        user_agent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
      }
    ]
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement de l\'historique', 'error')
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  // Les filtres sont appliqués automatiquement via computed
  appStore.showSnackbar('Filtres appliqués', 'success')
}

const resetFilters = () => {
  filters.value = {
    search: '',
    statut: '',
    dateDebut: '',
    dateFin: ''
  }
  appStore.showSnackbar('Filtres réinitialisés', 'info')
}

const viewDetails = (item) => {
  selectedItem.value = item
  detailsDialog.value = true
}

const exportData = () => {
  // Simulation d'export
  const data = filteredHistorique.value.map(item => ({
    Étudiant: `${item.nom || 'Inconnu'} ${item.prenom || ''}`,
    Matricule: item.matricule_utilise,
    Salle: item.nom_salle || 'Inconnue',
    Type: item.type_acces,
    Statut: item.statut,
    'Date/Heure': formatDateTime(item.date_entree)
  }))
  
  const csv = [
    Object.keys(data[0]).join(','),
    ...data.map(row => Object.values(row).join(','))
  ].join('\n')
  
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `historique_acces_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
  
  appStore.showSnackbar('Export terminé', 'success')
}

const formatDateTime = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

onMounted(() => {
  loadHistorique()
})
</script>