<template>
  <v-app>
    <AppNavigation />
    <AppBar />
    
    <v-main>
      <v-container fluid class="pa-6">
        <!-- En-tête -->
        <div class="d-flex justify-space-between align-center mb-6">
          <div>
            <h1 class="text-h4 font-weight-bold mb-2">Gestion des étudiants</h1>
            <p class="text-body-1 text-medium-emphasis">
              Ajouter, modifier et gérer les étudiants du système
            </p>
          </div>
          <v-btn
            color="primary"
            size="large"
            @click="openDialog()"
          >
            <v-icon start>mdi-plus</v-icon>
            Ajouter étudiant
          </v-btn>
        </div>

        <!-- Tableau des étudiants -->
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <v-icon class="me-2">mdi-account-group</v-icon>
              Étudiants ({{ students.length }})
            </div>
            <v-text-field
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Rechercher..."
              variant="outlined"
              density="compact"
              hide-details
              clearable
              style="max-width: 300px;"
            ></v-text-field>
          </v-card-title>

          <v-data-table
            :headers="headers"
            :items="students"
            :search="search"
            :loading="loading"
            no-data-text="Aucun étudiant trouvé"
            loading-text="Chargement des étudiants..."
            class="elevation-0"
          >
            <template v-slot:item.matricule="{ item }">
              <v-chip color="primary" size="small" variant="tonal">
                {{ item.matricule }}
              </v-chip>
            </template>

            <template v-slot:item.nom_complet="{ item }">
              <div class="d-flex align-center">
                <v-avatar color="primary" size="32" class="me-3">
                  <span class="text-white text-caption">
                    {{ item.nom.charAt(0) }}{{ item.prenom.charAt(0) }}
                  </span>
                </v-avatar>
                <div>
                  <div class="font-weight-medium">{{ item.nom }} {{ item.prenom }}</div>
                  <div class="text-caption text-medium-emphasis">{{ item.promotion }}</div>
                </div>
              </div>
            </template>

            <template v-slot:item.actions="{ item }">
              <v-btn
                icon="mdi-pencil"
                variant="text"
                size="small"
                @click="openDialog(item)"
              ></v-btn>
              <v-btn
                icon="mdi-delete"
                variant="text"
                size="small"
                color="error"
                @click="deleteStudent(item)"
              ></v-btn>
            </template>
          </v-data-table>
        </v-card>

        <!-- Dialog d'ajout/modification -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
          <v-card>
            <v-card-title class="d-flex align-center">
              <v-icon class="me-2">{{ editingStudent ? 'mdi-pencil' : 'mdi-plus' }}</v-icon>
              {{ editingStudent ? 'Modifier étudiant' : 'Ajouter étudiant' }}
            </v-card-title>

            <v-card-text>
              <v-form ref="form" @submit.prevent="saveStudent">
                <v-row>
                  <v-col cols="12">
                    <v-text-field
                      v-model="form.matricule"
                      label="Matricule UCB"
                      prepend-inner-icon="mdi-card-account-details"
                      variant="outlined"
                      :rules="[rules.required, rules.matricule]"
                      placeholder="Ex: 05/23.09319"
                      hint="Format: XX/YY.ZZZZZ"
                      persistent-hint
                    >
                      <template v-slot:append>
                        <v-btn
                          icon="mdi-download"
                          variant="text"
                          size="small"
                          :loading="importLoading"
                          @click="importFromUCB"
                          :disabled="!form.matricule"
                        ></v-btn>
                      </template>
                    </v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.nom"
                      label="Nom"
                      prepend-inner-icon="mdi-account"
                      variant="outlined"
                      :rules="[rules.required]"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.prenom"
                      label="Prénom"
                      prepend-inner-icon="mdi-account"
                      variant="outlined"
                      :rules="[rules.required]"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12">
                    <v-text-field
                      v-model="form.email"
                      label="Email"
                      prepend-inner-icon="mdi-email"
                      variant="outlined"
                      type="email"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.faculte"
                      label="Faculté"
                      prepend-inner-icon="mdi-school"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field
                      v-model="form.promotion"
                      label="Promotion"
                      prepend-inner-icon="mdi-calendar"
                      variant="outlined"
                    ></v-text-field>
                  </v-col>
                </v-row>
              </v-form>
            </v-card-text>

            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn
                variant="text"
                @click="closeDialog"
              >
                Annuler
              </v-btn>
              <v-btn
                color="primary"
                :loading="saving"
                @click="saveStudent"
              >
                {{ editingStudent ? 'Modifier' : 'Ajouter' }}
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Dialog de confirmation de suppression -->
        <v-dialog v-model="deleteDialog" max-width="400px">
          <v-card>
            <v-card-title class="text-h6">Confirmer la suppression</v-card-title>
            <v-card-text>
              Êtes-vous sûr de vouloir supprimer l'étudiant 
              <strong>{{ studentToDelete?.nom }} {{ studentToDelete?.prenom }}</strong> ?
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
import { ref, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { studentsAPI, ucbAPI } from '@/services/api'
import AppNavigation from '@/components/AppNavigation.vue'
import AppBar from '@/components/AppBar.vue'

const appStore = useAppStore()

const students = ref([])
const search = ref('')
const loading = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const editingStudent = ref(null)
const studentToDelete = ref(null)
const saving = ref(false)
const deleting = ref(false)
const importLoading = ref(false)
const form = ref({})

const headers = [
  { title: 'Matricule', key: 'matricule', sortable: true },
  { title: 'Nom complet', key: 'nom_complet', sortable: false },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Faculté', key: 'faculte', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false, width: '120px' }
]

const rules = {
  required: value => !!value || 'Ce champ est requis',
  matricule: value => {
    const pattern = /^\d{2}\/\d{2}\.\d{5}$/
    return pattern.test(value) || 'Format invalide (XX/YY.ZZZZZ)'
  }
}

const loadStudents = async () => {
  loading.value = true
  try {
    // Simulation de données (remplacer par l'API réelle)
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    students.value = [
      {
        id: 1,
        matricule: '05/23.09319',
        nom: 'MUKAMBA',
        prenom: 'Jean',
        email: 'jean.mukamba@ucbukavu.ac.cd',
        faculte: 'Sciences Informatiques',
        promotion: '2023-2024'
      },
      {
        id: 2,
        matricule: '05/23.09320',
        nom: 'NABINTU',
        prenom: 'Marie',
        email: 'marie.nabintu@ucbukavu.ac.cd',
        faculte: 'Sciences Informatiques',
        promotion: '2023-2024'
      },
      {
        id: 3,
        matricule: '02/22.01234',
        nom: 'BAHATI',
        prenom: 'Pierre',
        email: 'pierre.bahati@ucbukavu.ac.cd',
        faculte: 'Sciences Économiques',
        promotion: '2022-2023'
      }
    ]
  } catch (error) {
    appStore.showSnackbar('Erreur lors du chargement des étudiants', 'error')
  } finally {
    loading.value = false
  }
}

const openDialog = (student = null) => {
  editingStudent.value = student
  if (student) {
    form.value = { ...student }
  } else {
    form.value = {
      matricule: '',
      nom: '',
      prenom: '',
      email: '',
      faculte: '',
      promotion: ''
    }
  }
  dialog.value = true
}

const closeDialog = () => {
  dialog.value = false
  editingStudent.value = null
  form.value = {}
}

const importFromUCB = async () => {
  if (!form.value.matricule) {
    appStore.showSnackbar('Veuillez saisir un matricule', 'warning')
    return
  }

  importLoading.value = true
  try {
    const response = await ucbAPI.getStudent(form.value.matricule)
    
    if (response.data && response.data.data && response.data.message === "Request was successful") {
      const student = response.data.data
      form.value.nom = student.lastname || student.nom || ''
      form.value.prenom = student.firstname || student.prenom || ''
      form.value.email = student.email || ''
      
      appStore.showSnackbar('Données importées avec succès depuis UCB', 'success')
    } else {
      appStore.showSnackbar('Aucun étudiant trouvé avec ce matricule dans la base UCB', 'warning')
    }
  } catch (error) {
    appStore.showSnackbar('Erreur lors de l\'import depuis UCB', 'error')
  } finally {
    importLoading.value = false
  }
}

const saveStudent = async () => {
  // Validation du formulaire
  const formElement = document.querySelector('form')
  if (!formElement.checkValidity()) {
    return
  }

  saving.value = true
  try {
    if (editingStudent.value) {
      // Modification
      const index = students.value.findIndex(s => s.id === editingStudent.value.id)
      if (index !== -1) {
        students.value[index] = { ...form.value, id: editingStudent.value.id }
      }
      appStore.showSnackbar('Étudiant modifié avec succès', 'success')
    } else {
      // Ajout
      const newStudent = {
        ...form.value,
        id: Date.now() // Simulation d'un ID
      }
      students.value.push(newStudent)
      appStore.showSnackbar('Étudiant ajouté avec succès', 'success')
    }
    
    closeDialog()
  } catch (error) {
    appStore.showSnackbar('Erreur lors de l\'enregistrement', 'error')
  } finally {
    saving.value = false
  }
}

const deleteStudent = (student) => {
  studentToDelete.value = student
  deleteDialog.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    const index = students.value.findIndex(s => s.id === studentToDelete.value.id)
    if (index !== -1) {
      students.value.splice(index, 1)
    }
    
    appStore.showSnackbar('Étudiant supprimé avec succès', 'success')
    deleteDialog.value = false
    studentToDelete.value = null
  } catch (error) {
    appStore.showSnackbar('Erreur lors de la suppression', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadStudents()
})
</script>