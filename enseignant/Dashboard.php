<?php
session_start();

// Vérification : seul un enseignant peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$teacher_id   = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'] ?? 'Enseignant';   // Protection contre null

// === Statistiques rapides ===
$stats = [];

// Nombre de cours enseignés
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT ca.course_id)
    FROM course_assignments ca
    WHERE ca.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$stats['courses'] = (int)$stmt->fetchColumn();

// Nombre total d'étudiants dans ses cours
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT e.student_id)
    FROM enrollments e
    JOIN course_assignments ca ON e.course_id = ca.course_id
    WHERE ca.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$stats['students'] = (int)$stmt->fetchColumn();

// Nombre d'absences saisies ce mois-ci
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM absences
    WHERE teacher_id = ?
      AND MONTH(date) = MONTH(CURRENT_DATE())
      AND YEAR(date)  = YEAR(CURRENT_DATE())
");
$stmt->execute([$teacher_id]);
$stats['absences_this_month'] = (int)$stmt->fetchColumn();

// Nombre de notes saisies
$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM notes n
    JOIN course_assignments ca ON n.course_id = ca.course_id
    WHERE ca.teacher_id = ?
");
$stmt->execute([$teacher_id]);
$stats['notes'] = (int)$stmt->fetchColumn();

// === Mes cours (avec nombre d'étudiants inscrits) ===
$courses = $pdo->prepare("
    SELECT 
        c.id,
        c.name,
        (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS nb_students
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name
");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

// === Dernières absences saisies (5 dernières dates) ===
$recent_absences = $pdo->prepare("
    SELECT 
        a.date,
        c.name AS course,
        COUNT(a.student_id) AS absents
    FROM absences a
    JOIN courses c ON a.course_id = c.id
    WHERE a.teacher_id = ?
    GROUP BY a.date, a.course_id, c.name
    ORDER BY a.date DESC, c.name
    LIMIT 5
");
$recent_absences->execute([$teacher_id]);
$recent_absences = $recent_absences->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord - Enseignant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
 <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }
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
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }

    .content { margin-left: 260px; padding: 100px 40px 40px; }
    .card-custom {
      background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px;
    }
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
      <li><a href="Dashboard.php" class="nav-link active"><i class="fa fa-tachometer-alt me-2"></i> Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link"><i class="fa fa-book me-2"></i> Mes cours</a></li>
      <li><a href="mesetudiants.php" class="nav-link"><i class="fa fa-users me-2"></i> Mes étudiants</a></li>
      <li><a href="absence.php" class="nav-link"><i class="fa fa-calendar-times me-2"></i> Prise d'absences</a></li>
      <li><a href="mesnotes.php" class="nav-link"><i class="fa fa-clipboard-check me-2"></i> Mes notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">

      <!-- Bienvenue -->
      <div class="text-dark mb-5">
        <h1 class="fw-bold display-5">Bonjour, <?= htmlspecialchars($teacher_name, ENT_QUOTES, 'UTF-8') ?> !</h1>
        <p class="lead opacity-90">Voici votre tableau de bord enseignant</p>
      </div>

      <!-- Cartes statistiques -->
      <div class="row g-4 mb-5">
        <div class="col-md-3">
          <div class="stat-card text-primary">
            <i class="fa fa-book-open stat-icon"></i>
            <div class="stat-number"><?= $stats['courses'] ?></div>
            <div class="stat-label">Cours enseignés</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-success">
            <i class="fa fa-users stat-icon"></i>
            <div class="stat-number"><?= $stats['students'] ?></div>
            <div class="stat-label">Étudiants suivis</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-warning">
            <i class="fa fa-calendar-times stat-icon"></i>
            <div class="stat-number"><?= $stats['absences_this_month'] ?></div>
            <div class="stat-label">Absences ce mois</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-card text-info">
            <i class="fa fa-clipboard-check stat-icon"></i>
            <div class="stat-number"><?= $stats['notes'] ?></div>
            <div class="stat-label">Notes saisies</div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <!-- Mes cours -->
        <div class="col-lg-8">
          <div class="card-custom">
            <h4 class="mb-4">Mes cours</h4>
            <?php if (empty($courses)): ?>
              <p class="text-muted">Aucun cours assigné pour le moment.</p>
            <?php else: ?>
              <div class="row g-3">
                <?php foreach ($courses as $c): ?>
                  <div class="col-md-6">
                    <div class="p-3 bg-light rounded border-start border-primary border-4">
                      <h6 class="mb-1"><?= htmlspecialchars($c['name'] ?? 'Cours sans nom', ENT_QUOTES, 'UTF-8') ?></h6>
                      <small class="text-muted"><?= (int)($c['nb_students'] ?? 0) ?> étudiant(s) inscrit(s)</small>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Dernières absences -->
        <div class="col-lg-4">
          <div class="card-custom">
            <h4 class="mb-4">Dernières absences saisies</h4>
            <?php if (empty($recent_absences)): ?>
              <p class="text-muted">Aucune absence enregistrée récemment.</p>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($recent_absences as $a): ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?= htmlspecialchars($a['course'] ?? 'Cours inconnu', ENT_QUOTES, 'UTF-8') ?></strong><br>
                      <small class="text-muted"><?= date('d/m/Y', strtotime($a['date'])) ?></small>
                    </div>
                    <span class="badge bg-danger rounded-pill"><?= (int)($a['absents'] ?? 0) ?> abs.</span>
                  </div>
                <?php endforeach; ?>
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