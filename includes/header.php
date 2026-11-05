<?php
require_once __DIR__ . '/../config.php';
$page = basename($_SERVER['PHP_SELF']);
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion de bibliothèque</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
    <div class="container nav">
        <a class="logo" href="index.php">Bibliothèque</a>
        <nav>
            <a href="index.php" class="<?= $page === 'index.php' ? 'active' : '' ?>">Accueil</a>
            <a href="livres.php" class="<?= $page === 'livres.php' ? 'active' : '' ?>">Livres</a>
            <a href="utilisateurs.php" class="<?= $page === 'utilisateurs.php' ? 'active' : '' ?>">Utilisateurs</a>
            <a href="emprunts.php" class="<?= $page === 'emprunts.php' ? 'active' : '' ?>">Emprunts</a>
        </nav>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
