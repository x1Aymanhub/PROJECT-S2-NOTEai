# NOTEai 📚

## Description
NOTEai est une plateforme intelligente dédiée à la gestion des notes, des modules et des QCM interactifs. Le système intègre l'intelligence artificielle pour personnaliser l'expérience d'apprentissage des étudiants.

## Fonctionnalités Principales
- Gestion des notes et des modules
- QCM interactifs
- Organisation automatique des notes via IA
- Interface utilisateur intuitive
- Système de gestion des coefficients
- Organisation par semestre

## Structure du Projet
```
NOTEai/
├── admin/              # Administration du système
│   ├── dashboard.php   # Tableau de bord administrateur
│   └── users.php       # Gestion des utilisateurs
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

## Technologies Utilisées
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Base de données**: MySQL 8.0
- **API**: RESTful Architecture
- **Sécurité**: JWT, HTTPS
- **IA**: Algorithmes de personnalisation

## Configuration Requise
1. Serveur Web Apache/Nginx
2. PHP 8.0 ou supérieur
3. MySQL 8.0 ou supérieur
4. Extensions PHP requises:
   - PDO
   - MySQLi
   - JSON
   - Session

## Installation
1. Clonez ce dépôt
2. Configurez votre serveur web
3. Importez la base de données depuis le dossier `sql/`
4. Configurez les paramètres de connexion dans `config/database.php`
5. Lancez l'application dans votre navigateur

## Contribution
1. Forkez ce dépôt
2. Créez une branche pour vos modifications
3. Effectuez vos changements
4. Soumettez une pull request

## Licence
Ce projet est sous licence MIT.

## Support
Pour toute question ou assistance, veuillez ouvrir une issue dans le dépôt.

---
Made with ❤️ by l'équipe NOTEai SAMI AND AYMAN 
