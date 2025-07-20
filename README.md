# SmartAccess UCB

Système de gestion d'accès pour l'Université Catholique de Bukavu

## Description

SmartAccess UCB est un système complet de gestion d'accès aux salles pour les étudiants de l'Université Catholique de Bukavu. Il permet aux administrateurs de gérer les étudiants, les salles et les autorisations d'accès de manière efficace.

## Fonctionnalités

### 🔐 Authentification Admin
- Système de login/logout sécurisé
- Protection des pages par session PHP
- Gestion des sessions avec timeout

### 👨‍🎓 Gestion des Étudiants
- Interface Vue.js dynamique pour la gestion des étudiants
- Import automatique depuis l'API UCB par matricule
- Recherche et filtrage en temps réel
- Validation des matricules (format XX/YY/ZZZ)
 Validation des matricules (format XX/YY.ZZZZZ)

### 🏢 Gestion des Salles
- Interface Vue.js pour la gestion des salles
- Informations détaillées (nom, localisation, capacité, description)
- Recherche et filtrage

### 🔑 Attribution des Accès
- **Attribution individuelle** : associer un étudiant à une salle
- **Attribution groupée** : attribution par faculté/promotion via API UCB
- Gestion des niveaux d'accès (Lecture, Écriture, Admin)
- Périodes de validité configurables

### 📊 Tableau de Bord
- Statistiques en temps réel
- Historique des accès récents
- Actions rapides
- Interface responsive

### 🔍 API de Vérification
- Endpoint REST pour vérifier les accès
- Format JSON standardisé
- Historique automatique des tentatives d'accès

## Technologies Utilisées

- **Backend** : PHP natif avec architecture MVC
- **Base de données** : MySQL
- **Frontend** : Vue.js 3 + Vuetify 3 (Material Design)
- **API** : REST avec réponses JSON
- **Intégration** : API UCB pour import des données

## Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Extensions PHP : PDO, pdo_mysql, json
- Node.js 16+ (pour le frontend)

### Étapes d'installation

1. **Cloner le projet**
   ```bash
   git clone [url-du-repo]
   cd smartaccess-ucb
   ```

2. **Installer les dépendances frontend**
   ```bash
   npm install
   ```

3. **Configurer la base de données**
   ```bash
   # Importer le script SQL
   mysql -u root -p < supabase/migrations/20250720042307_falling_lodge.sql
   ```

4. **Configurer la connexion**
   Modifier `api/config/database.php` avec vos paramètres :
   ```php
   private $host = 'localhost';
   private $db_name = 'smartaccess_ucb';
   private $username = 'votre_utilisateur';
   private $password = 'votre_mot_de_passe';
   ```

5. **Démarrer le serveur de développement**
   ```bash
   # Frontend (Vue.js + Vuetify)
   npm run dev
   
   # Backend (PHP) - via serveur web local
   # Assurez-vous que votre serveur Apache/Nginx pointe vers le dossier du projet
   ```

6. **Configurer le serveur web**
   - Pointer le DocumentRoot vers le dossier du projet
   - Activer mod_rewrite si nécessaire
   - Configurer les headers CORS (voir .htaccess)

## Structure du Projet

```
smartaccess-ucb/
├── src/                   # Frontend Vue.js + Vuetify
│   ├── components/        # Composants réutilisables
│   ├── views/            # Pages de l'application
│   ├── stores/           # Gestion d'état (Pinia)
│   ├── services/         # Services API
│   └── plugins/          # Configuration Vuetify
├── api/                  # Backend PHP
│   ├── config/           # Configuration
│   │   ├── database.php  # Connexion MySQL
│   │   └── cors.php      # Configuration CORS
│   ├── models/           # Modèles de données
│   │   ├── Student.php   # Modèle Étudiant
│   │   ├── Salle.php     # Modèle Salle
│   │   ├── Autorisation.php # Modèle Autorisation
│   │   └── HistoriqueAcces.php # Modèle Historique
│   └── endpoints/        # Points d'entrée API
│       ├── students.php  # API étudiants
│       ├── salles.php    # API salles
│       ├── autorisations.php # API autorisations
│       ├── verifier_acces.php # API vérification
│       ├── historique.php # API historique
│       └── dashboard.php # API tableau de bord
├── supabase/migrations/  # Scripts SQL
├── public/               # Fichiers publics
├── package.json          # Dépendances Node.js
├── vite.config.js        # Configuration Vite
└── .htaccess            # Configuration Apache
```

## API de Vérification d'Accès

### Endpoint
```
GET /api/endpoints/verifier_acces.php?matricule=XXX&salle_id=YYY
```

### Réponse - Accès Autorisé
```json
{
    "status": "ACCES AUTORISE",
    "etudiant": "MUKAMBA Jean",
    "salle": "Salle Informatique A",
    "matricule": "05/23.09319",
    "salle_id": 1,
    "timestamp": "2024-01-15 10:30:00"
}
```

### Réponse - Accès Refusé
```json
{
    "status": "ACCES REFUSE",
    "message": "Aucune autorisation valide trouvée",
    "matricule": "05/23.99999",
    "salle_id": 1,
    "timestamp": "2024-01-15 10:30:00"
}
```

## Intégration API UCB

### Import Étudiant
```
GET https://akhademie.ucbukavu.ac.cd/api/v1/school-students/read-by-matricule?matricule=05/23.09319
```

### Liste Facultés/Promotions
```
GET https://akhademie.ucbukavu.ac.cd/api/v1/school/entity-main-list?entity_id=undefined&promotion_id=1&traditional=undefined
```

## Comptes par Défaut

### Administrateur
- **Utilisateur** : `admin`
- **Mot de passe** : `admin123`

## Base de Données

### Tables Principales
- `admins` : Comptes administrateurs
- `etudiants` : Informations des étudiants
- `salles` : Informations des salles
- `autorisations` : Autorisations d'accès
- `historiques_acces` : Historique des tentatives d'accès

### Architecture Backend
- **Modèles** : Classes PHP pour la gestion des données (PDO)
- **Endpoints** : APIs REST pour chaque entité
- **Configuration** : Gestion centralisée de la base de données et CORS

### Architecture Frontend
- **Vue.js 3** : Framework JavaScript réactif
- **Vuetify 3** : Composants Material Design
- **Pinia** : Gestion d'état
- **Axios** : Client HTTP pour les APIs

## Sécurité

- APIs REST sécurisées avec validation des données
- Validation des entrées utilisateur
- Requêtes préparées PDO pour éviter les injections SQL
- Headers CORS configurés
- Soft delete pour préserver l'historique

## Déploiement

### Développement Local
1. **Backend** : Serveur Apache/Nginx avec PHP
2. **Frontend** : `npm run dev` (Vite dev server sur port 3000)
3. **Base de données** : MySQL local

### Production
1. **Build frontend** : `npm run build`
2. **Déployer** : Copier les fichiers sur le serveur
3. **Configuration** : Ajuster les paramètres de base de données
4. **Serveur web** : Configurer Apache/Nginx avec .htaccess


## Support

Pour toute question ou problème :
- Consulter la documentation
- Vérifier les logs d'erreur PHP
- Contacter l'équipe de développement

## Licence

Ce projet est développé pour l'Université Catholique de Bukavu.

---

**SmartAccess UCB** - Système de Gestion d'Accès
Université Catholique de Bukavu - 2024# Pro
# Pro
# Pro
