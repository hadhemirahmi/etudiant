<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../Database.php';
$pdo = connectDatabase(); // ← $pdo est bien défini ici

$message = '';

// === Ajout d'un cours ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name       = trim($_POST['course_name']);
    $code       = strtoupper(trim($_POST['course_code']));
    $credits    = (int)$_POST['credits'];
    $desc       = trim($_POST['description'] ?? '');

    if (!empty($name) && !empty($code) && $credits > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (name, code, credits, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $code, $credits, $desc]);
            $message = '<div class="alert alert-success">Cours ajouté avec succès !</div>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = '<div class="alert alert-danger">Ce code de cours existe déjà.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-warning">Tous les champs obligatoires doivent être remplis.</div>';
    }
}

// === Liste des cours ===
$courses = $pdo->query("SELECT * FROM courses ORDER BY name ASC")->fetchAll(); // ← $pdo bien utilisé
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gérer les cours - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .sidebar { width: 260px; background: #ffffff; min-height: 100vh; position: fixed; left: 0; top: 76px; box-shadow: 2px 0 18px rgba(0,0,0,0.07); padding-top: 30px; z-index: 999; }
    .sidebar h4 { margin-left: 25px; margin-bottom: 25px; font-weight: 700; color: #4f46e5; }
    .sidebar .nav-link { color: #0d1b3e; padding: 14px 25px; font-size: 15px; font-weight: 500; border-radius: 8px; margin: 5px 15px; display: flex; align-items: center; transition: all 0.3s; }
    .sidebar .nav-link i { font-size: 18px; margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #eef3ff; color: #4f46e5; padding-left: 30px; }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }
    .content { margin-left: 260px; padding: 100px 40px 40px; }
    .card-custom { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px; }
    .table th { background: #4f46e5; color: white; }
    .btn-action { border-radius: 50px; padding: 6px 14px; font-size: 14px; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Gestion Étudiants <span class="text-primary">.</span></a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Admin</span>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="Dashboard.php" class="nav-link">Tableau de bord</a></li>
      <li class="nav-item"><a href="etudiants.php" class="nav-link">Gérer étudiants</a></li>
      <li class="nav-item"><a href="enseignants.php" class="nav-link">Gérer enseignants</a></li>
      <li class="nav-item"><a href="cours.php" class="nav-link active">Gérer cours</a></li>
      <li class="nav-item"><a href="notes.php" class="nav-link">Gérer notes</a></li>
      <li class="nav-item"><a href="absence.php" class="nav-link">Absences</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark">Gérer les cours</h2>

      <?php if ($message) echo $message; ?>

      <div class="row g-4">
        <!-- Formulaire d'ajout -->
        <div class="col-lg-5">
          <div class="card-custom">
            <h4 class="mb-4">Ajouter un nouveau cours</h4>
            <form method="post">
              <input type="hidden" name="add_course" value="1">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nom du cours</label>
                <input type="text" name="course_name" class="form-control" placeholder="Ex: Programmation Web" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Code du cours</label>
                <input type="text" name="course_code" class="form-control" placeholder="Ex: WEB301" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Crédits ECTS</label>
                <input type="number" name="credits" class="form-control" min="1" max="30" value="6" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Description (facultatif)</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Description du cours..."></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100">Ajouter le cours</button>
            </form>
          </div>
        </div>

        <!-- Liste des cours -->
        <div class="col-lg-7">
          <div class="card-custom">
            <h4 class="mb-4">Liste des cours (<?= count($courses) ?>)</h4>
            <?php if (empty($courses)): ?>
              <p class="text-center text-muted py-5">Aucun cours enregistré.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Nom</th>
                      <th>Code</th>
                      <th>Crédits</th>
                      <th>Description</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($courses as $c): ?>
                      <tr>
                        <td><strong>#<?= $c['id'] ?></strong></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($c['code'] ?? '') ?></span></td>
                        <td><strong><?= $c['credits'] ?? '-' ?></strong></td>
                        <td class="text-muted small">
                          <?= $c['description'] ? htmlspecialchars(substr($c['description'], 0, 60)).'...' : 'Aucune description' ?>
                        </td>
                        <td>
                          <a href="edit_course.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning btn-action">Modifier</a>
                          <a href="delete_course.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger btn-action"
                             onclick="return confirm('Supprimer ce cours ? Toutes les données liées seront perdues !')">
                             Supprimer
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>