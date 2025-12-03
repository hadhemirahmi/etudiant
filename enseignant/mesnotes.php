<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$teacher_id   = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];
$message      = '';
$selected_course = $_GET['cours'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $student_id = trim($_POST['student_id']);
    $course_id  = $_POST['course_id'];
    $type       = $_POST['type'];
    $grade      = str_replace(',', '.', $_POST['grade']);

    if (
        is_numeric($student_id) && $student_id > 0 &&
        !empty($course_id) &&
        is_numeric($grade) && $grade >= 0 && $grade <= 20
    ) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO notes (student_id, course_id, teacher_id, grade, type, date)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    grade = VALUES(grade),
                    type  = VALUES(type),
                    date  = NOW()
            ");
            $stmt->execute([$student_id, $course_id, $teacher_id, $grade, $type]);
            $message = "<div class='alert alert-success'>Note enregistrée/modifiée avec succès !</div>";
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Données invalides. Note entre 0 et 20, ID étudiant numérique.</div>";
    }
}
if (isset($_GET['delete'])) {
    $note_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND teacher_id = ?");
        $stmt->execute([$note_id, $teacher_id]);
        $message = "<div class='alert alert-success'>Note supprimée définitivement.</div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>Erreur de suppression.</div>";
    }

    $redirect = $selected_course ? "?cours=$selected_course" : "";
    header("Location: mesnotes.php$redirect");
    exit;
}
$courses = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name
");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);


if ($selected_course) {
    $stmt = $pdo->prepare("
        SELECT n.id, n.grade, n.type, n.date,
               u.name AS student_name, u.id AS student_id,
               c.name AS course_name
        FROM notes n
        JOIN users u ON n.student_id = u.id
        JOIN courses c ON n.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ? AND c.id = ?
        ORDER BY n.date DESC, u.name
    ");
    $stmt->execute([$teacher_id, $selected_course]);
} else {
    $stmt = $pdo->prepare("
        SELECT n.id, n.grade, n.type, n.date,
               u.name AS student_name, u.id AS student_id,
               c.name AS course_name
        FROM notes n
        JOIN users u ON n.student_id = u.id
        JOIN courses c ON n.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ?
        ORDER BY n.date DESC, c.name, u.name
    ");
    $stmt->execute([$teacher_id]);
}
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes notes - Enseignant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .navbar { background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.1); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .sidebar { width: 260px; background: #ffffff; min-height: 100vh; position: fixed; left: 0; top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07); padding-top: 30px; }
    .sidebar h4 { margin-left: 25px; color: #4f46e5; font-weight: 700; }
    .sidebar .nav-link { color: #0d1b3e; padding: 14px 25px; font-weight: 500; border-radius: 8px; margin: 5px 15px;
      display: flex; align-items: center; transition: all 0.3s; }
    .sidebar .nav-link i { margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }
    .content { margin-left: 260px; padding: 100px 40px 40px; }
    .card-custom { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px; }
    .badge-grade { font-size: 1.1em; padding: 8px 14px; }
    .table th { background: #4f46e5; color: white; }
  </style>
</head>
<body>

  <!-- Navbar -->
   <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">systeme gestion des etudiants <span class="text-primary">.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link active" href="indexenseignant.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="Dashboard.php">tableaux de bord</a></li>
        </ul>


        <div class="d-flex gap-3">
         <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h4>Espace Enseignant</h4>
    <ul class="nav flex-column">
      <li><a href="Dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link"><i class="fas fa-book"></i> Mes cours</a></li>
      <li><a href="mesetudiants.php" class="nav-link"><i class="fas fa-users"></i> Mes étudiants</a></li>
      <li><a href="absence.php" class="nav-link"><i class="fas fa-calendar-times"></i> Absences</a></li>
      <li><a href="mesnotes.php" class="nav-link active"><i class="fas fa-clipboard-check"></i> Mes notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark">Gestion des notes</h2>
          <p class="text-muted"><?= count($notes) ?> note(s) saisie(s)</p>
        </div>
        <div class="col-md-4">
          <select class="form-select form-select-lg" onchange="window.location.href='?cours='+this.value">
            <option value="">Tous mes cours</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($selected_course == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?= $message ?>

      <!-- Formulaire d'ajout rapide -->
      <div class="card-custom mb-4">
        <h5 class="mb-3 text-primary"><i class="fas fa-plus-circle"></i> Ajouter ou modifier une note</h5>
        <form method="post" class="row g-3 align-items-end">
          <div class="col-md-3">
            <select name="course_id" class="form-select" required>
              <option value="">-- Choisir un cours --</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="student_id" class="form-control" placeholder="ID étudiant (ex: 3)" required>
            <small class="text-muted">Voir l'ID dans "Mes étudiants"</small>
          </div>
          <div class="col-md-2">
            <select name="type" class="form-select" required>
              <option value="DS">DS</option>
              <option value="Examen">Examen</option>
              <option value="TP">TP</option>
              <option value="Projet">Projet</option>
              <option value="Autre">Autre</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="text" name="grade" class="form-control" placeholder="15.75" step="0.01" required>
          </div>
          <div class="col-md-2">
            <button type="submit" name="add_note" class="btn btn-primary w-100">
              <i class="fas fa-save"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>

      <!-- Tableau des notes -->
      <div class="card-custom">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="m-0">Notes saisies</h5>
        </div>

        <?php if (empty($notes)): ?>
          <div class="text-center py-5 text-muted">
            <i class="fas fa-clipboard-list fa-4x mb-3 opacity-25"></i>
            <p>Aucune note saisie pour le moment.</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Cours</th>
                  <th>Étudiant</th>
                  <th>Note</th>
                  <th>Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($notes as $n): ?>
                  <tr>
                    <td><?= date('d/m/Y', strtotime($n['date'])) ?></td>
                    <td><strong><?= htmlspecialchars($n['course_name']) ?></strong></td>
                    <td><?= htmlspecialchars($n['student_name']) ?> <small class="text-muted">(ID: <?= $n['student_id'] ?>)</small></td>
                    <td>
                      <span class="badge <?= $n['grade'] >= 10 ? 'bg-success' : 'bg-danger' ?> badge-grade">
                        <?= number_format($n['grade'], 2) ?>/20
                      </span>
                    </td>
                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($n['type']) ?></span></td>
                    <td>
                      <a href="mesnotes.php?delete=<?= $n['id'] ?>&cours=<?= $selected_course ?>"
                         class="btn btn-sm btn-outline-danger"
                         onclick="return confirm('Supprimer cette note définitivement ?')">
                        <i class="fas fa-trash"></i>
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
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>