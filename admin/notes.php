<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

// Initialiser $message pour éviter l'erreur
$message = '';

// Ajouter une note
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];
    $grade      = (float) $_POST['grade'];
    $type       = $_POST['type'];
    $teacher_id = $_SESSION['user_id']; // l'admin qui ajoute

    if (!empty($student_id) && !empty($course_id) && $grade >= 0 && $grade <= 20) {
        try {
            $stmt = $pdo->prepare("INSERT INTO notes (student_id, course_id, teacher_id, grade, type, date) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$student_id, $course_id, $teacher_id, $grade, $type]);
            $message = "<div class='alert alert-success'>Note ajoutée avec succès !</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Vérifiez les champs (note entre 0 et 20).</div>";
    }
}

// Récupérer les notes
$notes = $pdo->query("
    SELECT n.id, n.grade, n.type, n.date,
           u.name AS student_name,
           c.name AS course_name
    FROM notes n
    JOIN users u ON n.student_id = u.id
    JOIN courses c ON n.course_id = c.id
    ORDER BY n.date DESC
")->fetchAll();

// Étudiants
$students = $pdo->query("SELECT id, name FROM users WHERE role='etudiant' ORDER BY name")->fetchAll();

// Cours
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gérer les notes - Admin</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }

    .sidebar {
      width: 260px;
      background: #ffffff;
      min-height: 100vh;
      position: fixed;
      left: 0;
      top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07);
      padding-top: 30px;
      z-index: 999;
    }
    .sidebar h4 { margin-left: 25px; margin-bottom: 25px; font-weight: 700; color: #4f46e5; }
    .sidebar .nav-link {
      color: #0d1b3e; padding: 14px 25px; font-size: 15px; font-weight: 500;
      border-radius: 8px; margin: 5px 15px; display: flex; align-items: center;
      transition: all 0.3s;
    }
    .sidebar .nav-link i { font-size: 18px; margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }
    .content {
      margin-left: 260px;
      padding: 100px 40px 40px;
    }
    .card-custom {
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      padding: 30px;
    }
    .table th { background: #4f46e5; color: white; }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">Système gestion des étudiants <span class="text-primary">.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link active" href="indexadmin.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="Dashboard.php">Tableaux de bord</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
           
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>
      </div>
    </div>
  </nav>

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

<main class="content">
  <div class="container-fluid">
    <h2 class="fw-bold text-dark mb-4"><i class="fa fa-pen-to-square text-primary"></i> Gérer les notes</h2>

    <?= $message ?>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card-custom">
          <h4 class="fw-bold mb-3"><i class="fa fa-plus-circle text-primary"></i> Ajouter une note</h4>
          <form method="post">
            <input type="hidden" name="add_note" value="1">
            <div class="mb-3">
              <label class="form-label fw-semibold">Étudiant</label>
              <select name="student_id" class="form-select" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($students as $s): ?>
                  <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Cours</label>
              <select name="course_id" class="form-select" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Type de note</label>
              <select name="type" class="form-select" required>
                <option value="DS">DS</option>
                <option value="Examen">Examen</option>
                <option value="TP">TP</option>
                <option value="Projet">Projet</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Note (0-20)</label>
              <input type="number" name="grade" min="0" max="20" step="0.25" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100"><i class="fa fa-check-circle"></i> Ajouter</button>
          </form>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card-custom">
          <h4 class="fw-bold mb-3"><i class="fa fa-list text-primary"></i> Liste des notes (<?= count($notes) ?>)</h4>
          <?php if (empty($notes)) : ?>
            <p class="text-center text-muted py-4">Aucune note enregistrée.</p>
          <?php else : ?>
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead>
                  <tr>
                    <th>#</th>
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
                      <td><?= $n['id'] ?></td>
                      <td><?= htmlspecialchars($n['student_name']) ?></td>
                      <td><?= htmlspecialchars($n['course_name']) ?></td>
                      <td><span class="badge bg-primary"><?= $n['grade'] ?>/20</span></td>
                      <td><?= htmlspecialchars($n['type']) ?></td>
                      <td><?= date('d/m/Y H:i', strtotime($n['date'])) ?></td>
                      <td>
                        <span>
                        <a class="btn btn-warning btn-sm" href="edit_notes.php?id=<?= $n['id'] ?>"><i class="fa fa-edit"></i></a>
                        <a class="btn btn-danger btn-sm" href="delete_note.php?id=<?= $n['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');"><i class="fa fa-trash"></i></a>
                        </span>
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
</body>
</html>
