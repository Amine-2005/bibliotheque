<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'ajouter' || $action === 'modifier') {
            $titre = trim($_POST['titre'] ?? '');
            $auteur = trim($_POST['auteur'] ?? '');
            $isbn = trim($_POST['isbn'] ?? '');
            $categorie = trim($_POST['categorie'] ?? '');
            $quantite = (int) ($_POST['quantite'] ?? 1);

            if ($titre === '' || $auteur === '') {
                flash('error', 'Le titre et l\'auteur sont obligatoires.');
            } elseif ($quantite < 1) {
                flash('error', 'La quantité doit être au moins 1.');
            } elseif ($action === 'ajouter') {
                $stmt = $pdo->prepare('INSERT INTO livre (titre, auteur, isbn, categorie, quantite) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$titre, $auteur, $isbn !== '' ? $isbn : null, $categorie, $quantite]);
                flash('success', 'Livre ajouté.');
            } else {
                $stmt = $pdo->prepare('UPDATE livre SET titre = ?, auteur = ?, isbn = ?, categorie = ?, quantite = ? WHERE id = ?');
                $stmt->execute([$titre, $auteur, $isbn !== '' ? $isbn : null, $categorie, $quantite, (int) $_POST['id']]);
                flash('success', 'Livre modifié.');
            }
        } elseif ($action === 'supprimer') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM emprunt WHERE livre_id = ?');
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Ce livre a des emprunts enregistrés, il ne peut pas être supprimé.');
            } else {
                $pdo->prepare('DELETE FROM livre WHERE id = ?')->execute([$id]);
                flash('success', 'Livre supprimé.');
            }
        }
    } catch (PDOException $ex) {
        flash('error', 'Opération impossible : cet ISBN existe déjà.');
    }
    header('Location: livres.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM livre WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$sql = 'SELECT l.*, l.quantite - (SELECT COUNT(*) FROM emprunt e WHERE e.livre_id = l.id AND e.date_retour IS NULL) AS dispo
        FROM livre l';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE l.titre LIKE ? OR l.auteur LIKE ? OR l.isbn LIKE ? OR l.categorie LIKE ?';
    $params = array_fill(0, 4, '%' . $q . '%');
}
$sql .= ' ORDER BY l.titre';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>Livres</h1>

<section class="card">
    <h2><?= $edit ? 'Modifier le livre' : 'Ajouter un livre' ?></h2>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $edit ? 'modifier' : 'ajouter' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <label>Titre
            <input type="text" name="titre" required value="<?= e($edit['titre'] ?? '') ?>">
        </label>
        <label>Auteur
            <input type="text" name="auteur" required value="<?= e($edit['auteur'] ?? '') ?>">
        </label>
        <label>ISBN
            <input type="text" name="isbn" value="<?= e($edit['isbn'] ?? '') ?>">
        </label>
        <label>Catégorie
            <input type="text" name="categorie" value="<?= e($edit['categorie'] ?? '') ?>">
        </label>
        <label>Quantité
            <input type="number" name="quantite" min="1" value="<?= e($edit['quantite'] ?? 1) ?>">
        </label>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($edit): ?><a href="livres.php" class="btn secondary">Annuler</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <div class="toolbar">
        <h2>Liste des livres</h2>
        <form method="get" class="search">
            <input type="search" name="q" placeholder="Titre, auteur, ISBN, catégorie" value="<?= e($q) ?>">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>
    <?php if (!$livres): ?>
        <p class="muted">Aucun livre trouvé.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Titre</th><th>Auteur</th><th>ISBN</th><th>Catégorie</th><th>Disponibles</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($livres as $l): ?>
                <tr>
                    <td><?= e($l['titre']) ?></td>
                    <td><?= e($l['auteur']) ?></td>
                    <td><?= e($l['isbn']) ?></td>
                    <td><?= e($l['categorie']) ?></td>
                    <td><?= (int) $l['dispo'] ?> / <?= (int) $l['quantite'] ?></td>
                    <td class="actions">
                        <a href="livres.php?edit=<?= (int) $l['id'] ?>" class="btn small secondary">Modifier</a>
                        <form method="post" data-confirm="Supprimer ce livre ?">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                            <button type="submit" class="btn small danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
