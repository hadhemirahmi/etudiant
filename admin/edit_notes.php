<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include '../Database.php';
$pdo = connectDatabase();

$message = '';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: notes.php");
    exit;
}
$note_id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) {
    header("Location: notes.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $course_id  = (int)$_POST['course_id'];
    $grade      = floatval($_POST['grade']);
    $type       = trim($_POST['type']);
    $date       = trim($_POST['date']);

    if ($student_id && $course_id && $grade >= 0 && $type && $date) {
        try {
            $stmt = $pdo->prepare("UPDATE notes SET student_id = ?, course_id = ?, grade = ?, type = ?, date = ? WHERE id = ?");
            $stmt->execute([$student_id, $course_id, $grade, $type, $date, $note_id]);

            header("Location: notes.php");
            exit;
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
        }
        header("Location: notes.php");
        exit;
    } else {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires et la note doit être positive.</div>";
    }
}
$students = $pdo->query("SELECT id, name FROM users WHERE role='etudiant' ORDER BY name")->fetchAll();
 
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier la note - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    body { font-family: Arial, sans-serif; background: #f4f6f9; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }

    .sidebar {
      width: 260px;
      background: #ffffff;
      position: fixed;
      top: 60px;
      left: 0;
      height: 100%;
      padding: 20px;
      box-shadow: 2px 0 15px rgba(0,0,0,0.06);
    }
    .sidebar .nav-link {
      color: #333;
      margin-bottom: 10px;
      font-weight: 500;
    }
    .sidebar .nav-link.active {
      background: #4f46e5;
      color: #fff !important;
      border-radius: 8px;
    }
    .content {
      margin-left: 260px;
      padding: 100px 40px 40px;
    }
    .card-custom {
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      padding: 30px;
    }
    .form-label { font-weight: 600; }
    </style>
</head>
<body>
    <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark">Modifier la note</h2>
      <?php echo $message; ?>
      <div class="card-custom">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="student_id" class="form-label">Étudiant</label>
            <select class="form-select" id="student_id" name="student_id" required>
              <option value="">Sélectionner un étudiant</option>
              <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>" <?= $student['id'] == $note['student_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($student['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
            <div class="mb-3">
                <label for="course_id" class="form-label">Cours</label>
                <select class="form-select" id="course_id" name="course_id" required>
                <option value="">Sélectionner un cours</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course['id'] ?>" <?= $course['id'] == $note['course_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['name']) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>
          <div class="mb-3">
            <label for="grade" class="form-label">Note</label>
            <input type="number" step="0.01" class="form-control" id="grade" name="grade" value="<?= htmlspecialchars($note['grade']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type de note</label>
                <input type="text" class="form-control" id="type" name="type" value="<?= htmlspecialchars($note['type']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="datetime-local" class="form-control" id="date" name="date" value="<?= date('Y-m-d\TH:i', strtotime($note['date'])) ?>" required>
            </div>
          <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
      </div>
    </div>
  </main>
</body>
</html>