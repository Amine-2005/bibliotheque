# Gestion de bibliothèque

Site web permettant de gérer les livres, les utilisateurs et les emprunts d'une bibliothèque.

## Fonctionnalités

- Tableau de bord avec statistiques (titres, exemplaires, utilisateurs, emprunts en cours, retards)
- Livres : ajouter, modifier, supprimer, rechercher (titre, auteur, ISBN, catégorie)
- Utilisateurs : ajouter, modifier, supprimer, rechercher
- Emprunts : enregistrer un emprunt, enregistrer un retour, filtrer par statut (en cours, en retard, rendus)
- Gestion du stock : un livre n'est plus empruntable quand tous ses exemplaires sont sortis
- Requêtes préparées (PDO) et protection contre les failles XSS

## Technologies

- HTML, CSS, JavaScript
- PHP (PDO)
- MySQL

## Structure du projet

```
bibliotheque/
├── css/style.css
├── js/script.js
├── includes/
│   ├── header.php
│   └── footer.php
├── config.php
├── schema.sql
├── index.php
├── livres.php
├── utilisateurs.php
└── emprunts.php
```

## Installation

1. Installer XAMPP et démarrer **Apache** et **MySQL**.
2. Copier le dossier `bibliotheque` dans `C:\xampp\htdocs\`.
3. Ouvrir phpMyAdmin (http://localhost/phpmyadmin), onglet **SQL**, puis exécuter le contenu de `schema.sql`.
4. Si besoin, modifier l'utilisateur et le mot de passe MySQL dans `config.php` (par défaut avec XAMPP : `root` sans mot de passe).
5. Ouvrir http://localhost/bibliotheque/ dans le navigateur.
