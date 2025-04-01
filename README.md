# Projet Symfony Gestion

Ce projet est une application de gestion initialement développée sous Symfony 4 et migrée vers Symfony 7. Il s'agit d'une solution complète pour la gestion d'entreprise incluant la gestion des stocks, des commandes et des statistiques.

![Dashboard de l'application](docs/images/dashboard.png)

## Migration de Symfony 4 vers 7

Ce projet a été mis à jour de Symfony 4 vers Symfony 7 avec les améliorations suivantes :
- Mise à jour des dépendances vers les versions compatibles
- Refactoring du code pour utiliser les nouvelles fonctionnalités de Symfony 7
- Optimisation des performances
- Modernisation de l'interface utilisateur
- Migration vers PHP 8.2

## Intégration Continue avec GitHub Actions

Le projet utilise GitHub Actions pour l'intégration continue avec un pipeline CI/CD qui s'exécute sur les push vers la branche main et les pull requests.

Le workflow est configuré dans `.github/workflows/ci-cd.yml` et comprend deux jobs principaux :

### Job: test
- Configuration de PHP 8.2 avec les extensions nécessaires
- Validation du composer.json
- Mise en cache des dépendances Composer
- Installation des dépendances
- Exécution des tests PHPUnit
- Analyse statique avec PHPStan

### Job: build
- S'exécute uniquement après le succès des tests et sur les push vers main
- Configuration de Docker Buildx
- Construction de l'image Docker du projet

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [ "main" ]
  pull_request:
    branches: [ "main" ]
```

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Symfony CLI
- PostgreSQL 14 ou supérieur
- Docker et Docker Compose
- Redis 7.0 ou supérieur

## Installation

1. Cloner le projet :
```bash
git clone [URL_DU_REPO]
cd projet-symfony-gestion
```

2. Démarrer les conteneurs Docker :
```bash
docker-compose up -d
```

3. Installer les dépendances :
```bash
composer install
```

4. Configurer les bases de données et Redis :
- Créer un fichier `.env.local` à la racine du projet
- Configurer les variables d'environnement :
```env
DATABASE_URL="postgresql://user:password@postgres:5432/dbname?serverVersion=14&charset=utf8"
REDIS_URL="redis://redis:6379"
SENTRY_DSN="votre_dsn_sentry"  # Obtenir votre DSN depuis le dashboard Sentry
```

5. Créer la base de données :
```bash
symfony console doctrine:database:create
```

6. Exécuter les migrations :
```bash
symfony console doctrine:migrations:migrate
```

## Configuration

Les paramètres principaux sont configurés dans `config/services.yaml` :
- `uploads_directory`: Dossier pour les fichiers uploadés
- `sounds_directory`: Dossier pour les fichiers sons
- `root_directory`: Dossier racine public
- `session_max_idle_time`: Durée maximale d'inactivité de session (14400)
- `cookie_lifetime`: Durée de vie des cookies (14400)
- `redis_ttl`: Durée de vie du cache Redis pour les statistiques (3600)

### Monitoring avec Sentry

Le projet utilise Sentry pour le monitoring des erreurs et des performances. La configuration se fait via la variable d'environnement :
```env
SENTRY_DSN="votre_dsn_sentry"
```

Fonctionnalités Sentry activées :
- Capture des exceptions
- Traçage des performances
- Intégration avec Doctrine et Twig
- Monitoring des requêtes HTTP
- Support multilingue (i18n)

## Fonctionnalités

- Gestion des produits
- Gestion des catégories
- Gestion des stocks
- Gestion des utilisateurs
- Gestion des commandes avec export
- Gestion des statistiques avec mise en cache Redis
- Système de notification
- Support multilingue (i18n)
- Intégration Sentry pour le monitoring des erreurs et des performances
- Support CORS (Cross-Origin Resource Sharing)

## Développement

Pour lancer le serveur de développement :
```bash
symfony server:start
```

Pour nettoyer le cache :
```bash
symfony console cache:clear
```

## Sécurité

- Authentification requise pour l'accès à l'administration
- Gestion des rôles (ROLE_ADMIN, ROLE_SUPER_ADMIN)
- Protection CSRF activée
- Configuration CORS sécurisée

## Structure du projet

```
src/
├── Controller/         # Contrôleurs de l'application
│   └── Admin/         # Contrôleurs de l'administration
├── Entity/            # Entités Doctrine
├── Repository/        # Repositories Doctrine
├── Service/          # Services métier
├── Listener/         # Event Listeners
└── Twig/             # Extensions Twig personnalisées
config/
├── packages/         # Configuration des packages
docker/               # Configuration Docker
├── nginx/            # Configuration Nginx
├── php/             # Configuration PHP-FPM
└── redis/           # Configuration Redis
```

## Docker

Le projet utilise Docker pour l'environnement de développement. Les services disponibles sont :
- `nginx`: Serveur web
- `php`: Application PHP-FPM
- `postgres`: Base de données PostgreSQL
- `redis`: Cache Redis

Pour gérer les conteneurs :
```bash
# Démarrer les conteneurs
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs
docker-compose logs -f

# Accéder au conteneur PHP
docker-compose exec php bash
```

## Tests

Le projet utilise PHPUnit pour les tests. Pour exécuter les tests :
```bash
php bin/phpunit
```

## Qualité du code

- PHPStan pour l'analyse statique
- Rector pour les mises à niveau automatiques
- Symfony Deprecation Detector

## Maintenance

Pour mettre à jour les dépendances :
```bash
composer update
```

Pour mettre à jour la base de données :
```bash
symfony console doctrine:schema:update --force
```

## Support

Pour toute question ou problème, veuillez créer une issue dans le dépôt GitHub.
