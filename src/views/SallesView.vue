<template>
  <v-app>
    <AppNavigation />
    <AppBar />
    
    <v-main>
      <v-container fluid class="pa-6">
        <!-- En-tête -->
        <div class="d-flex justify-space-between align-center mb-6">
          <div>
            <h1 class="text-h4 font-weight-bold mb-2">Gestion des salles</h1>
            <p class="text-body-1 text-medium-emphasis">
              Ajouter, modifier et gérer les salles du système
            </p>
          </div>
          <v-btn
            color="primary"
            size="large"
            @click="openDialog()"
          >
            <v-icon start>mdi-plus</v-icon>
            Ajouter salle
          </v-btn>
        </div>

        <!-- Grille des salles -->
        <v-row>
          <v-col
            v-for="salle in filteredSalles"
            :key="salle.id"
            cols="12"
            sm="6"
            lg="4"
            xl="3"
          >
            <v-card class="h-100" hover>
              <v-card-title class="d-flex align-center justify-space-between">
                <div class="text-truncate">{{ salle.nom_salle }}</div>
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
                    <v-list-item @click="openDialog(salle)">
                      <template v-slot:prepend>
                        <v-icon>mdi-pencil</v-icon>
                      </template>
                      <v-list-item-title>Modifier</v-list-item-title>
                    </v-list-item>
                    <v-list-item @click="deleteSalle(salle)" class="text-error">
                      <template v-slot:prepend>
                        <v-icon>mdi-delete</v-icon>
                      </template>
                      <v-list-item-title>Supprimer</v-list-item-title>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </v-card-title>

              <v-card-text>
                <div class="mb-3">
                  <v-chip
                    color="primary"
                    size="small"
                    variant="tonal"
                    prepend-icon="mdi-map-marker"
                  >
                    {{ salle.localisation || 'Localisation non spécifiée' }}
                  </v-chip>
                </div>

                <div class="mb-3" v-if="salle.capacite">
                  <div class="d-flex align-center">
                    <v-icon size="16" class="me-2">mdi-account-group</v-icon>
                    <span class="text-body-2">Capacité: {{ salle.capacite }} personnes</span>
                  </div>
                </div>

                <p class="text-body-2 text-medium-emphasis" v-if="salle.description">
                  {{ salle.description.length > 100 ? salle.description.substring(0, 100) + '...' : salle.description }}
                </p>
              </v-card-text>

              <v-card-actions>
                <v-chip
                  color="success"
                  size="small"
                  variant="tonal"
                  prepend-icon="mdi-check-circle"
                >
                  Active
                </v-chip>
                <v-spacer></v-spacer>
                <v-btn
                  icon="mdi-eye"
                  variant="text"
                  size="small"
                  @click="viewSalle(salle)"
                ></v-btn>
              </v-card-actions>
            </v-card>
          </v-col>

          <!-- Message si aucune salle -->
          <v-col v-if="salles.length === 0 && !loading" cols="12">
            <v-card class="text-center pa-8">
              <v-icon size="64" color="grey-lighten-1" class="mb-4">mdi-domain-off</v-icon>
              <h3 class="text-h6 mb-2">Aucune salle trouvée</h3>
              <p class="text-body-2 text-medium-emphasis mb-4">
                Commencez par ajouter votre première salle
              </p>
              <v-btn color="primary" @click="openDialog()">
                <v-icon start>mdi-plus</v-icon>
                Ajouter une salle
              </v-btn>
            </v-card>
          </v-col>
        </v-row>

        <!-- Barre de recherche flottante -->
        <v-fab
          v-if="salles.length > 0"
          location="bottom end"
          size="large"
          color="primary"
          icon="mdi-magnify"
          @click="searchDialog = true"
        ></v-fab>

        <!-- Dialog de recherche -->
        <v-dialog v-model="searchDialog" max-width="400px">
          <v-card>
            <v-card-title>Rechercher une salle</v-card-title>
            <v-card-text>
              <v-text-field
                v-model="search"
                label="Nom ou localisation"
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                clearable
                autofocus
              ></v-text-field>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="text" @click="searchDialog = false">Fermer</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Dialog d'ajout/modification -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2">{{ editingSalle ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
              {{ editingSalle ? 'Modifier salle' : 'Ajouter salle' }}
            </v-card-title>

            <v-card-text>
              <v-form ref="form" @submit.prevent="saveSalle">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.nom_salle"
                      label="Nom de la salle"
                      prepend-inner-icon="mdi-domain"
                      variant="outlined"
                      :rules="[rules.required]"
                      placeholder="Ex: Salle Informatique A"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12">
                    <v-text-field
                      v-model="form.localisation"
                      label="Localisation"
                      prepend-inner-icon="mdi-map-marker"
                      variant="outlined"
                      placeholder="Ex: Bâtiment Sciences - 1er étage"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.capacite"
                      label="Capacité"
                      prepend-inner-icon="mdi-account-group"
                      variant="outlined"
                      type="number"
                      min="1"
                      placeholder="Ex: 30"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12">
                    <v-textarea
                      v-model="form.description"
                      label="Description"
                      prepend-inner-icon="mdi-text"
                      variant="outlined"
                      rows="3"
                      placeholder="Description de la salle et de ses équipements..."
                    ></v-textarea>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="text" @click="closeDialog">Annuler</v-btn>
              <v-btn
                color="primary"
                :loading="saving"
                @click="saveSalle"
              >
                {{ editingSalle ? 'Modifier' : 'Ajouter' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Dialog de confirmation de suppression -->
        <v-dialog v-model="deleteDialog" max-width="400px">
          <v-card>
            <v-card-title class="text-h6">Confirmer la suppression</v-card-title>
            <v-card-text>
              Êtes-vous sûr de vouloir supprimer la salle 
              <strong>{{ salleToDelete?.nom_salle }}</strong> ?
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
              <v-btn color="error" :loading="deleting" @click="confirmDelete">Supprimer</v-btn>
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

const salles = ref([])
const search = ref('')
const loading = ref(false)
const dialog = ref(false)
const searchDialog = ref(false)
const deleteDialog = ref(false)
const editingSalle = ref(null)
const salleToDelete = ref(null)
const saving = ref(false)
const deleting = ref(false)
const form = ref({})

const rules = {
  required: value => !!value || 'Ce champ est requis'
}

const filteredSalles = computed(() => {
  if (!search.value) return salles.value
  
  const searchTerm = search.value.toLowerCase()
  return salles.value.filter(salle => 
    salle.nom_salle.toLowerCase().includes(searchTerm) ||
    (salle.localisation && salle.localisation.toLowerCase().includes(searchTerm))
  )
})

const loadSalles = async () => {
  loading.value = true
  try {
    // Simulation de données (remplacer par l'API réelle)
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    salles.value = [
      {
        id: 1,
        nom_salle: 'Salle Informatique A',
        localisation: 'Bâtiment Sciences - 1er étage',
        description: 'Salle équipée de 30 ordinateurs avec connexion internet haut débit',
        capacite: 30
      },
      {
        id: 2,
        nom_salle: 'Amphithéâtre Central',
        localisation: 'Bâtiment Principal - RDC',
        description: 'Grand amphithéâtre pour conférences et cours magistraux',
        capacite: 200
      },
      {
        id: 3,
        nom_salle: 'Laboratoire Chimie',
        localisation: 'Bâtiment Sciences - 2ème étage',
        description: 'Laboratoire de chimie générale avec équipements de sécurité',
        capacite: 25
      },
      {
        id: 4,
        nom_salle: 'Bibliothèque Principale',
        localisation: 'Bâtiment Central - 1er étage',
        description: 'Espace de lecture et de recherche avec accès aux ressources numériques',
        capacite: 100
      },
      {
        id: 5,
        nom_salle: 'Salle de Réunion',
        localisation: 'Administration - 2ème étage',
        description: 'Salle pour réunions administratives et rencontres',
        capacite: 15
      }
    ]
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement des salles', 'error')
  } finally {
    loading.value = false
  }
}

const openDialog = (salle = null) => {
  editingSalle.value = salle
  if (salle) {
    form.value = { ...salle }
  } else {
    form.value = {
      nom_salle: '',
      localisation: '',
      description: '',
      capacite: ''
    }
  }
  dialog.value = true
}

const closeDialog = () => {
  dialog.value = false
  editingSalle.value = null
  form.value = {}
}

const saveSalle = async () => {
  // Validation du formulaire
  if (!form.value.nom_salle) {
    appStore.showSnackbar('Le nom de la salle est requis', 'warning')
    return
  }

  saving.value = true
  try {
    if (editingSalle.value) {
      // Modification
      const index = salles.value.findIndex(s => s.id === editingSalle.value.id)
      if (index !== -1) {
        salles.value[index] = { ...form.value, id: editingSalle.value.id }
      }
      appStore.showSnackbar('Salle modifiée avec succès', 'success')
    } else {
      // Ajout
      const newSalle = {
        ...form.value,
        id: Date.now() // Simulation d'un ID
      }
      salles.value.push(newSalle)
      appStore.showSnackbar('Salle ajoutée avec succès', 'success')
    }
    
    closeDialog()
  } catch (error) {
    appStore.showSnackbar('Erreur lors de l\'enregistrement', 'error')
  } finally {
    saving.value = false
  }
}

const deleteSalle = (salle) => {
  salleToDelete.value = salle
  deleteDialog.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    const index = salles.value.findIndex(s => s.id === salleToDelete.value.id)
    if (index !== -1) {
      salles.value.splice(index, 1)
    }
    
    appStore.showSnackbar('Salle supprimée avec succès', 'success')
    deleteDialog.value = false
    salleToDelete.value = null
  } catch (error) {
    appStore.showSnackbar('Erreur lors de la suppression', 'error')
  } finally {
    deleting.value = false
  }
}

const viewSalle = (salle) => {
  // Ouvrir un dialog de détails ou naviguer vers une page de détails
  appStore.showSnackbar(`Affichage des détails de ${salle.nom_salle}`, 'info')
}

onMounted(() => {
  loadSalles()
})
</script>