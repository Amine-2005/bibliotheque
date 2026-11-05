<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'emprunter') {
        $livreId = (int) ($_POST['livre_id'] ?? 0);
        $userId = (int) ($_POST['utilisateur_id'] ?? 0);
        $retourPrevu = $_POST['date_retour_prevue'] ?? '';

        $stmt = $pdo->prepare(
            'SELECT l.quantite - (SELECT COUNT(*) FROM emprunt e WHERE e.livre_id = l.id AND e.date_retour IS NULL)
             FROM livre l WHERE l.id = ?'
        );
        $stmt->execute([$livreId]);
        $dispo = $stmt->fetchColumn();

        if (!$livreId || !$userId || $retourPrevu === '') {
            flash('error', 'Tous les champs sont obligatoires.');
        } elseif ($retourPrevu < date('Y-m-d')) {
            flash('error', 'La date de retour prévue doit être aujourd\'hui ou plus tard.');
        } elseif ($dispo === false || $dispo < 1) {
            flash('error', 'Ce livre n\'est plus disponible.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO emprunt (livre_id, utilisateur_id, date_emprunt, date_retour_prevue) VALUES (?, ?, CURDATE(), ?)'
            );
            $stmt->execute([$livreId, $userId, $retourPrevu]);
            flash('success', 'Emprunt enregistré.');
        }
    } elseif ($action === 'retourner') {
        $stmt = $pdo->prepare('UPDATE emprunt SET date_retour = CURDATE() WHERE id = ? AND date_retour IS NULL');
        $stmt->execute([(int) $_POST['id']]);
        flash('success', 'Retour enregistré.');
    }
    header('Location: emprunts.php');
    exit;
}

$filtre = $_GET['statut'] ?? 'tous';
$sql = 'SELECT e.*, l.titre, u.nom, u.prenom
        FROM emprunt e
        JOIN livre l ON l.id = e.livre_id
        JOIN utilisateur u ON u.id = e.utilisateur_id';
if ($filtre === 'en_cours') {
    $sql .= ' WHERE e.date_retour IS NULL';
} elseif ($filtre === 'retard') {
    $sql .= ' WHERE e.date_retour IS NULL AND e.date_retour_prevue < CURDATE()';
} elseif ($filtre === 'rendus') {
    $sql .= ' WHERE e.date_retour IS NOT NULL';
}
$sql .= ' ORDER BY e.id DESC';
$emprunts = $pdo->query($sql)->fetchAll();

$livresDispo = $pdo->query(
    'SELECT l.id, l.titre, l.auteur,
            l.quantite - (SELECT COUNT(*) FROM emprunt e WHERE e.livre_id = l.id AND e.date_retour IS NULL) AS dispo
     FROM livre l HAVING dispo > 0 ORDER BY l.titre'
)->fetchAll();
$utilisateurs = $pdo->query('SELECT id, nom, prenom FROM utilisateur ORDER BY nom, prenom')->fetchAll();
$defaut = date('Y-m-d', strtotime('+14 days'));

require __DIR__ . '/includes/header.php';
?>
<h1>Emprunts</h1>

<section class="card">
    <h2>Nouvel emprunt</h2>
    <?php if (!$livresDispo || !$utilisateurs): ?>
        <p class="muted">Il faut au moins un livre disponible et un utilisateur pour créer un emprunt.</p>
    <?php else: ?>
        <form method="post" class="form-grid">
            <input type="hidden" name="action" value="emprunter">
            <label>Livre
                <select name="livre_id" required>
                    <option value="">Choisir un livre</option>
                    <?php foreach ($livresDispo as $l): ?>
                        <option value="<?= (int) $l['id'] ?>"><?= e($l['titre'] . ' - ' . $l['auteur'] . ' (' . $l['dispo'] . ' dispo)') ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Utilisateur
                <select name="utilisateur_id" required>
                    <option value="">Choisir un utilisateur</option>
                    <?php foreach ($utilisateurs as $u): ?>
                        <option value="<?= (int) $u['id'] ?>"><?= e($u['prenom'] . ' ' . $u['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Retour prévu
                <input type="date" name="date_retour_prevue" required min="<?= date('Y-m-d') ?>" value="<?= $defaut ?>">
            </label>
            <div class="form-actions">
                <button type="submit" class="btn">Enregistrer l'emprunt</button>
            </div>
        </form>
    <?php endif; ?>
</section>

<section class="card">
    <div class="toolbar">
        <h2>Historique</h2>
        <div class="filters">
            <a href="emprunts.php" class="<?= $filtre === 'tous' ? 'active' : '' ?>">Tous</a>
            <a href="emprunts.php?statut=en_cours" class="<?= $filtre === 'en_cours' ? 'active' : '' ?>">En cours</a>
            <a href="emprunts.php?statut=retard" class="<?= $filtre === 'retard' ? 'active' : '' ?>">En retard</a>
            <a href="emprunts.php?statut=rendus" class="<?= $filtre === 'rendus' ? 'active' : '' ?>">Rendus</a>
        </div>
    </div>
    <?php if (!$emprunts): ?>
        <p class="muted">Aucun emprunt.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Livre</th><th>Utilisateur</th><th>Emprunté le</th><th>Retour prévu</th><th>Rendu le</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php foreach ($emprunts as $m): ?>
                <?php
                if ($m['date_retour']) {
                    $statut = ['Rendu', 'ok'];
                } elseif ($m['date_retour_prevue'] < date('Y-m-d')) {
                    $statut = ['En retard', 'late'];
                } else {
                    $statut = ['En cours', 'progress'];
                }
                ?>
                <tr>
                    <td><?= e($m['titre']) ?></td>
                    <td><?= e($m['prenom'] . ' ' . $m['nom']) ?></td>
                    <td><?= e($m['date_emprunt']) ?></td>
                    <td><?= e($m['date_retour_prevue']) ?></td>
                    <td><?= e($m['date_retour'] ?? '-') ?></td>
                    <td><span class="badge <?= $statut[1] ?>"><?= $statut[0] ?></span></td>
                    <td class="actions">
                        <?php if (!$m['date_retour']): ?>
                            <form method="post" data-confirm="Confirmer le retour de ce livre ?">
                                <input type="hidden" name="action" value="retourner">
                                <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn small">Retourner</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
