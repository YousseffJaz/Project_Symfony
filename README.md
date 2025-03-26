# Projet Symfony Gestion

Ce projet est une application de gestion développée avec Symfony 7.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Symfony CLI
- PostgreSQL 14 ou supérieur
- Elasticsearch 8.0 ou supérieur
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

4. Configurer les bases de données, Redis et Elasticsearch :
- Créer un fichier `.env.local` à la racine du projet
- Configurer les variables d'environnement :
```env
DATABASE_URL="postgresql://user:password@postgres:5432/dbname?serverVersion=14&charset=utf8"
ELASTICSEARCH_URL="http://elasticsearch:9200"
REDIS_URL="redis://redis:6379"
```

5. Créer la base de données :
```bash
symfony console doctrine:database:create
```

6. Exécuter les migrations :
```bash
symfony console doctrine:migrations:migrate
```

## API Platform

Le projet utilise API Platform pour exposer une API REST.

### API REST

Les endpoints disponibles :

- Catégories (PostgreSQL) :
  - GET `/api/categories` : Liste toutes les catégories
  - GET `/api/categories/{id}` : Récupère une catégorie spécifique
  - POST `/api/categories` : Crée une nouvelle catégorie
  - PUT `/api/categories/{id}` : Met à jour une catégorie
  - DELETE `/api/categories/{id}` : Supprime une catégorie

- Produits :
  - GET `/api/products` : Liste tous les produits
  - GET `/api/products/{id}` : Récupère un produit spécifique
  - POST `/api/products` : Crée un nouveau produit
  - PUT `/api/products/{id}` : Met à jour un produit
  - DELETE `/api/products/{id}` : Supprime un produit

Exemple de requête REST :
```bash
# Lister les catégories
curl http://localhost:8080/api/categories \
  -H "Accept: application/json"

# Créer une catégorie
curl -X POST http://localhost:8080/api/categories \
  -H "Content-Type: application/json" \
  -d '{"name": "Nouvelle Catégorie"}'
```

## Configuration

Les paramètres principaux sont configurés dans `config/services.yaml` :
- `uploads_directory`: Dossier pour les fichiers uploadés
- `sounds_directory`: Dossier pour les fichiers sons
- `root_directory`: Dossier racine public
- `session_max_idle_time`: Durée maximale d'inactivité de session (14400)
- `cookie_lifetime`: Durée de vie des cookies (14400)
- `redis_ttl`: Durée de vie du cache Redis pour les statistiques (3600)

## Fonctionnalités

- Gestion des produits
- Gestion des catégories
- Gestion des stocks
- Gestion des utilisateurs
- Gestion des commandes avec export
- Gestion des statistiques avec mise en cache Redis
- Système de notification
- Support multilingue (i18n)
- Intégration Bugsnag pour le monitoring des erreurs
- Support CORS (Cross-Origin Resource Sharing)
- API REST sécurisée

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
- Support JWT pour l'authentification API
- Configuration CORS sécurisée

## Structure du projet

```
src/
├── Controller/         # Contrôleurs de l'application
│   └── Admin/         # Contrôleurs de l'administration
├── Entity/            # Entités Doctrine avec attributs PHP 8 et API Platform
├── Repository/        # Repositories Doctrine
├── Service/          # Services métier
├── Listener/         # Event Listeners
└── Twig/             # Extensions Twig personnalisées
config/
├── packages/         # Configuration des packages
│   └── api_platform/ # Configuration API Platform
docker/               # Configuration Docker
├── nginx/            # Configuration Nginx
├── php/             # Configuration PHP-FPM
├── elasticsearch/   # Configuration Elasticsearch
└── redis/           # Configuration Redis
```

## Docker

Le projet utilise Docker pour l'environnement de développement. Les services disponibles sont :
- `nginx`: Serveur web
- `php`: Application PHP-FPM
- `postgres`: Base de données PostgreSQL
- `elasticsearch`: Moteur de recherche Elasticsearch
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

# Accéder à Elasticsearch
curl http://localhost:9200
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
