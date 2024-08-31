# Projet Live Question

Ce projet est une création d’un mini réseau social basé sur des questions ouvertes.
Ce README fournit des instructions détaillées pour l'installation, la configuration et l'utilisation du projet.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants :

- PHP 8.2 ou supérieur
- Composer (gestionnaire de dépendances PHP)
- Symfony CLI (facultatif, mais recommandé pour certaines commandes)
- Un serveur web local (Apache, Nginx, etc.) ou le serveur intégré de Symfony
- Une base de données MySQL.

## Installation

vérifiez l'installation de symfony en exécutant :
symfony -v

Cloner le Dépôt

Clonez le dépôt GitHub du projet sur votre machine locale en utilisant la commande suivante :

git clone https://github.com/BR4NJO/Live_Question/edit/master


Ensuite, dans le terminal de votre éditeur de code, pour vous rendre dans le dossier concerné :
cd Live_Question  

Installez les dépendances PHP requises à l'aide de Composer :
composer install

Ouvrez le fichier .env et configurez les paramètres de votre base de données et autres variables d'environnement si nécessaires :
DATABASE_URL="mysql://root@127.0.0.1:3306/Live_Question?serverVersion=10.4.28-MariaDB&charset=utf8mb4"

Créez la base de données et exécutez les migrations :
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate


Installer les Actifs Frontend (Facultatif)
Si votre projet utilise des actifs frontend (CSS, JS, etc.), installez-les et compilez-les :
npm install
npm run dev


Pour démarrer le serveur de développement intégré Symfony, utilisez la commande suivante :
symfony server:start











