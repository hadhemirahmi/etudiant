<?php
session_start();

// Vérification : seul un enseignant peut accéder
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'enseignant') {
    header("Location: login.php");
    exit;
}

include 'Database.php';
$pdo = connectDatabase();

$teacher_id = $_SESSION['user_id'];
$message = '';

// === Prise d'absence ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_absences'])) {
    $course_id = $_POST['course_id'];
    $date      = $_POST['date'];
    $absents   = $_POST['absent'] ?? []; // tableau des étudiants absents

    try {
        $pdo->beginTransaction();

        // On supprime les anciennes absences pour cette date et ce cours (pour éviter doublons)
        $del = $pdo->prepare("DELETE FROM absences WHERE course_id = ? AND date = ?");
        $del->execute([$course_id, $date]);

        // On insère les nouvelles absences
        if (!empty($absents)) {
            $stmt = $pdo->prepare("INSERT INTO absences (student_id, course_id, date, teacher_id) VALUES (?, ?, ?, ?)");
            foreach ($absents as $student_id) {
                $stmt->execute([$student_id, $course_id, $date, $teacher_id]);
            }
        }

        $pdo->commit();
        $message = "<div class='alert alert-success'>Absences enregistrées avec succès pour le " . date('d/m/Y', strtotime($date)) . " !</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// === Récupérer les cours de l'enseignant ===
$courses = $pdo->prepare("
    SELECT c.id, c.name 
    FROM courses c
    JOIN course_assignments ca ON c.id = ca.course_id
    WHERE ca.teacher_id = ?
    ORDER BY c.name
");
$courses->execute([$teacher_id]);
$courses = $courses->fetchAll();

// === Historique des absences saisies par l'enseignant ===
$history = $pdo->prepare("
    SELECT DISTINCT a.date, c.name AS course_name, COUNT(a.student_id) AS nb_absents
    FROM absences a
    JOIN courses c ON a.course_id = c.id
    WHERE a.teacher_id = ?
    GROUP BY a.date, a.course_id
    ORDER BY a.date DESC
");
$history->execute([$teacher_id]);
$history = $history->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prise d'absences - Enseignant</title>
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
    .card-custom { background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold" href="#">Gestion Étudiants</a>
      <div class="d-flex align-items-center gap-3">
        <span class="text-muted">Bonjour, <strong><?= htmlspecialchars($_SESSION['name']) ?></strong></span>
        <a href="logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
      </div>
    </div>
  </nav>

  <!-- Sidebar Enseignant -->
  <aside class="sidebar">
    <h4>Enseignant</h4>
    <ul class="nav flex-column">
      <li><a href="teacher_dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li><a href="absence.php" class="nav-link active"><i class="fa fa-calendar-times"></i> Prise d'absences</a></li>
      <li><a href="teacher_notes.php" class="nav-link"><i class="fa fa-pen-to-square"></i> Saisir les notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark"><i class="fa fa-calendar-times text-danger"></i> Prise d'absences</h2>

      <?php if ($message) echo $message; ?>

      <div class="row g-4">
        <!-- Formulaire de prise d'absence -->
        <div class="col-lg-8">
          <div class="card-custom">
            <h4 class="mb-4"><i class="fa fa-users-slash"></i> Marquer les absents</h4>
            <form method="post">
              <div class="row g-3 mb-4">
                <div class="col-md-5">
                  <label class="form-label fw-semibold">Cours</label>
                  <select name="course_id" class="form-select" required onchange="this.form.submit()">
                    <option value="">-- Choisir un cours --</option>
                    <?php foreach ($courses as $c): ?>
                      <option value="<?= $c['id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id'] == $c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Date</label>
                  <input type="date" name="date" class="form-control" value="<?= $_POST['date'] ?? date('Y-m-d') ?>" required>
                </div>
              </div>

              <?php if (isset($_POST['course_id']) && !empty($_POST['course_id'])): 
                $course_id = $_POST['course_id'];
                $students = $pdo->prepare("
                    SELECT u.id, u.name 
                    FROM users u
                    JOIN enrollments e ON u.id = e.student_id
                    WHERE e.course_id = ?
                    ORDER BY u.name
                ");
                $students->execute([$course_id]);
                $students = $students->fetchAll();
              ?>
                <div class="table-responsive">
                  <table class="table table-bordered align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Étudiant</th>
                        <th width="100" class="text-center">
                          <input type="checkbox" id="select-all"> Absent
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($students as $s): ?>
                        <tr>
                          <td><?= htmlspecialchars($s['name']) ?></td>
                          <td class="text-center">
                            <input type="checkbox" name="absent[]" value="<?= $s['id'] ?>">
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <button type="submit" name="save_absences" class="btn btn-danger mt-3">
                  <i class="fa fa-save"></i> Enregistrer les absences
                </button>
              <?php endif; ?>
            </form>
          </div>
        </div>

        <!-- Historique -->
        <div class="col-lg-4">
          <div class="card-custom">
            <h4 class="mb-4"><i class="fa fa-history"></i> Historique</h4>
            <?php if (empty($history)): ?>
              <p class="text-muted">Aucune absence enregistrée.</p>
            <?php else: ?>
              <div class="list-group">
                <?php foreach ($history as $h): ?>
                  <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                      <h6 class="mb-1"><?= htmlspecialchars($h['course_name']) ?></h6>
                      <small><?= date('d/m/Y', strtotime($h['date'])) ?></small>
                    </div>
                    <small class="text-danger"><strong><?= $h['nb_absents'] ?></strong> absent(s)</small>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('select-all')?.addEventListener('change', function() {
      document.querySelectorAll('input[name="absent[]"]').forEach(cb => cb.checked = this.checked);
    });
  </script>
</body>
</html>