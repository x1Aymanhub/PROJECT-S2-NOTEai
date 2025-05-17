# Générateur de QCM avec GitHub AI

Ce système permet de générer des QCM (Questionnaires à Choix Multiples) à partir de vos modules de cours en utilisant l'API GitHub AI avec des modèles avancés comme GPT-4.

## Prérequis

- PHP 7.4 ou supérieur
- Node.js 16 ou supérieur
- npm (gestionnaire de paquets Node.js)
- Serveur web (Apache, Nginx, etc.)
- Un token GitHub AI avec les permissions requises

## Installation

1. **Installation des dépendances Node.js**

   Ouvrez un terminal dans le répertoire `NOTEai/ai` et exécutez :

   ```bash
   npm install
   ```

   Cette commande installera les dépendances nécessaires (`@azure-rest/ai-inference` et `@azure/core-auth`).

2. **Configuration du token GitHub**

   Pour des raisons de sécurité, configurez votre token GitHub en utilisant une variable d'environnement :

   Windows:
   ```bash
   set GITHUB_TOKEN=votre_token_github
   ```

   Linux/MacOS:
   ```bash
   export GITHUB_TOKEN=votre_token_github
   ```

   **Important** : 
   - Ne partagez JAMAIS votre token GitHub
   - Assurez-vous que votre token a les permissions `models:read`
   - Ne committez pas votre token dans le code source

3. **Configuration du chemin Node.js**

   Si Node.js n'est pas dans le PATH standard, modifiez la variable `$nodePath` dans le fichier `github_qcm_api.php` :

   ```php
   $nodePath = 'C:\\chemin\\vers\\node.exe'; // Sur Windows
   ```

## Utilisation

1. Accédez à l'interface du générateur de QCM via votre navigateur
2. Sélectionnez un module dans la liste déroulante
3. Cochez les descriptions du module à inclure dans le QCM
4. Cliquez sur "Générer un QCM"
5. Le système utilisera GitHub AI pour créer un QCM adapté aux contenus sélectionnés

## Structure des fichiers

- `qcm_generator.php` : Interface utilisateur principale
- `github_qcm_api.php` : API PHP qui communique avec le script Node.js
- `github_ai.js` : Script JavaScript qui communique avec l'API GitHub AI
- `package.json` : Configuration des dépendances Node.js

## Dépannage

Si vous rencontrez l'erreur "Erreur lors de la génération du QCM", vérifiez les points suivants :

1. **Installation de Node.js**
   - Vérifiez que Node.js est correctement installé (`node --version`)
   - Version 16+ recommandée

2. **Installation des dépendances**
   - Vérifiez que `npm install` a été exécuté
   - Vérifiez la présence du dossier `node_modules`

3. **Configuration**
   - Vérifiez que le token GitHub est correctement configuré
   - Vérifiez les permissions du token
   - Sur Windows, assurez-vous que le chemin vers Node.js est correct

4. **Problèmes de modules**
   - Si vous rencontrez des erreurs liées aux modules ES, modifiez `package.json` et retirez `"type": "module"`

## Test manuel

Pour tester le script Node.js directement :

```bash
# Configuration du token (à faire une seule fois par session)
set GITHUB_TOKEN=votre_token_github  # Windows
export GITHUB_TOKEN=votre_token_github  # Linux/MacOS

# Test du chat
node github_ai.js chat "Bonjour"

# Génération d'un QCM
node github_ai.js qcm "Contenu du module" "Nom du module"
```

## Sécurité

- Ne stockez jamais votre token GitHub dans les fichiers source
- Utilisez toujours des variables d'environnement pour les informations sensibles
- Vérifiez régulièrement les permissions de votre token
- Faites des rotations régulières de votre token