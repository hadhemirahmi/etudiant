<?php
session_start();

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

// Vérifier si l'id du teacher est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: enseignants.php");
    exit;
}

$teacher_id = (int)$_GET['id'];

// Récupérer les informations de l'enseignant
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, t.department, t.phone 
    FROM users u 
    LEFT JOIN teachers t ON u.id = t.user_id 
    WHERE u.id = ? AND u.role = 'enseignant'
");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch();

if (!$teacher) {
    header("Location: enseignants.php");
    exit;
}

// Gestion du formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);

    if ($name && $email) {
        try {
            // Mettre à jour la table users
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $teacher_id]);

            // Vérifier si l'enseignant existe dans teachers
            $stmt = $pdo->prepare("SELECT * FROM teachers WHERE user_id = ?");
            $stmt->execute([$teacher_id]);
            $exists = $stmt->fetch();

            if ($exists) {
                // Mise à jour de teachers
                $stmt = $pdo->prepare("UPDATE teachers SET department = ?, phone = ? WHERE user_id = ?");
                $stmt->execute([$department, $phone, $teacher_id]);
            } else {
                // Insérer si n'existe pas
                $stmt = $pdo->prepare("INSERT INTO teachers (user_id, department, phone) VALUES (?, ?, ?)");
                $stmt->execute([$teacher_id, $department, $phone]);
            }

            $message = "Enseignant mis à jour avec succès !";
            // Rafraîchir les données
            $stmt = $pdo->prepare("
                SELECT u.id, u.name, u.email, t.department, t.phone 
                FROM users u 
                LEFT JOIN teachers t ON u.id = t.user_id 
                WHERE u.id = ? AND u.role = 'enseignant'
            ");
            $stmt->execute([$teacher_id]);
            $teacher = $stmt->fetch();
            header("location: enseignants.php");
            exit;

        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
        }
    } else {
        $message = "Le nom et l'email sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modifier enseignant</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">

<div class="container">
    <h2 class="mb-4">Modifier enseignant</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nom</label>
            <input type="text" name="name" value="<?= htmlspecialchars($teacher['name'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($teacher['email'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Département</label>
            <input type="text" name="department" class="form-control" value="<?= htmlspecialchars($teacher['department'] ?? '') ?>">
        </div>

        <button class="btn btn-primary">Mettre à jour</button>
        <a href="enseignants.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

</body>
</html>
