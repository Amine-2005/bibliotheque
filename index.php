<?php
require_once __DIR__ . '/config.php';

$nbLivres = (int) $pdo->query('SELECT COALESCE(SUM(quantite), 0) FROM livre')->fetchColumn();
$nbTitres = (int) $pdo->query('SELECT COUNT(*) FROM livre')->fetchColumn();
$nbUtilisateurs = (int) $pdo->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn();
$nbEnCours = (int) $pdo->query('SELECT COUNT(*) FROM emprunt WHERE date_retour IS NULL')->fetchColumn();
$nbRetards = (int) $pdo->query('SELECT COUNT(*) FROM emprunt WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()')->fetchColumn();

$derniers = $pdo->query(
    'SELECT e.*, l.titre, u.nom, u.prenom
     FROM emprunt e
     JOIN livre l ON l.id = e.livre_id
     JOIN utilisateur u ON u.id = e.utilisateur_id
     ORDER BY e.id DESC LIMIT 5'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>Tableau de bord</h1>

<section class="stats">
    <div class="card stat"><span class="number"><?= $nbTitres ?></span><span>Titres</span></div>
    <div class="card stat"><span class="number"><?= $nbLivres ?></span><span>Exemplaires</span></div>
    <div class="card stat"><span class="number"><?= $nbUtilisateurs ?></span><span>Utilisateurs</span></div>
    <div class="card stat"><span class="number"><?= $nbEnCours ?></span><span>Emprunts en cours</span></div>
    <div class="card stat <?= $nbRetards > 0 ? 'alert' : '' ?>"><span class="number"><?= $nbRetards ?></span><span>En retard</span></div>
</section>

<section class="card">
    <h2>Derniers emprunts</h2>
    <?php if (!$derniers): ?>
        <p class="muted">Aucun emprunt pour le moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Livre</th><th>Utilisateur</th><th>Date d'emprunt</th><th>Retour prévu</th><th>Statut</th></tr>
            </thead>
            <tbody>
            <?php foreach ($derniers as $d): ?>
                <?php
                if ($d['date_retour']) {
                    $statut = ['Rendu', 'ok'];
                } elseif ($d['date_retour_prevue'] < date('Y-m-d')) {
                    $statut = ['En retard', 'late'];
                } else {
                    $statut = ['En cours', 'progress'];
                }
                ?>
                <tr>
                    <td><?= e($d['titre']) ?></td>
                    <td><?= e($d['prenom'] . ' ' . $d['nom']) ?></td>
                    <td><?= e($d['date_emprunt']) ?></td>
                    <td><?= e($d['date_retour_prevue']) ?></td>
                    <td><span class="badge <?= $statut[1] ?>"><?= $statut[0] ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
