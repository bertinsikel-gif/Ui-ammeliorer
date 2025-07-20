<template>
  <v-app>
    <AppNavigation />
    <AppBar />
    
    <v-main>
      <v-container fluid class="pa-6">
        <!-- En-tête -->
        <div class="mb-6">
          <h1 class="text-h4 font-weight-bold mb-2">Gestion des accès</h1>
          <p class="text-body-1 text-medium-emphasis">
            Attribution individuelle et groupée d'accès aux salles
          </p>
        </div>

        <!-- Onglets -->
        <v-tabs v-model="tab" color="primary" class="mb-6">
          <v-tab value="individual">
            <v-icon start>mdi-account</v-icon>
            Attribution individuelle
          </v-tab>
          <v-tab value="group">
            <v-icon start>mdi-account-group</v-icon>
            Attribution groupée
          </v-tab>
          <v-tab value="list">
            <v-icon start>mdi-format-list-bulleted</v-icon>
            Liste des autorisations
          </v-tab>
        </v-tabs>

        <v-window v-model="tab">
          <!-- Attribution individuelle -->
          <v-window-item value="individual">
            <v-row>
              <v-col cols="12" lg="6">
                <v-card>
                  <v-card-title class="d-flex align-center">
                    <v-icon class="me-2">mdi-account-plus</v-icon>
                    Attribuer un accès individuel
                  </v-card-title>

                  <v-card-text>
                    <v-form ref="individualForm" @submit.prevent="saveIndividualAccess">
                      <v-select
                        v-model="individualForm.etudiant_id"
                        :items="students"
                        item-title="display_name"
                        item-value="id"
                        label="Étudiant"
                        prepend-inner-icon="mdi-account"
                        variant="outlined"
                        :rules="[rules.required]"
                        class="mb-4"
                      ></v-select>

                      <v-select
                        v-model="individualForm.salle_id"
                        :items="salles"
                        item-title="nom_salle"
                        item-value="id"
                        label="Salle"
                        prepend-inner-icon="mdi-domain"
                        variant="outlined"
                        :rules="[rules.required]"
                        class="mb-4"
                      ></v-select>

                      <v-row>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="individualForm.date_debut"
                            label="Date de début"
                            type="datetime-local"
                            prepend-inner-icon="mdi-calendar-start"
                            variant="outlined"
                            :rules="[rules.required]"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="individualForm.date_fin"
                            label="Date de fin"
                            type="datetime-local"
                            prepend-inner-icon="mdi-calendar-end"
                            variant="outlined"
                            :rules="[rules.required]"
                          ></v-text-field>
                        </v-col>
                      </v-row>

                      <v-btn
                        type="submit"
                        color="primary"
                        size="large"
                        :loading="saving"
                        block
                      >
                        <v-icon start>mdi-check</v-icon>
                        Attribuer l'accès
                      </v-btn>
                    </v-form>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-window-item>

          <!-- Attribution groupée -->
          <v-window-item value="group">
            <v-row>
              <v-col cols="12" lg="8">
                <v-card>
                  <v-card-title class="d-flex align-center">
                    <v-icon class="me-2">mdi-account-group</v-icon>
                    Attribution groupée par faculté/promotion
                  </v-card-title>

                  <v-card-text>
                    <v-form ref="groupForm" @submit.prevent="saveGroupAccess">
                      <v-row>
                        <v-col cols="12" md="6">
                          <v-select
                            v-model="groupForm.faculte"
                            :items="facultes"
                            item-title="name"
                            item-value="name"
                            label="Faculté"
                            prepend-inner-icon="mdi-school"
                            variant="outlined"
                            :rules="[rules.required]"
                            @update:model-value="loadPromotions"
                          ></v-select>
                          <v-btn
                            variant="outlined"
                            size="small"
                            :loading="loadingFacultes"
                            @click="loadFacultesFromUCB"
                            class="mt-2"
                          >
                            <v-icon start>mdi-download</v-icon>
                            Charger depuis UCB
                          </v-btn>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-select
                            v-model="groupForm.promotion"
                            :items="filteredPromotions"
                            item-title="name"
                            item-value="name"
                            label="Promotion"
                            prepend-inner-icon="mdi-calendar"
                            variant="outlined"
                            :rules="[rules.required]"
                            :disabled="!groupForm.faculte"
                          ></v-select>
                        </v-col>
                      </v-row>

                      <v-select
                        v-model="groupForm.salle_id"
                        :items="salles"
                        item-title="nom_salle"
                        item-value="id"
                        label="Salle"
                        prepend-inner-icon="mdi-domain"
                        variant="outlined"
                        :rules="[rules.required]"
                        class="mb-4"
                      ></v-select>

                      <v-row>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="groupForm.date_debut"
                            label="Date de début"
                            type="datetime-local"
                            prepend-inner-icon="mdi-calendar-start"
                            variant="outlined"
                            :rules="[rules.required]"
                          ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="6">
                          <v-text-field
                            v-model="groupForm.date_fin"
                            label="Date de fin"
                            type="datetime-local"
                            prepend-inner-icon="mdi-calendar-end"
                            variant="outlined"
                            :rules="[rules.required]"
                          ></v-text-field>
                        </v-col>
                      </v-row>

                      <!-- Aperçu des étudiants concernés -->
                      <v-alert
                        v-if="groupForm.faculte && groupForm.promotion"
                        type="info"
                        variant="tonal"
                        class="mb-4"
                      >
                        <strong>Étudiants concernés:</strong> 
                        {{ getStudentsCount() }} étudiant(s) de la faculté "{{ groupForm.faculte }}" 
                        promotion "{{ groupForm.promotion }}"
                      </v-alert>

                      <v-btn
                        type="submit"
                        color="primary"
                        size="large"
                        :loading="saving"
                        block
                      >
                        <v-icon start>mdi-account-group</v-icon>
                        Attribuer l'accès groupé
                      </v-btn>
                    </v-form>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </v-window-item>

          <!-- Liste des autorisations -->
          <v-window-item value="list">
            <v-card>
              <v-card-title class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                  <v-icon class="me-2">mdi-format-list-bulleted</v-icon>
                  Autorisations actives ({{ autorisations.length }})
                </div>
                <v-btn
                  icon="mdi-refresh"
                  variant="text"
                  @click="loadAutorisations"
                ></v-btn>
              </v-card-title>

              <v-card-text>
                <v-text-field
                  v-model="searchAutorisations"
                  prepend-inner-icon="mdi-magnify"
                  label="Rechercher par étudiant, matricule ou salle..."
                  variant="outlined"
                  density="compact"
                  hide-details
                  clearable
                  class="mb-4"
                ></v-text-field>

                <v-data-table
                  :headers="autorisationHeaders"
                  :items="filteredAutorisations"
                  :loading="loadingAutorisations"
                  no-data-text="Aucune autorisation trouvée"
                  loading-text="Chargement des autorisations..."
                  class="elevation-0"
                >
                  <template v-slot:item.etudiant="{ item }">
                    <div class="d-flex align-center">
                      <v-avatar color="primary" size="32" class="me-3">
                        <span class="text-white text-caption">
                          {{ item.nom.charAt(0) }}{{ item.prenom.charAt(0) }}
                        </span>
                      </v-avatar>
                      <div>
                        <div class="font-weight-medium">{{ item.nom }} {{ item.prenom }}</div>
                        <v-chip color="primary" size="x-small" variant="tonal">
                          {{ item.matricule }}
                        </v-chip>
                      </div>
                    </div>
                  </template>

                  <template v-slot:item.periode="{ item }">
                    <div class="text-caption">
                      Du {{ formatDate(item.date_debut) }}<br>
                      Au {{ formatDate(item.date_fin) }}
                    </div>
                  </template>

                  <template v-slot:item.statut="{ item }">
                    <v-chip
                      :color="item.actif ? 'success' : 'error'"
                      size="small"
                      variant="tonal"
                    >
                      {{ item.actif ? 'Active' : 'Inactive' }}
                    </v-chip>
                  </template>

                  <template v-slot:item.actions="{ item }">
                    <v-btn
                      icon="mdi-cancel"
                      variant="text"
                      size="small"
                      color="error"
                      @click="revokeAccess(item)"
                    ></v-btn>
                  </template>
                </v-data-table>
              </v-card-text>
            </v-card>
          </v-window-item>
        </v-window>
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
import { ucbAPI } from '@/services/api'
import AppNavigation from '@/components/AppNavigation.vue'
import AppBar from '@/components/AppBar.vue'

