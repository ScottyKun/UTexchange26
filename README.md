# UTexchange — Installation et lancement

Ce document explique comment installer et lancer le projet (backend + frontend), configurer PostgreSQL et MongoDB, et installer les dépendances PHP via Composer.

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

Créez un fichier d'environnement dans `backend/` (par exemple `.env`) et définissez au minimum :

```
PG_HOST=localhost
PG_PORT=5432
PG_DATABASE=utexchange_db
PG_USER=utexchange_user
PG_PASSWORD=secret

MONGO_URI=mongodb://localhost:27017/utexchange

JWT_SECRET=change_this_secret
APP_ENV=development
APP_DEBUG=true
```

Adaptez les noms des variables selon votre configuration si le code utilise des clés différentes.

## PostgreSQL — installation rapide

Sur Debian/Ubuntu :

```bash
sudo apt update
sudo apt install postgresql postgresql-contrib
```

Créer une base et un utilisateur (exemple) :

```bash
sudo -u postgres psql
CREATE USER utexchange_user WITH PASSWORD 'secret';
CREATE DATABASE utexchange_db OWNER utexchange_user;
GRANT ALL PRIVILEGES ON DATABASE utexchange_db TO utexchange_user;
\q
```

Si vous préférez Docker :

```bash
docker run -d --name utex-pg -e POSTGRES_USER=utexchange_user -e POSTGRES_PASSWORD=secret -e POSTGRES_DB=utexchange_db -p 5432:5432 postgres:15
```

## MongoDB — installation rapide

Sur Debian/Ubuntu :

```bash
sudo apt install -y mongodb
sudo systemctl start mongodb
sudo systemctl enable mongodb
```

Via Docker :

```bash
docker run -d --name utex-mongo -p 27017:27017 -v mongo-data:/data/db mongo:6
```

Testez la connexion avec la chaîne `MONGO_URI` indiquée dans votre `.env`.

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

Remarque : en production, utilisez Nginx/Apache et configurez le vhost pointant vers `backend/index.php`.

## Frontend — installation

1. Ouvrir un terminal dans le dossier frontend :

```bash
cd frontend
```

2. Installer les dépendances :

```bash
npm install
```

3. Configurer l'URL de l'API :

Modifiez `src/environments/environment.ts` et `src/environments/environment.prod.ts` pour définir la variable `apiUrl` (par exemple `http://localhost:8000` ou `http://localhost:8000/api`).

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

### Copier et configurer les fichiers d'environnement

1. Ouvrez PowerShell dans le dossier `backend` puis copiez l'exemple d'environnement :

```powershell
cd C:\chemin\vers\UTexchange26\backend
copy .env.example .env
# puis ouvrez .env dans un éditeur (Notepad, VS Code) et remplissez les valeurs
```

Le fichier `backend/.env.example` contient au minimum ces clés :

```
DB_HOST=
DB_NAME=
DB_PORT=
DB_USER=
DB_PASS=

# JWT
JWT_SECRET=
JWT_EXPIRY=

# MongoDB
MONGO_URI=
MONGO_DB=

# Angular (CORS)
ANGULAR_URL=
```

Remplissez-les avec vos paramètres PostgreSQL / MongoDB et votre `JWT_SECRET`.

### Installer PostgreSQL & créer la base

1. Installez PostgreSQL via l'installateur Windows (https://www.postgresql.org/download/windows/).
2. Lancez `pgAdmin` ou utilisez `psql` pour créer la base et l'utilisateur :

```powershell
psql -U postgres
CREATE USER utexchange_user WITH PASSWORD 'secret';
CREATE DATABASE utexchange_db OWNER utexchange_user;
GRANT ALL PRIVILEGES ON DATABASE utexchange_db TO utexchange_user;
\q
```

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

### Remarques Windows

- Si vous utilisez des chemins contenant des espaces, encadrez-les de guillemets.
- Exécutez PowerShell en administrateur si vous rencontrez des permissions lors de l'installation de services.
- Pour la connexion DB, vérifiez que PostgreSQL écoute sur `localhost:5432` et que le pare-feu Windows autorise la connexion si nécessaire.
- Si vous avez un service chatbot externe (Python/Flask), consultez son dossier (ex. `chatbot-nlp`) et copiez aussi son `.env.example` vers `.env`.


## Remarques et dépannage

- Vérifiez les logs du backend (sortie console ou fichiers de logs) pour les erreurs de connexion DB.
- Assurez-vous que les ports ne sont pas en conflit (Postgres 5432, MongoDB 27017, backend 8000, frontend 4200 par défaut).
- Pour tester rapidement les bases, utilisez `psql` pour Postgres et `mongosh` pour MongoDB.

## Questions
Si vous voulez, je peux :
- ajouter un `docker-compose.yml` pour démarrer PostgreSQL + MongoDB + backend + frontend ensemble
- ajuster le README avec des variables d'environnement exactes lues dans le code
