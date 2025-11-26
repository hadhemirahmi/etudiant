<?php
session_start();

// Vérification : seul un étudiant peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.php");
    exit;
}

include 'Database.php';
$pdo = connectDatabase();

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];

// === Récupération des cours de l'étudiant avec infos complètes ===
$courses = $pdo->prepare("
    SELECT 
        c.id,
        c.name AS course_name,
        c.code,
        c.description,
        u.name AS teacher_name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS total_students
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    JOIN course_assignments ca ON c.id = ca.course_id
    JOIN users u ON ca.teacher_id = u.id
    WHERE e.student_id = ?
    GROUP BY c.id
    ORDER BY c.name ASC
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes cours - Étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
    }
    .navbar {
      background: rgba(255,255,255,0.95) !important;
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 32px rgba(0,0,0,0.1);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
    }
    .navbar-brand {
      font-weight: 800;
      color: #4f46e5 !important;
    }
    .sidebar {
      width: 260px;
      background: #ffffff;
      min-height: 100vh;
      position: fixed;
      left: 0;
      top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07);
      padding-top: 30px;
    }
    .sidebar h4 {
      margin-left: 25px;
      color: #4f46e5;
      font-weight: 700;
    }
    .sidebar .nav-link {
      color: #0d1b3e;
      padding: 14px 25px;
      font-weight: 500;
      border-radius: 8px;
      margin: 5px 15px;
      display: flex;
      align-items: center;
      transition: all 0.3s;
    }
    .sidebar .nav-link i { margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff;
      color: #4f46e5;
      padding-left: 30px;
    }
    .sidebar .nav-link.active {
      background: #4f46e5;
      color: white !important;
    }
    .content {
      margin-left: 260px;
      padding: 100px 40px 40px;
    }
    .course-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 15px 35px rgba(0,0,0,0.15);
      transition: all 0.4s;
      height: 100%;
    }
    .course-card:hover {
      transform: translateY(-12px);
      box-shadow: 0 25px 50px rgba(0,0,0,0.2);
    }
    .course-header {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      color: white;
      padding: 25px;
      text-align: center;
    }
    .course-header h5 {
      margin: 0;
      font-weight: 700;
      font-size: 1.4rem;
    }
    .course-code {
      font-size: 0.9rem;
      opacity: 0.9;
      margin-top: 5px;
    }
    .course-body {
      padding: 30px;
    }
    .teacher-info {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 20px;
    }
    .btn-action {
      border-radius: 50px;
      padding: 10px 20px;
      font-weight: 600;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="student_dashboard.php">Gestion Étudiants</a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Bonjour, <strong class="text-primary"><?= htmlspecialchars($student_name) ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
      </div>
    </div>
  </nav>

  <!-- Sidebar Étudiant -->
  <aside class="sidebar">
    <h4>Mon espace</h4>
    <ul class="nav flex-column">
      <li><a href="student_dashboard.php" class="nav-link">Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link active">Mes cours</a></li>
      <li><a href="mesnotes_etudiant.php" class="nav-link">Mes notes</a></li>
      <li><a href="mesabsences.php" class="nav-link">Mes absences</a></li>
      <li><a href="monprofil.php" class="nav-link">Mon profil</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">

      <div class="text-white mb-5">
        <h1 class="display-5 fw-bold">Mes cours</h1>
        <p class="lead opacity-90">Vous êtes inscrit à <strong><?= count($courses) ?></strong> cours cette année</p>
      </div>

      <?php if (empty($courses)): ?>
        <div class="text-center py-5">
          <i class="fa fa-book-open fa-5x text-white mb-4 opacity-30"></i>
          <h3 class="text-white">Aucun cours inscrit</h3>
          <p class="text-white opacity-80">Contactez l'administration pour vous inscrire à des cours.</p>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($courses as $c): ?>
            <div class="col-md-6 col-lg-4">
              <div class="course-card">
                <div class="course-header">
                  <h5><?= htmlspecialchars($c['course_name']) ?></h5>
                  <?php if ($c['code']): ?>
                    <div class="course-code">Code : <?= htmlspecialchars($c['code']) ?></div>
                  <?php endif; ?>
                </div>
                <div class="course-body">
                  <?php if ($c['description']): ?>
                    <p class="text-muted small mb-3"><?= htmlspecialchars(substr($c['description'], 0, 120)) ?>...</p>
                  <?php endif; ?>

                  <div class="teacher-info">
                    <i class="fa fa-chalkboard-teacher text-primary mb-2"></i>
                    <div><strong><?= htmlspecialchars($c['teacher_name']) ?></strong></div>
                    <small class="text-muted">Enseignant</small>
                  </div>

                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <small class="text-muted">
                      <i class="fa fa-users"></i> <?= $c['total_students'] ?> étudiant<?= $c['total_students'] > 1 ? 's' : '' ?>
                    </small>
                    <span class="badge bg-success fs-6">Inscrit</span>
                  </div>

                  <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="mesnotes_etudiant.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-action">
                      Mes notes
                    </a>
                    <a href="mesabsences.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-action">
                      Mes absences
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