const appStore = useAppStore()

const tab = ref('individual')
const saving = ref(false)
const loadingAutorisations = ref(false)
const loadingFacultes = ref(false)
const searchAutorisations = ref('')

const students = ref([])
const salles = ref([])
const autorisations = ref([])
const facultes = ref([])
const promotions = ref([])

const individualForm = ref({
  etudiant_id: '',
  salle_id: '',
  date_debut: '',
  date_fin: ''
})

const groupForm = ref({
  faculte: '',
  promotion: '',
  salle_id: '',
  date_debut: '',
  date_fin: ''
})

const rules = {
  required: value => !!value || 'Ce champ est requis'
}

const autorisationHeaders = [
  { title: 'Étudiant', key: 'etudiant', sortable: false },
  { title: 'Salle', key: 'nom_salle', sortable: true },
  { title: 'Période', key: 'periode', sortable: false },
  { title: 'Statut', key: 'statut', sortable: false },
  { title: 'Actions', key: 'actions', sortable: false, width: '100px' }
]

const filteredPromotions = computed(() => {
  if (!groupForm.value.faculte) return []
  
  const selectedFaculte = facultes.value.find(f => f.name === groupForm.value.faculte)
  if (selectedFaculte) {
    return promotions.value.filter(promotion => 
      promotion.entityId === selectedFaculte.id
    )
  }
  return []
})

