<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$student_id = $_SESSION['user_id'];


$selected_course = $_GET['course_id'] ?? '';

// === Liste des cours ===
$courses = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
    ORDER BY c.name
");
$courses->execute([$student_id]);
$courses = $courses->fetchAll();

// === Notes ===
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

// === Moyennes ===
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
      background: #eef1f7;
      font-family: 'Poppins', sans-serif;
    }

    .navbar {
      border-bottom: 2px solid #e2e6f0;
    }

    .card-custom {
      background: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.07);
    }

    .grade-badge {
      font-size: 28px;
      padding: 10px 25px;
      border-radius: 50px;
      display: inline-block;
      font-weight: bold;
    }

    .note-item {
      padding: 15px 10px;
      border-radius: 12px;
      background: #f9fafc;
      margin-bottom: 12px;
      border: 1px solid #e6e9f2;
      transition: 0.2s;
    }
    .note-item:hover {
      transform: scale(1.01);
      background: #ffffff;
    }

    h4 {
      font-weight: 600;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">
        Espace Étudiant <span class="text-primary">.</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link " href="indexetudiant.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="mescours.php">Mes Cours</a></li>
          <li class="nav-item"><a class="nav-link" href="mesabsences.php">Mes Absences</a></li>
          <li class="nav-item"><a class="nav-link active" href="mesnotes.php">Mes Notes</a></li>
        </ul>

       <div class="d-flex align-items-center gap-3">
           
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>
      </div>
    </div>
</nav>

<main class="content py-4">
    <div class="container">

      <!-- Moyenne générale -->
      <div class="text-center mb-5">
        <div class="card-custom d-inline-block">
          <h2 class="mb-2 fw-bold">Moyenne générale</h2>
          <div class="grade-badge <?= $moyenne_generale >= 10 ? 'bg-success' : 'bg-danger' ?> text-white">
            <?= number_format($moyenne_generale, 2) ?> / 20
          </div>
          <p class="mt-3 text-muted fs-5">
            <?= $moyenne_generale >= 12 ? 'Excellent travail !' : 
               ($moyenne_generale >= 10 ? 'Bon travail !' : 'Il faut travailler plus') ?>
          </p>
        </div>
      </div>

      <div class="row g-4">

        <!-- Notes -->
        <div class="col-lg-8">
          <div class="card-custom">

            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="fw-bold">Mes notes (<?= count($notes) ?>)</h4>

              <select class="form-select w-auto shadow-sm" onchange="window.location.href='?course_id='+this.value">
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
                <h5>Aucune note disponible</h5>
                <p class="text-muted">Les enseignants n'ont pas encore ajouté de notes.</p>
              </div>

            <?php else: ?>
              <?php foreach ($notes as $n): ?>
                <div class="note-item">
                  <div class="d-flex justify-content-between">
                    <div>
                      <strong class="fs-5"><?= htmlspecialchars($n['course_name']) ?></strong>
                      <span class="badge bg-primary ms-2"><?= $n['type'] ?></span>
                    </div>
                    <div>
                      <span class="fs-4 fw-bold <?= $n['grade'] >= 10 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($n['grade'], 2) ?>/20
                      </span>
                      <small class="text-muted ms-3 d-block">
                        <?= date('d/m/Y', strtotime($n['date'])) ?>
                      </small>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

          </div>
        </div>

        <!-- Moyennes -->
        <div class="col-lg-4">
          <div class="card-custom">
            <h4 class="fw-bold mb-3">Moyenne par cours</h4>

            <?php foreach ($moyennes as $cours => $data):
              $moy = round($data['sum'] / $data['count'], 2);
            ?>
              <div class="d-flex justify-content-between py-3 border-bottom">
                <div>
                  <strong><?= htmlspecialchars($cours) ?></strong><br>
                  <small class="text-muted"><?= $data['count'] ?> note<?= $data['count']>1 ? 's':'' ?></small>
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
