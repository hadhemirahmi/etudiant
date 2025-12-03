<?php
session_start();

// Vérification : seul un étudiant peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: ../login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$student_id = $_SESSION['user_id'];


// === Récupération des cours de l'étudiant ===
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
    GROUP BY c.id, c.name, c.code, c.description, u.name
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
      background: #f7faff;
      font-family: 'Poppins', sans-serif;
    }

    .nav-link:hover {
      color: #5a4ff3 !important;
    }

    .hero {
      padding: 80px 0;
    }
    .hero-title {
      font-size: 48px;
      font-weight: 700;
      color: #0d1b3e;
      line-height: 1.2;
    }

    .search-box {
      background: white;
      border-radius: 50px;
      padding: 12px 25px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 15px;
    }

    .feature-item i {
      color: #4f46e5;
      font-size: 22px;
    }
  </style>
</head>
<body>

<!-- NAVBAR FIXÉE -->
 <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">

      <a class="navbar-brand fs-3 fw-bold" href="#">
        Espace Étudiant <span class="text-primary">.</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">

        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link " href="indexetudiant.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active" href="mescours.php">Mes Cours</a></li>
          <li class="nav-item"><a class="nav-link" href="mesabsences.php">Mes Absences</a></li>
          <li class="nav-item"><a class="nav-link" href="mesnotes.php">Mes Notes</a></li>
        </ul>

        <!-- Espace déconnexion -->
       <div class="d-flex align-items-center gap-3">
           
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>

      </div>
    </div>
  </nav>
<br>

<!-- Contenu -->
<div class="container mt-5">

  <div class="text-center mb-5">
    <h1 class="hero-title">Mes cours</h1>
    <p class="text-secondary">Vous êtes inscrit à <strong><?= count($courses) ?></strong> cours cette année.</p>
  </div>

  <?php if (empty($courses)): ?>
    <div class="text-center py-5">
      <i class="fa fa-book-open fa-5x text-muted mb-4"></i>
      <h3>Aucun cours inscrit</h3>
      <p>Contactez l'administration pour vous inscrire à des cours.</p>
    </div>

  <?php else: ?>
    <div class="row g-4">

      <?php foreach ($courses as $c): ?>
      <div class="col-md-6 col-lg-4">
        <div class="course-card">

          <div class="course-header">
            <h5><?= htmlspecialchars($c['course_name'] ?? '') ?></h5>

            <?php if (!empty($c['code'])): ?>
              <div class="course-code">Code : <?= htmlspecialchars($c['code'] ?? '') ?></div>
            <?php endif; ?>
          </div>

          <div class="course-body">

            <?php if (!empty($c['description'])): ?>
            <p class="text-muted small">
              <?= htmlspecialchars(substr($c['description'] ?? '', 0, 120)) ?>...
            </p>
            <?php endif; ?>

            <div class="teacher-info">
              <i class="fa fa-chalkboard-teacher text-primary mb-2"></i>
              <div><strong><?= htmlspecialchars($c['teacher_name'] ?? '') ?></strong></div>
              <small class="text-muted">Enseignant</small>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <small class="text-muted">
                <i class="fa fa-users"></i>
                <?= $c['total_students'] ?> étudiant<?= $c['total_students'] > 1 ? 's' : '' ?>
              </small>
              <span class="badge bg-success">Inscrit</span>
            </div>

            <div class="d-flex justify-content-center gap-2">
              <a href="mesnotes_etudiant.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-action">Mes notes</a>
              <a href="mesabsences.php?course_id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-action">Mes absences</a>
            </div>

          </div>

        </div>
      </div>
    <?php endforeach; ?>

    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