const filteredAutorisations = computed(() => {
  if (!searchAutorisations.value) return autorisations.value
  
  const term = searchAutorisations.value.toLowerCase()
  return autorisations.value.filter(auth => 
    auth.nom.toLowerCase().includes(term) ||
    auth.prenom.toLowerCase().includes(term) ||
    auth.matricule.toLowerCase().includes(term) ||
    auth.nom_salle.toLowerCase().includes(term)
  )
})

const loadStudents = async () => {
  // Simulation de données
  students.value = [
    {
      id: 1,
      matricule: '05/23.09319',
      nom: 'MUKAMBA',
      prenom: 'Jean',
      display_name: '05/23.09319 - MUKAMBA Jean',
      faculte: 'Sciences Informatiques',
      promotion: '2023-2024'
    },
    {
      id: 2,
      matricule: '05/23.09320',
      nom: 'NABINTU',
      prenom: 'Marie',
      display_name: '05/23.09320 - NABINTU Marie',
      faculte: 'Sciences Informatiques',
      promotion: '2023-2024'
    }
  ]
}

const loadSalles = async () => {
  // Simulation de données
  salles.value = [
    { id: 1, nom_salle: 'Salle Informatique A', localisation: 'Bâtiment Sciences - 1er étage' },
    { id: 2, nom_salle: 'Amphithéâtre Central', localisation: 'Bâtiment Principal - RDC' },
    { id: 3, nom_salle: 'Laboratoire Chimie', localisation: 'Bâtiment Sciences - 2ème étage' }
  ]
}

