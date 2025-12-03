<?php
session_start();

// Vérification : seul un étudiant peut accéder
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'etudiant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? '';

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
$absences = $absences->fetchAll(PDO::FETCH_ASSOC);

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
$stats = $stats->fetchAll(PDO::FETCH_ASSOC);

// Total
$total_absences = array_sum(array_column($stats, 'total_absences')) ?: 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes absences - Étudiant</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: #f7faff;
      font-family: 'Poppins', sans-serif;
    }

    

    .card-custom {
      background: white;
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.06);
      transition: 0.2s;
    }

    .card-custom:hover {
      transform: scale(1.01);
    }

    .total-badge {
      background: #4f46e5;
      color: white;
      font-size: 36px;
      font-weight: bold;
      padding: 8px 20px;
      border-radius: 50px;
      display: inline-block;
      margin-bottom: 10px;
      box-shadow: 0 8px 18px rgba(79,70,229,0.25);
    }

    .absence-date {
      font-weight: 600;
    }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm sticky-top">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">
        Espace Étudiant <span class="text-primary">.</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="indexetudiant.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="mescours.php">Mes Cours</a></li>
          <li class="nav-item"><a class="nav-link active" href="mesabsences.php">Mes Absences</a></li>
          <li class="nav-item"><a class="nav-link" href="mesnotes.php">Mes Notes</a></li>
        </ul>
    <div class="d-flex align-items-center gap-3">
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>
    </div>
</nav>

<main class="content">
    <div class="container-fluid py-4">

      <h1 class="fw-bold mb-1">Mes absences</h1>
      <p class="text-muted mb-4">Suivez vos absences par cours</p>

      <div class="row g-4">

        <!-- TOTAL ABSENCES -->
        <div class="col-lg-4">
          <div class="card-custom text-center">
            <i class="fa fa-calendar-times fa-3x text-danger opacity-50 mb-2"></i>

            <div class="total-badge"><?= $total_absences ?></div>

            <h5 class="fw-bold">Absence<?= $total_absences > 1 ? 's' : '' ?> totale<?= $total_absences > 1 ? 's' : '' ?></h5>

            <p class="text-muted">
              <?= $total_absences == 0
                ? 'Félicitations ! Aucune absence'
                : ($total_absences <= 3
                    ? 'Attention, restez régulier'
                    : 'Attention : trop d\'absences'
                  ) ?>
            </p>
          </div>
        </div>

        <!-- DETAILS PAR COURS -->
        <div class="col-lg-8">
          <div class="card-custom">
            <h4 class="mb-4">Détail par cours</h4>

            <?php if (empty($stats)): ?>
              <div class="text-center py-5">
                <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Aucune absence enregistrée</h5>
                <p class="text-muted">Continuez comme ça !</p>
              </div>
            <?php else: ?>
              <div class="list-group list-group-flush">
                <?php foreach ($stats as $s): ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div>
                      <h6 class="mb-1"><?= htmlspecialchars($s['course_name']) ?></h6>
                      <small class="text-muted">
                        <?= $s['total_absences'] ?> absence<?= $s['total_absences'] > 1 ? 's' : '' ?>
                      </small>
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

      <!-- HISTORIQUE COMPLET -->
      <?php if (!empty($absences)): ?>
        <div class="card-custom mt-4">
          <h4 class="mb-3">Historique complet</h4>

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
                    <td class="absence-date"><?= date('d/m/Y', strtotime($a['date'])) ?></td>
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
