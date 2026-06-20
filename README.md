# UTexchange — Installation et lancement

Ce document explique comment installer et lancer le projet, configurer PostgreSQL et MongoDB, et installer les dépendances PHP via Composer.

## Prérequis

- PHP 8.3+ (ou version compatible utilisée par le projet)
- Composer
- Node.js 14+ et npm
- Angular CLI (optionnel pour développement)
- PostgreSQL
- MongoDB

## Structure

Le projet contient deux parties principales :
- Backend : `backend/`
- Frontend : `frontend/`

## Variables d'environnement (backend)

Créez un fichier d'environnement dans `backend/` (par exemple `.env.example`) 

Adaptez les noms des variables selon votre configuration si le code utilise des clés différentes.

## Backend — installation

1. Ouvrir un terminal à la racine du backend :

```bash
cd backend
```

2. Installer les dépendances PHP :

```bash
composer install
```

3. Copier et configurer l'environnement :

```bash
cp .env.example .env
# puis éditez .env pour renseigner les informations PostgreSQL/MongoDB/JWT
```

4. Lancer le serveur pour développement (exemple avec le serveur PHP intégré) :

```bash
php -S 0.0.0.0:8000 -t .
```


## Frontend — installation

1. Ouvrir un terminal dans le dossier frontend :

```bash
cd frontend
```

2. Installer les dépendances :

```bash
npm install
```

4. Lancer l'application Angular en mode développement :

```bash
npm run start
# ou
ng serve --open
```

## Composer — notes

- Si `composer` n'est pas installé : suivez https://getcomposer.org/download/
- Après modification des dépendances :

```bash
composer install
composer dump-autoload -o
```

## Démarrage complet (ordre recommandé)

1. Démarrer PostgreSQL
2. Démarrer MongoDB
3. Configurer `backend/.env`
4. `cd backend && composer install`
5. Lancer le backend : `php -S 0.0.0.0:8000 -t .` (ou via Nginx/Apache)
6. `cd frontend && npm install && ng serve --open`

## Windows — procédure détaillée

Ces instructions couvrent l'installation et le lancement sous Windows (PowerShell/Invite de commandes). Elles supposent que vous avez téléchargé le projet dans `C:\xampp\htdocs\UTexchange26` ou dans un dossier de votre choix.

### Prérequis Windows

- XAMPP (ou PHP installé séparément)
- PostgreSQL (installer Windows)
- MongoDB Community Server (ou Docker)
- Composer (ajoutez `C:\xampp\php` au PATH si vous utilisez XAMPP)
- Node.js + npm

### Installer MongoDB (Windows)

1. Installez MongoDB Community Server depuis https://www.mongodb.com/try/download/community.
2. Configurez le service et démarrez-le (ou lancez `mongod` depuis PowerShell).
3. Définissez `MONGO_URI` dans `backend/.env`, par exemple `mongodb://localhost:27017` et `MONGO_DB=utexchange`.

### Configurer PHP (XAMPP) pour PostgreSQL

1. Ouvrez `C:\xampp\php\php.ini` et décommentez (ou ajoutez) :

```
extension=pgsql
extension=pdo_pgsql
```

2. Redémarrez Apache via le panneau XAMPP.

### Installer dépendances et lancer le backend

1. Ouvrez PowerShell dans `backend` :

```powershell
cd C:\chemin\vers\UTexchange26\backend
composer install
```

2. Lancer le serveur PHP (exemple avec PHP de XAMPP) :

```powershell
C:\xampp\php\php.exe -S localhost:8000 -t .
```

Ou, si vous utilisez Apache, placez le dossier `backend` dans `htdocs` et configurez un VirtualHost pointant vers `backend/index.php`.

### Installer et lancer le frontend

1. Ouvrez PowerShell dans `frontend` :

```powershell
cd C:\chemin\vers\UTexchange26\frontend
npm install
ng serve --open
```

Si `ng` n'est pas reconnu, installez Angular CLI globalement : `npm install -g @angular/cli`.

### Remarques

- Pour le service chatbot externe (Python/Flask), consultez son dossier (ex. `chatbot-nlp`) et copiez aussi son `.env.example` vers `.env`.