const loadAutorisations = async () => {
  loadingAutorisations.value = true
  try {
    // Simulation de données
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    autorisations.value = [
      {
        id: 1,
        nom: 'MUKAMBA',
        prenom: 'Jean',
        matricule: '05/23.09319',
        nom_salle: 'Salle Informatique A',
        date_debut: '2024-01-01T08:00:00',
        date_fin: '2024-12-31T18:00:00',
        actif: true
      },
      {
        id: 2,
        nom: 'NABINTU',
        prenom: 'Marie',
        matricule: '05/23.09320',
        nom_salle: 'Bibliothèque',
        date_debut: '2024-01-01T08:00:00',
        date_fin: '2024-12-31T20:00:00',
        actif: true
      }
    ]
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement des autorisations', 'error')
  } finally {
    loadingAutorisations.value = false
  }
}

const loadFacultesFromUCB = async () => {
  loadingFacultes.value = true
  try {
    const response = await ucbAPI.getFaculties()
    
    if (response.data && response.data.data && response.data.message === "Request was successful") {
      facultes.value = response.data.data.entities.map(entity => ({
        id: entity.id,
        name: entity.label || entity.title
      }))
      
      promotions.value = response.data.data.promotions.map(promotion => ({
        id: promotion.id,
        name: promotion.label || promotion.title,
        entityId: promotion.entityId
      }))
      
      appStore.showSnackbar('Données UCB chargées avec succès', 'success')
    } else {
      appStore.showSnackbar('Erreur dans la structure des données UCB', 'error')
    }
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement des données UCB', 'error')
  } finally {
    loadingFacultes.value = false
  }
}

const loadPromotions = () => {
  groupForm.value.promotion = ''
}

const getStudentsCount = () => {
  if (!groupForm.value.faculte || !groupForm.value.promotion) return 0
  
  return students.value.filter(etudiant => 
    etudiant.faculte === groupForm.value.faculte && 
    etudiant.promotion === groupForm.value.promotion
  ).length
}

const setDefaultDates = () => {
  const now = new Date()
  const nextMonth = new Date(now)
  nextMonth.setMonth(nextMonth.getMonth() + 1)

  const nowString = now.toISOString().slice(0, 16)
  const nextMonthString = nextMonth.toISOString().slice(0, 16)

  individualForm.value.date_debut = nowString
  individualForm.value.date_fin = nextMonthString
  groupForm.value.date_debut = nowString
  groupForm.value.date_fin = nextMonthString
}

const saveIndividualAccess = async () => {
  saving.value = true
  try {
    // Simulation de sauvegarde
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    appStore.showSnackbar('Accès individuel attribué avec succès', 'success')
    
    // Reset form
    individualForm.value = {
      etudiant_id: '',
      salle_id: '',
      date_debut: '',
      date_fin: ''
    }
    setDefaultDates()
  } catch (error) {
    appStore.showSnackbar('Erreur lors de l\'attribution', 'error')
  } finally {
    saving.value = false
  }
}

const saveGroupAccess = async () => {
  saving.value = true
  try {
    // Simulation de sauvegarde
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    const count = getStudentsCount()
    appStore.showSnackbar(`Accès groupé attribué à ${count} étudiant(s)`, 'success')
    
    // Reset form
    groupForm.value = {
      faculte: '',
      promotion: '',
      salle_id: '',
      date_debut: '',
      date_fin: ''
    }
    setDefaultDates()
  } catch (error) {
    appStore.showSnackbar('Erreur lors de l\'attribution groupée', 'error')
  } finally {
    saving.value = false
  }
}

const revokeAccess = async (autorisation) => {
  if (!confirm(`Révoquer l'accès de ${autorisation.nom} ${autorisation.prenom} à ${autorisation.nom_salle} ?`)) {
    return
  }

  try {
    // Simulation de révocation
    await new Promise(resolve => setTimeout(resolve, 500))
    
    const index = autorisations.value.findIndex(a => a.id === autorisation.id)
    if (index !== -1) {
      autorisations.value.splice(index, 1)
    }
    
    appStore.showSnackbar('Accès révoqué avec succès', 'success')
  } catch (error) {
    appStore.showSnackbar('Erreur lors de la révocation', 'error')
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
  loadStudents()
  loadSalles()
  loadAutorisations()
  setDefaultDates()
})
</script>