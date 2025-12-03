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

// Filtre par cours (optionnel)
$selected_course = $_GET['cours'] ?? '';

// === Liste des cours de l'enseignant (pour le filtre) ===
$courses = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name
");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll();

// === Récupération des étudiants (par cours ou tous) ===
if ($selected_course) {
    $query = "
        SELECT u.id, u.name, u.email, c.name AS course_name, c.id AS course_id
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        JOIN courses c ON e.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ? AND c.id = ?
        ORDER BY c.name, u.name
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$teacher_id, $selected_course]);
} else {
    $query = "
        SELECT u.id, u.name, u.email, c.name AS course_name, c.id AS course_id
        FROM users u
        JOIN enrollments e ON u.id = e.student_id
        JOIN courses c ON e.course_id = c.id
        JOIN course_assignments ca ON c.id = ca.course_id
        WHERE ca.teacher_id = ?
        ORDER BY c.name, u.name
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$teacher_id]);
}

$students = $stmt->fetchAll(PDO::FETCH_GROUP); // groupe par cours
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
    .course-section { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); overflow: hidden; margin-bottom: 30px; }
    .course-header {
      background: linear-gradient(135deg, #4f46e5, #7c3aed); color: white; padding: 18px 25px;
    }
    .course-header h5 { margin: 0; font-weight: 600; }
    .student-avatar {
      width: 44px; height: 44px; border-radius: 50%; background: #e9ecef; display: flex;
      align-items: center; justify-content: center; font-weight: bold; color: #4f46e5; font-size: 16px;
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="Dashboard.php">Gestion Étudiants</a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Bonjour, <strong><?= htmlspecialchars($teacher_name) ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
      </div>
    </div>
  </nav>

 <aside class="sidebar">
    <h4>Espace Enseignant</h4>
    <ul class="nav flex-column">
      <li><a href="Dashboard.php" class="nav-link">Tableau de bord</a></li>
      <li><a href="mescours.php" class="nav-link">Mes cours</a></li>
      <li><a href="mesetudiants.php" class="nav-link">Mes étudiants</a></li>
      <li><a href="absence.php" class="nav-link">Prise d'absences</a></li>
      <li><a href="mesnotes.php" class="nav-link active">Mes notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark"><i class="fa fa-users text-primary"></i> Mes étudiants</h2>
          <p class="text-muted">Vous suivez actuellement <strong><?= array_sum(array_map('count', $students)) ?></strong> étudiant(s)</p>
        </div>

        <!-- Filtre par cours -->
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
          <h4 class="text-muted">Aucun étudiant trouvé</h4>
          <p class="text-muted">Soit aucun étudiant n'est inscrit, soit aucun cours ne vous est assigné.</p>
        </div>
      <?php else: ?>
        <?php foreach ($students as $course_name => $list): 
          $course_id = $list[0]['course_id'] ?? '';
        ?>
          <div class="course-section">
            <div class="course-header">
              <h5><i class="fa fa-book me-2"></i> <?= htmlspecialchars($course_name) ?> (<?= count($list) ?> étudiant<?= count($list)>1?'s':'' ?>)</h5>
            </div>
            <div class="p-4">
              <div class="row g-3">
                <?php foreach ($list as $s): ?>
                  <div class="col-md-6 col-lg-4">
                    <div class="d-flex align-items-center p-3 bg-light rounded border">
                      <div class="student-avatar me-3">
                        <?= strtoupper(substr($s['name'], 0, 2)) ?>
                      </div>
                      <div class="flex-grow-1">
                        <h6 class="mb-0"><?= htmlspecialchars($s['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($s['email']) ?></small>
                      </div>
                      <div class="d-flex gap-2">
                        <a href="teacher_notes.php?course_id=<?= $course_id ?>&student_id=<?= $s['id'] ?>" 
                           class="btn btn-sm btn-outline-primary" title="Saisir note">
                          <i class="fa fa-pen"></i>
                        </a>
                        <a href="absence.php?course_id=<?= $course_id ?>&date=<?= date('Y-m-d') ?>&mark_absent=<?= $s['id'] ?>" 
                           class="btn btn-sm btn-outline-danger" title="Marquer absent aujourd'hui">
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