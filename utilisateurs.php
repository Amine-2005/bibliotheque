<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'ajouter' || $action === 'modifier') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');

            if ($nom === '' || $prenom === '' || $email === '') {
                flash('error', 'Le nom, le prénom et l\'email sont obligatoires.');
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'L\'adresse email n\'est pas valide.');
            } elseif ($action === 'ajouter') {
                $stmt = $pdo->prepare('INSERT INTO utilisateur (nom, prenom, email, telephone) VALUES (?, ?, ?, ?)');
                $stmt->execute([$nom, $prenom, $email, $telephone]);
                flash('success', 'Utilisateur ajouté.');
            } else {
                $stmt = $pdo->prepare('UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ? WHERE id = ?');
                $stmt->execute([$nom, $prenom, $email, $telephone, (int) $_POST['id']]);
                flash('success', 'Utilisateur modifié.');
            }
        } elseif ($action === 'supprimer') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM emprunt WHERE utilisateur_id = ?');
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Cet utilisateur a des emprunts enregistrés, il ne peut pas être supprimé.');
            } else {
                $pdo->prepare('DELETE FROM utilisateur WHERE id = ?')->execute([$id]);
                flash('success', 'Utilisateur supprimé.');
            }
        }
    } catch (PDOException $ex) {
        flash('error', 'Opération impossible : cet email existe déjà.');
    }
    header('Location: utilisateurs.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$sql = 'SELECT * FROM utilisateur';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?';
    $params = array_fill(0, 3, '%' . $q . '%');
}
$sql .= ' ORDER BY nom, prenom';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$utilisateurs = $stmt->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>Utilisateurs</h1>

<section class="card">
    <h2><?= $edit ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' ?></h2>
    <form method="post" class="form-grid">
        <input type="hidden" name="action" value="<?= $edit ? 'modifier' : 'ajouter' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <label>Nom
            <input type="text" name="nom" required value="<?= e($edit['nom'] ?? '') ?>">
        </label>
        <label>Prénom
            <input type="text" name="prenom" required value="<?= e($edit['prenom'] ?? '') ?>">
        </label>
        <label>Email
            <input type="email" name="email" required value="<?= e($edit['email'] ?? '') ?>">
        </label>
        <label>Téléphone
            <input type="text" name="telephone" value="<?= e($edit['telephone'] ?? '') ?>">
        </label>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $edit ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($edit): ?><a href="utilisateurs.php" class="btn secondary">Annuler</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <div class="toolbar">
        <h2>Liste des utilisateurs</h2>
        <form method="get" class="search">
            <input type="search" name="q" placeholder="Nom, prénom, email" value="<?= e($q) ?>">
            <button type="submit" class="btn">Rechercher</button>
        </form>
    </div>
    <?php if (!$utilisateurs): ?>
        <p class="muted">Aucun utilisateur trouvé.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($utilisateurs as $u): ?>
                <tr>
                    <td><?= e($u['nom']) ?></td>
                    <td><?= e($u['prenom']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['telephone']) ?></td>
                    <td class="actions">
                        <a href="utilisateurs.php?edit=<?= (int) $u['id'] ?>" class="btn small secondary">Modifier</a>
                        <form method="post" data-confirm="Supprimer cet utilisateur ?">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
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
