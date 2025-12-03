<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$message = '';

$student_id = $_GET['id'] ?? null;
if (!$student_id || !is_numeric($student_id)) {
    header("Location: etudiants.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'etudiant'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: etudiants.php");
    exit;
}
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();


$stmt2 = $pdo->prepare("SELECT course_id FROM enrollments WHERE student_id = ?");
$stmt2->execute([$student_id]);
$student_courses = $stmt2->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $passwd = trim($_POST['passwd']);
    $courses_selected = $_POST['course_id'] ?? [];

    if (empty($name) || empty($email) || empty($courses_selected)) {
        $message = "<div class='alert alert-warning'>Tous les champs sauf le mot de passe sont obligatoires.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt_update->execute([$name, $email, $student_id]);

            if (!empty($passwd)) {
                $password_hashed = password_hash($passwd, PASSWORD_DEFAULT);
                $stmt_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt_pass->execute([$password_hashed, $student_id]);
            }

            $stmt_del = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ?");
            $stmt_del->execute([$student_id]);


            $stmt_ins = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
            foreach ($courses_selected as $course_id) {
                $stmt_ins->execute([$student_id, (int)$course_id]);
            }

            $pdo->commit();
            $message = "<div class='alert alert-success'>Étudiant mis à jour avec succès !</div>";
            $student_courses = $courses_selected;
            header("Location: etudiants.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier étudiant</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; padding: 50px; }
    .card-custom { background: #fff; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px; max-width: 600px; margin: auto; }
  </style>
</head>
<body>

<div class="card-custom">
    <h3 class="mb-4"><i class="fa fa-user-edit"></i> Modifier étudiant</h3>

    <?php if ($message) echo $message; ?>

    <form method="post">
        <input type="hidden" name="edit_student" value="1">

        <div class="mb-3">
            <label class="form-label fw-semibold">Nom complet</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Mot de passe (laisser vide pour conserver)</label>
            <input type="password" name="passwd" class="form-control" minlength="6">
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Cours inscrits</label>
            <select name="course_id[]" class="form-select" multiple required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= in_array($c['id'], $student_courses) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success w-100"><i class="fa fa-save"></i> Enregistrer</button>
        <a href="etudiants.php" class="btn btn-secondary w-100 mt-2"><i class="fa fa-arrow-left"></i> Retour</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
