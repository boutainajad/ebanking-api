# E-banking API

## Description
API RESTful pour une plateforme e-banking développée avec Laravel 11. Elle permet la gestion des utilisateurs, des comptes bancaires (courant, épargne, mineur), des opérations de virement et des transactions. L'authentification est assurée par JWT. Le projet respecte les règles métier (découvert, limites de retraits, comptes joints, comptes mineurs, frais et intérêts automatiques).

## Technologies utilisées
- Laravel 11
- JWT (tymon/jwt-auth)
- MySQL
- PHPUnit pour les tests
- Postman pour la documentation des endpoints

## Prérequis
- PHP >= 8.2
- Composer
- MySQL / MariaDB
- Postman (pour tester l'API)

## Installation

1. **Cloner le dépôt**
   ```bash
   git clone <url-du-depot>
   cd ebanking-api