<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];
    $grade      = (float) $_POST['grade'];
    $type       = $_POST['type'];

    if (!empty($student_id) && !empty($course_id) && $grade >= 0 && $grade <= 20) {
        try {
            $stmt = $pdo->prepare("INSERT INTO notes (student_id, course_id, grade, type, date) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$student_id, $course_id, $grade, $type]);
            $message = "<div class='alert alert-success'>Note ajoutée avec succès !</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Vérifiez les champs (note entre 0 et 20).</div>";
    }
}

$notes = $pdo->query("
    SELECT n.id, n.grade, n.type, n.date,
           u.name AS student_name,
           c.name AS course_name
    FROM notes n
    JOIN users u ON n.student_id = u.id
    JOIN courses c ON n.course_id = c.id
    ORDER BY n.date DESC
")->fetchAll();
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'etudiant' ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gérer les notes - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }

    /* Sidebar */
    .sidebar {
      width: 260px; background: #ffffff; min-height: 100vh; position: fixed; left: 0; top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07); padding-top: 30px; z-index: 999;
    }
    .sidebar h4 { margin-left: 25px; margin-bottom: 25px; font-weight: 700; color: #4f46e5; }
    .sidebar .nav-link {
      color: #0d1b3e; padding: 14px 25px; font-size: 15px; font-weight: 500;
      border-radius: 8px; margin: 5px 15px; display: flex; align-items: center; transition: all 0.3s;
    }
    .sidebar .nav-link i { font-size: 18px; margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5e5;}

    .content { margin-left: 30px;}
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Gestion Étudiants <span class="text-primary">.</span></a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Admin</span>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">
          <i class="fa fa-sign-out-alt"></i> Déconnexion
        </a>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="Dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li class="nav-item"><a href="etudiants.php" class="nav-link"><i class="fa fa-users"></i> Gérer étudiants</a></li>
      <li class="nav-item"><a href="enseignants.php" class="nav-link"><i class="fa fa-chalkboard-teacher"></i> Gérer enseignants</a></li>
      <li class="nav-item"><a href="cours.php" class="nav-link"><i class="fa fa-book"></i> Gérer cours</a></li>
      <li class="nav-item"><a href="notes.php" class="nav-link active"><i class="fa fa-pen-to-square"></i> Gérer notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark"><i class="fa fa-pen-to-square text-primary"></i> Gérer les notes</h2>

      <?php if ($message) echo $message; ?>

      <div class="row g-4">
        <!-- Formulaire d'ajout -->
        <div class="col-lg-5">
          <div class="card-custom mb-4">
            <h4 class="mb-4"><i class="fa fa-plus"></i> Ajouter une note</h4>
            <form method="post">
              <input type="hidden" name="add_note" value="1">
              <div class="mb-3">
                <label class="form-label fw-semibold">Étudiant</label>
                <select name="student_id" class="form-select" required>
                  <option value="">-- Sélectionner un étudiant --</option>
                  <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Cours</label>
                <select name="course_id" class="form-select" required>
                  <option value="">-- Sélectionner un cours --</option>
                  <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Type de note</label>
                <select name="type" class="form-select" required>
                  <option value="DS">Devoir Surveillé (DS)</option>
                  <option value="Examen">Examen</option>
                  <option value="TP">TP</option>
                  <option value="Projet">Projet</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Note (0-20)</label>
                <input type="number" step="0.25" min="0" max="20" name="grade" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fa fa-check"></i> Ajouter la note
              </button>
            </form>
          </div>
        </div>

        <!-- Liste des notes -->
        <div class="col-lg-7">
          <div class="card-custom">
            <h4 class="mb-4"><i class="fa fa-list"></i> Liste des notes (<?= count($notes) ?>)</h4>
            <?php if (empty($notes)): ?>
              <p class="text-center text-muted py-5">Aucune note enregistrée pour le moment.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Étudiant</th>
                      <th>Cours</th>
                      <th>Note</th>
                      <th>Type</th>
                      <th>Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($notes as $n): ?>
                      <tr>
                        <td><strong>#<?= $n['id'] ?></strong></td>
                        <td><?= htmlspecialchars($n['student_name']) ?></td>
                        <td><?= htmlspecialchars($n['course_name']) ?></td>
                        <td><span class="badge bg-primary"><?= $n['grade'] ?>/20</span></td>
                        <td><?= htmlspecialchars($n['type']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($n['date'])) ?></td>
                        <td>
                          <button class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></button>
                          <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
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