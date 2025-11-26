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

// Filtre par cours
$selected_course = $_GET['course_id'] ?? '';

// === Liste des cours de l'étudiant (pour le filtre) ===
$courses = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY c.name
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll();

// === Récupération des notes (filtrées ou toutes) ===
if ($selected_course) {
    $notes = $pdo->prepare("
        SELECT n.grade, n.type, n.date, c.name AS course_name
        FROM notes n
        JOIN courses c ON n.course_id = c.id
        WHERE n.student_id = ? AND c.id = ?
        ORDER BY n.date DESC
    ");
    $notes->execute([$student_id, $selected_course]);
} else {
    $notes = $pdo->prepare("
        SELECT n.grade, n.type, n.date, c.name AS course_name
        FROM notes n
        JOIN courses c ON n.course_id = c.id
        WHERE n.student_id = ?
        ORDER BY c.name, n.date DESC
    ");
    $notes->execute([$student_id]);
}
$notes = $notes->fetchAll();

// === Calcul des moyennes ===
$moyennes = [];
$total_notes = 0;
$somme_ponderée = 0;

foreach ($notes as $n) {
    $cours = $n['course_name'];
    if (!isset($moyennes[$cours])) {
        $moyennes[$cours] = ['sum' => 0, 'count' => 0];
    }
    $moyennes[$cours]['sum'] += $n['grade'];
    $moyennes[$cours]['count']++;
    $total_notes++;
    $somme_ponderée += $n['grade'];
}

$moyenne_generale = $total_notes > 0 ? round($somme_ponderée / $total_notes, 2) : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes notes - Étudiant</title>
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
    .navbar-brand { font-weight: 800; color: #4f46e5 !important; }
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
    .card-custom {
      background: white; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.15);
      padding: 30px; transition: 0.4s;
    }
    .card-custom:hover { transform: translateY(-10px); }
    .grade-badge {
      font-size: 2.2rem; font-weight: 800; padding: 15px 30px; border-radius: 20px;
    }
    .note-item {
      background: #f8f9fa; border-left: 5px solid #4f46e5; padding: 15px; border-radius: 10px;
      margin-bottom: 12px; transition: 0.3s;
    }
    .note-item:hover { background: #eef3ff; transform: translateX(8px); }
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
      <li><a href="mescours.php" class="nav-link">Mes cours</a></li>
      <li><a href="mesnotes.php" class="nav-link active">Mes notes</a></li>
      <li><a href="mesabsences.php" class="nav-link">Mes absences</a></li>
      <li><a href="monprofil.php" class="nav-link">Mon profil</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">

      <div class="text-white mb-5">
        <h1 class="display-5 fw-bold">Mes notes</h1>
        <p class="lead opacity-90">Suivez vos performances académiques</p>
      </div>

      <!-- Moyenne générale -->
      <div class="text-center mb-5">
        <div class="card-custom d-inline-block">
          <h2 class="mb-2">Moyenne générale</h2>
          <div class="grade-badge <?= $moyenne_generale >= 10 ? 'bg-success' : 'bg-danger' ?> text-white">
            <?= number_format($moyenne_generale, 2) ?> / 20
          </div>
          <p class="mt-3 text-muted">
            <?= $moyenne_generale >= 12 ? 'Excellent travail !' : 
               ($moyenne_generale >= 10 ? 'Bon travail !' : 'Il faut travailler plus') ?>
          </p>
        </div>
      </div>

      <div class="row g-4">
        <!-- Filtre + Liste des notes -->
        <div class="col-lg-8">
          <div class="card-custom">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4>Mes notes (<?= count($notes) ?>)</h4>
              <select class="form-select w-auto" onchange="window.location.href='?course_id='+this.value">
                <option value="">Tous les cours</option>
                <?php foreach ($courses as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= ($selected_course == $c['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?php if (empty($notes)): ?>
              <div class="text-center py-5">
                <i class="fa fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5>Aucune note pour le moment</h5>
                <p class="text-muted">Vos enseignants n'ont pas encore saisi de notes.</p>
              </div>
            <?php else: ?>
              <div>
                <?php foreach ($notes as $n): ?>
                  <div class="note-item">
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong><?= htmlspecialchars($n['course_name']) ?></strong>
                        <span class="badge bg-info ms-2"><?= $n['type'] ?></span>
                      </div>
                      <div>
                        <span class="fs-4 fw-bold <?= $n['grade'] >= 10 ? 'text-success' : 'text-danger' ?>">
                          <?= number_format($n['grade'], 2) ?>/20
                        </span>
                        <small class="text-muted ms-3"><?= date('d/m/Y', strtotime($n['date'])) ?></small>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Moyennes par cours -->
        <div class="col-lg-4">
          <div class="card-custom">
            <h4>Moyenne par cours</h4>
            <?php foreach ($moyennes as $cours => $data): 
              $moy = round($data['sum'] / $data['count'], 2);
            ?>
              <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                <div>
                  <strong><?= htmlspecialchars($cours) ?></strong><br>
                  <small class="text-muted"><?= $data['count'] ?> note<?= $data['count']>1?'s':'' ?></small>
                </div>
                <span class="fs-5 fw-bold <?= $moy >= 10 ? 'text-success' : 'text-danger' ?>">
                  <?= number_format($moy, 2) ?>/20
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>