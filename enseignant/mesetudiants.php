<?php
session_start();

// Sécurité : accès réservé aux enseignants
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$teacher_id   = $_SESSION['user_id'];
$teacher_name = $_SESSION['name'] ?? 'Enseignant';

// Filtre par cours
$selected_course = $_GET['cours'] ?? '';

// === Liste des cours assignés à l'enseignant ===
$courses_stmt = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name
");
$courses_stmt->execute([$teacher_id]);
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);

// === Récupération des étudiants inscrits dans les cours de l'enseignant ===
if ($selected_course) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id,
            u.name,
            u.email,
            c.name AS course_name,
            c.id AS course_id
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        JOIN courses c ON e.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ? AND c.id = ?
          AND u.role = 'etudiant'
        ORDER BY c.name, u.name
    ");
    $stmt->execute([$teacher_id, $selected_course]);
} else {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            u.id,
            u.name,
            u.email,
            c.name AS course_name,
            c.id AS course_id
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        JOIN courses c ON e.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ?
          AND u.role = 'etudiant'
        ORDER BY c.name, u.name
    ");
    $stmt->execute([$teacher_id]);
}

$raw_students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regrouper par cours
$students = [];
foreach ($raw_students as $s) {
    $students[$s['course_name']][] = $s;
}

$total_students = count($raw_students);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes étudiants - Enseignant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
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
    .course-section { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 30px; }
    .course-header {
      background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 18px 25px;
    }
    .student-card { transition: all 0.3s; }
    .student-card:hover { background: #f0f4ff !important; transform: translateY(-3px); }
    .student-avatar {
      width: 44px; height: 44px; border-radius: 50%; background: #e9ecef;
      display: flex; align-items: center; justify-content: center;
      font-weight: bold; color: #4f46e5; font-size: 16px;
    }
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
      <li><a href="Dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link"><i class="fa fa-book"></i> Mes cours</a></li>
      <li><a href="mesetudiants.php" class="nav-link active"><i class="fa fa-users"></i> Mes étudiants</a></li>
      <li><a href="absence.php" class="nav-link"><i class="fa fa-calendar-times"></i> Absences</a></li>
      <li><a href="mesnotes.php" class="nav-link"><i class="fa fa-clipboard-check"></i> Mes notes</a></li>
    </ul>
  </aside>

  <!-- Contenu -->
  <main class="content">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark">Mes étudiants</h2>
          <p class="text-muted">Vous suivez <strong><?= $total_students ?></strong> étudiant<?= $total_students > 1 ? 's' : '' ?> dans vos cours</p>
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

      <?php if (empty($students)): ?>
        <div class="text-center py-5">
          <i class="fa fa-users fa-5x text-muted mb-4 opacity-25"></i>
          <h4 class="text-muted">Aucun étudiant inscrit</h4>
          <p class="text-muted">Aucun étudiant n'est inscrit dans vos cours pour le moment.</p>
        </div>
      <?php else: ?>
        <?php foreach ($students as $course_name => $list): ?>
          <?php $course_id = $list[0]['course_id']; ?>
          <div class="course-section">
            <div class="course-header">
              <h5>
                <i class="fa fa-book me-2"></i>
                <?= htmlspecialchars($course_name) ?>
                <span class="badge bg-light text-dark ms-2"><?= count($list) ?> étudiant<?= count($list)>1?'s':'' ?></span>
              </h5>
            </div>
            <div class="p-4">
              <div class="row g-3">
                <?php foreach ($list as $s): ?>
                  <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-center p-3 bg-light rounded border student-card">
                      <div class="student-avatar me-3">
                        <?= strtoupper(substr($s['name'], 0, 2)) ?>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($s['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                      </div>
                      <div class="d-flex gap-2">
                        <!-- Saisir une note -->
                        <a href="mesnotes.php?course_id=<?= $course_id ?>&student_id=<?= $s['id'] ?>"
                           class="btn btn-sm btn-outline-primary" title="Saisir une note">
                          <i class="fa fa-pen"></i>
                        </a>
                        <!-- Marquer absent aujourd'hui -->
                        <a href="absence.php?course_id=<?= $course_id ?>&date=<?= date('Y-m-d') ?>&mark_absent=<?= $s['id'] ?>"
                           class="btn btn-sm btn-outline-danger" title="Absent aujourd'hui">
                          <i class="fa fa-times"></i>
                        </a>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>