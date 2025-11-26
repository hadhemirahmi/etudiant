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

// === Récupération de toutes les absences de l'étudiant ===
$absences = $pdo->prepare("
    SELECT 
        a.date,
        c.name AS course_name,
        u.name AS teacher_name
    FROM absences a
    JOIN courses c ON a.course_id = c.id
    JOIN users u ON a.teacher_id = u.id
    WHERE a.student_id = ?
    ORDER BY a.date DESC
");
$absences->execute([$student_id]);
$absences = $absences->fetchAll();

// === Statistiques par cours ===
$stats = $pdo->prepare("
    SELECT 
        c.name AS course_name,
        COUNT(*) AS total_absences
    FROM absences a
    JOIN courses c ON a.course_id = c.id
    WHERE a.student_id = ?
    GROUP BY c.id, c.name
    ORDER BY total_absences DESC
");
$stats->execute([$student_id]);
$stats = $stats->fetchAll();

$total_absences = array_sum(array_column($stats, 'total_absences'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes absences - Étudiant</title>
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
    .card-custom {
      background: white;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.12);
      padding: 30px;
      transition: 0.3s;
    }
    .card-custom:hover { transform: translateY(-8px); }
    .absence-date {
      background: #fee;
      color: #c33;
      padding: 8px 14px;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.9em;
    }
    .total-badge {
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
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
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">
          Déconnexion
        </a>
      </div>
    </div>
  </nav>

  <!-- Sidebar Étudiant -->
  <aside class="sidebar">
    <h4>Mon espace</h4>
    <ul class="nav flex-column">
      <li><a href="student_dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li><a href="mesnotes_etudiant.php" class="nav-link"><i class="fa fa-clipboard-check"></i> Mes notes</a></li>
      <li><a href="mesabsences.php" class="nav-link active"><i class="fa fa-calendar-times"></i> Mes absences</a></li>
      <li><a href="monprofil.php" class="nav-link"><i class="fa fa-user"></i> Mon profil</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">

      <!-- En-tête -->
      <div class="text-white mb-5">
        <h1 class="display-5 fw-bold">Mes absences</h1>
        <p class="lead opacity-90">Suivez vos absences par cours</p>
      </div>

      <!-- Carte principale -->
      <div class="row g-4">
        <!-- Total des absences -->
        <div class="col-lg-4">
          <div class="card-custom text-center">
            <i class="fa fa-calendar-times fa-4x text-danger mb-3 opacity-25"></i>
            <div class="total-badge"><?= $total_absences ?></div>
            <h5 class="text-dark fw-bold">Absence<?= $total_absences > 1 ? 's' : '' ?> totale<?= $total_absences > 1 ? 's' : '' ?></h5>
            <p class="text-muted">
              <?= $total_absences == 0 ? 'Félicitations ! Aucune absence' : 
                 ($total_absences <= 3 ? 'Attention, restez régulier' : 'Attention : trop d\'absences') ?>
            </p>
          </div>
        </div>

        <!-- Détail par cours -->
        <div class="col-lg-8">
          <div class="card-custom">
            <h4 class="mb-4">Détail par cours</h4>
            <?php if (empty($stats)): ?>
              <div class="text-center py-5">
                <i class="fa fa-smile fa-4x text-success mb-3"></i>
                <h5>Aucune absence enregistrée</h5>
                <p class="text-muted">Continuez comme ça !</p>
              </div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($stats as $s): ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($s['course_name']) ?></h6>
                      <small class="text-muted"><?= $s['total_absences'] ?> absence<?= $s['total_absences'] > 1 ? 's' : '' ?></small>
                    </div>
                    <span class="badge bg-danger rounded-pill fs-6">
                      <?= $s['total_absences'] ?>
                    </span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Historique complet -->
      <?php if (!empty($absences)): ?>
        <div class="card-custom mt-4">
          <h4 class="mb-4">Historique complet</h4>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Cours</th>
                  <th>Enseignant</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($absences as $a): ?>
                  <tr>
                    <td><span class="absence-date"><?= date('d/m/Y', strtotime($a['date'])) ?></span></td>
                    <td><strong><?= htmlspecialchars($a['course_name']) ?></strong></td>
                    <td><?= htmlspecialchars($a['teacher_name']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>