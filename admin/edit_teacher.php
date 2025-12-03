<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include "../Database.php";
$pdo = connectDatabase();

$message = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID enseignant invalide.");
}

$teacher_id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        t.department
    FROM users u
    LEFT JOIN teachers t ON u.id = t.user_id
    WHERE u.id = ? AND u.role = 'enseignant'
");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("Enseignant introuvable.");
}
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll();


$stmt2 = $pdo->prepare("SELECT course_id FROM course_assignments WHERE teacher_id = ?");
$stmt2->execute([$teacher_id]);
$assigned_courses = $stmt2->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $selected_courses = $_POST['course_id'] ?? [];

    if (empty($name) || empty($email) || empty($department) || empty($selected_courses)) {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires, y compris les cours.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt_update = $pdo->prepare("
                UPDATE users SET name = ?, email = ? WHERE id = ?
            ");
            $stmt_update->execute([$name, $email, $teacher_id]);


            $stmt_dep = $pdo->prepare("
                UPDATE teachers SET department = ? WHERE user_id = ?
            ");
            $stmt_dep->execute([$department, $teacher_id]);

            $pdo->prepare("DELETE FROM course_assignments WHERE teacher_id = ?")
                ->execute([$teacher_id]);

            $stmt_add = $pdo->prepare("
                INSERT INTO course_assignments (course_id, teacher_id) VALUES (?, ?)
            ");

            foreach ($selected_courses as $cid) {
                $stmt_add->execute([(int)$cid, $teacher_id]);
            }

            $pdo->commit();

            $message = "<div class='alert alert-success'>Modifications enregistrées avec succès.</div>";
            $assigned_courses = $selected_courses;
            $teacher['name'] = $name;
            $teacher['email'] = $email;
            $teacher['department'] = $department;

        } catch (PDOException $e) {
            $pdo->rollBack();

            if ($e->getCode() === "23000") {
                $message = "<div class='alert alert-danger'>Cet email existe déjà.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        header("Location: enseignants.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier enseignant</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
body { background: #f4f7fc; font-family: 'Poppins', sans-serif; }
.card-custom {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    max-width: 700px;
    margin: 50px auto;
}
</style>

</head>

<body>

<div class="card-custom">
    <h3 class="mb-4 text-primary"><i class="fa fa-edit"></i> Modifier un enseignant</h3>

    <?= $message ?>

    <form method="post">

        <div class="mb-3">
            <label class="form-label fw-semibold">Nom complet</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($teacher['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($teacher['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Département</label>
            <input type="text" name="department" class="form-control"
                   value="<?= htmlspecialchars($teacher['department']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Cours enseignés</label>
            <select name="course_id[]" class="form-select" multiple required size="6">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= in_array($c['id'], $assigned_courses) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Maintenez CTRL (Windows) ou CMD (Mac) pour sélectionner plusieurs cours.</small>
        </div>

        <button class="btn btn-primary w-100"><i class="fa fa-save"></i> Enregistrer</button>

    </form>
</div>

</body>
</html>
