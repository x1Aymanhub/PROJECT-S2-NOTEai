
# PROJET-WEB-S2-NOTEai  est une plateforme intelligente dédiée à la gestion des notes, des modules et des QCM interactifs. Le système intègre l'intelligence artificielle pour personnaliser l'expérience d'apprentissage des étudiants.

                                                                                Made with ❤️ 
                                                                        by l'équipe NOTEai SAMI AND AYMAN 


## Fonctionnalités Principales
- Gestion des notes et des modules 
- QCM interactifs
- Organisation automatique des notes via IA
- Interface utilisateur intuitive
- Système de gestion des coefficients
- Organisation par semestre
- Analyse intelligente des notes avec IA
- Suggestions personnalisées d'apprentissage

## Structure du Projet
```
NOTEai/
├── admin/              # Administration du système
│   ├── dashboard.php   # Tableau de bord administrateur
│   └── users.php       # Gestion des utilisateurs
│
├── ai/                # Module d'Intelligence Artificielle
│   ├── ai.php        # Logique principale de l'IA
│   ├── ai.js         # Interface utilisateur IA
│   ├── ai.css        # Styles spécifiques à l'IA
│   ├── config.js     # Configuration de l'IA
│   └── README.md     # Documentation spécifique à l'IA
│
├── assets/            # Ressources statiques
│   ├── css/          # Styles CSS
│   ├── js/           # Scripts JavaScript
│   └── images/       # Images et icônes
│
├── config/           # Configuration
│   ├── database.php  # Configuration de la base de données
│   └── config.php    # Configuration générale
│
├── MOD/              # Gestion des Modules
│   ├── create.php    # Création de modules
│   ├── edit.php      # Édition de modules
│   └── style.css     # Styles spécifiques aux modules
│
├── php/             # Classes et fonctions PHP
│   ├── auth/        # Authentification
│   ├── database/    # Interactions base de données
│   └── utils/       # Fonctions utilitaires
│
├── sql/            # Scripts SQL
│   ├── schema.sql  # Structure de la base de données
│   └── data.sql    # Données initiales
│
├── dashboard.php   # Interface principale utilisateur
├── index.html     # Page d'accueil
└── register.html  # Page d'inscription
```
# Composants Principaux

### 1. Interface Utilisateur
- **index.html**: Page d'accueil avec login
- **register.html**: Inscription des nouveaux utilisateurs


### 2. Administration (/admin)
- Gestion des utilisateurs
### 3. Gestion des Modules (/MOD)
- Création et édition de modules
- Création des description dans chaque module 
- Organisation par semestre

### 4. API RESTful (/api)
- Création des descriptions avec ai 

### 5. Base de Données (/sql)
- Tables relationnelles
- Procédures stockées
- Indexation optimisée

## Technologies Utilisées

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Base de données**: MySQL 8.0
- **API**: RESTful Architecture
- **Sécurité**: JWT, HTTPS
- **IA**: Algorithmes de personnalisation

   api key : AIzaSyCccJymc412DUyuf7tXEDr-0LSIUgLJJaQ

   url : https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=GEMINI_API_KEY



## THE END 

  



