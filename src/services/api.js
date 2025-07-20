import axios from 'axios'

const api = axios.create({
  baseURL: '/api/endpoints',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// Intercepteurs pour la gestion des erreurs
api.interceptors.response.use(
  response => response,
  error => {
    console.error('Erreur API:', error)
    return Promise.reject(error)
  }
)

// Services API
export const authAPI = {
  login: (credentials) => api.post('/auth.php', credentials)
}

export const studentsAPI = {
  getAll: () => api.get('/students.php'),
  getById: (id) => api.get(`/students.php?id=${id}`),
  search: (term) => api.get(`/students.php?search=${term}`),
  create: (data) => api.post('/students.php', data),
  update: (data) => api.put('/students.php', data),
  delete: (id) => api.delete('/students.php', { data: { id } })
}

export const sallesAPI = {
  getAll: () => api.get('/salles.php'),
  getById: (id) => api.get(`/salles.php?id=${id}`),
  create: (data) => api.post('/salles.php', data),
  update: (data) => api.put('/salles.php', data),
  delete: (id) => api.delete('/salles.php', { data: { id } })
}

export const autorisationsAPI = {
  getAll: () => api.get('/autorisations.php'),
  create: (data) => api.post('/autorisations.php', data),
  delete: (id) => api.delete('/autorisations.php', { data: { id } })
}

export const accessAPI = {
  verify: (matricule, salle_id) => api.get(`/verifier_acces.php?matricule=${matricule}&salle_id=${salle_id}`)
}

export const historiqueAPI = {
  getAll: (limit = 100) => api.get(`/historique.php?limit=${limit}`),
  getStats: () => api.get('/historique.php?stats=1')
}

export const dashboardAPI = {
  getData: () => api.get('/dashboard.php')
}

export const ucbAPI = {
  getStudent: (matricule) => axios.get(`https://akhademie.ucbukavu.ac.cd/api/v1/school-students/read-by-matricule?matricule=${matricule}`),
  getFaculties: () => axios.get('https://akhademie.ucbukavu.ac.cd/api/v1/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined')
}

export default api