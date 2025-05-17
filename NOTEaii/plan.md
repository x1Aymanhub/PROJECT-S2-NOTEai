# NOTEai

## Description
NOTEai est une application web permettant de prendre, organiser et rechercher des notes efficacement grâce à l'intelligence artificielle.

## Installation
1. Clonez ce dépôt :  
   `git clone https://github.com/votre-utilisateur/NOTEai.git`
2. Placez le dossier dans `c:\xampp\htdocs\`.
3. Assurez-vous que XAMPP (Apache et MySQL) est lancé.
4. Accédez à `http://localhost/888/NOTEai` dans votre navigateur.

## Usage
- Ouvrez l'application dans votre navigateur.
- Créez, modifiez et recherchez vos notes.
- Utilisez les fonctionnalités d'IA pour organiser automatiquement vos notes.

## Contributing
1. Forkez ce dépôt et créez une branche pour vos modifications.
2. Effectuez vos changements et soumettez une pull request.
3. Merci de respecter le style de code et d'ajouter des tests si nécessaire.

## License
Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus d'informations.

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

## Composants Principaux

### 1. Interface Utilisateur
- **index.html**: Page d'accueil avec login
- **register.html**: Inscription des nouveaux utilisateurs
- **dashboard.php**: Interface principale après connexion

### 2. Administration (/admin)
- Gestion des utilisateurs
- Supervision des modules
- Statistiques et rapports

### 3. Gestion des Modules (/MOD)
- Création et édition de modules
- Attribution des coefficients
- Organisation par semestre

### 4. API RESTful (/api)
- Endpoints sécurisés
- Documentation Swagger
- Gestion des requêtes CRUD

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

## Configuration Requise

1. Serveur Web Apache/Nginx
2. PHP 8.0 ou supérieur
3. MySQL 8.0 ou supérieur
4. Extensions PHP requises:
   - PDO
   - MySQLi
   - JSON
   - Session
   ## THE END 

   ai : 

   api key : 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

   url : https://openrouter.ai/api/v1


# PROJET-WEB-S2-NOTEai
# PROJET-WEB-S2-NOTEai NOTEai est une plateforme intelligente dédiée à la gestion des notes, des modules et des QCM interactifs. Le système intègre l'intelligence artificielle pour personnaliser l'expérience d'apprentissage des étudiants.
