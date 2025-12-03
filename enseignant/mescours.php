<?php
session_start();

// Vérification : seul un enseignant peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'];

// === Récupération des cours de l'enseignant avec le nombre d'étudiants inscrits ===
$courses = $pdo->prepare("
    SELECT 
        c.id,
        c.name,
        c.code,
        c.description,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name ASC
");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes cours - Enseignant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Poppins', sans-serif; }
    .navbar { background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.1); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .sidebar {
      width: 260px; background: #ffffff; min-height: 100vh; position: fixed; left: 0; top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07); padding-top: 30px;
    }
    .sidebar h4 { margin-left: 25px; color: #4f46e5; font-weight: 700; }
    .sidebar .nav-link {
      color: #0d1b3e; padding: 14px 25px; font-weight: 500; border-radius: 8px; margin: 5px 15px;
      display: flex; align-items: center; transition: all 0.3s;
    }
    .sidebar .nav-link i { margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }
    .content { margin-left: 260px; padding: 100px 40px 40px; }
    .course-card {
      background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: all 0.3s; overflow: hidden; height: 100%;
    }
    .course-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
    .course-header {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white; padding: 20px; text-align: center;
    }
    .course-body { padding: 25px; }
    .course-code { font-size: 14px; opacity: 0.9; }
    .btn-action { border-radius: 50px; padding: 8px 20px; font-size: 14px; }
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
      <li><a href="Dashboard.php" class="nav-link "><i class="fa fa-tachometer-alt me-2"></i> Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link active"><i class="fa fa-book me-2"></i> Mes cours</a></li>
      <li><a href="mesetudiants.php" class="nav-link"><i class="fa fa-users me-2"></i> Mes étudiants</a></li>
      <li><a href="absence.php" class="nav-link"><i class="fa fa-calendar-times me-2"></i> Prise d'absences</a></li>
      <li><a href="mesnotes.php" class="nav-link"><i class="fa fa-clipboard-check me-2"></i> Mes notes</a></li>
    </ul>
  </aside>
  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark"><i class="fa fa-book-open text-primary"></i> Mes cours</h2>
          <p class="text-muted">Vous enseignez actuellement <strong><?= count($courses) ?></strong> cours</p>
        </div>
      </div>

      <?php if (empty($courses)): ?>
        <div class="text-center py-5">
          <i class="fa fa-book-open fa-5x text-muted mb-4 opacity-25"></i>
          <h4 class="text-muted">Aucun cours assigné</h4>
          <p class="text-muted">Contactez l'administrateur pour vous attribuer des cours.</p>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($courses as $c): ?>
            <div class="col-md-6 col-lg-4">
              <div class="course-card">
                <div class="course-header">
                  <h5 class="mb-1"><?= htmlspecialchars($c['name']) ?></h5>
                  <?php if ($c['code']): ?>
                    <div class="course-code">Code : <?= htmlspecialchars($c['code']) ?></div>
                  <?php endif; ?>
                </div>
                <div class="course-body">
                  <?php if ($c['description']): ?>
                    <p class="text-muted small mb-3"><?= htmlspecialchars(substr($c['description'], 0, 100)) ?>...</p>
                  <?php endif; ?>
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                      <i class="fa fa-users text-primary"></i>
                      <strong><?= $c['nb_students'] ?></strong> étudiant(s)
                    </div>
                    <span class="badge bg-success fs-6"><?= $c['nb_students'] > 0 ? 'Actif' : 'Vide' ?></span>
                  </div>
                  <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="absence.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-action">
                      <i class="fa fa-calendar-times"></i> Absences
                    </a>
                    <a href="teacher_notes.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-action">
                      <i class="fa fa-pen-to-square"></i> Notes
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>