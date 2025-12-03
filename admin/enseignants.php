<?php
session_start();

// Vérification admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$message = "";

/* -------------------------------------------------------
   RÉCUPÉRATION DES COURS
-------------------------------------------------------- */
$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name ASC")->fetchAll();

/* -------------------------------------------------------
   AJOUT D'UN ENSEIGNANT
-------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {

    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $selected_courses = $_POST['course_id'] ?? [];

    if (empty($name) || empty($email) || empty($department) || empty($selected_courses)) {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires, y compris les cours.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            // Mot de passe par défaut
            $password = password_hash("default123", PASSWORD_DEFAULT);

            // 1) Ajouter dans users
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role)
                                   VALUES (?, ?, ?, 'enseignant')");
            $stmt->execute([$name, $email, $password]);

            $teacher_id = $pdo->lastInsertId();

            // 2) Ajouter dans teachers
            $stmt2 = $pdo->prepare("INSERT INTO teachers (user_id, department) VALUES (?, ?)");
            $stmt2->execute([$teacher_id, $department]);

            // 3) Assigner les cours dans course_assignments
            $stmt3 = $pdo->prepare("INSERT INTO course_assignments (course_id, teacher_id) VALUES (?, ?)");

            foreach ($selected_courses as $course_id) {
                $stmt3->execute([(int)$course_id, $teacher_id]);
            }

            $pdo->commit();

            $message = "<div class='alert alert-success'>Enseignant ajouté et assigné à ses cours.</div>";

        } catch (PDOException $e) {
            $pdo->rollBack();

            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-danger'>Cet email existe déjà.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
            }
        }
    }
}

/* -------------------------------------------------------
   LISTE ENSEIGNANTS + COURS ENSEIGNÉS
-------------------------------------------------------- */
$teachers = $pdo->query("
    SELECT 
        u.id, 
        u.name, 
        u.email,
        t.department,
        GROUP_CONCAT(c.name SEPARATOR ', ') AS courses
    FROM users u
    LEFT JOIN teachers t ON u.id = t.user_id
    LEFT JOIN course_assignments ca ON ca.teacher_id = u.id
    LEFT JOIN courses c ON ca.course_id = c.id
    WHERE u.role = 'enseignant'
    GROUP BY u.id
    ORDER BY u.name ASC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des enseignants</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }
    .sidebar {
      width: 260px; background: #ffffff; min-height: 100vh; position: fixed; left: 0; top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07); padding-top: 30px; z-index: 999;
    }
    .sidebar h4 { margin-left: 25px; margin-bottom: 25px; font-weight: 700; color: #4f46e5; }
    .sidebar .nav-link {
      color: #0d1b3e; padding: 14px 25px; font-size: 15px; font-weight: 500;
      border-radius: 8px; margin: 5px 15px; display: flex; align-items: center; transition: all 0.3s;
    }
    .sidebar .nav-link i { font-size: 18px; margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }
    .content { margin-left: 260px; padding: 100px 40px 40px; }
    .card-custom {
      background: white; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); padding: 30px;
    }
    .table th { background: #4f46e5; color: white; }
    .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  </style>
</head>

<body>
<nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">Système gestion des étudiants <span class="text-primary">.</span></a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="indexadmin.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="Dashboard.php">Tableaux de bord</a></li>
        </ul>
        <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a>
      </div>
    </div>
  </nav>

  <aside class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
      <li><a href="Dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li><a href="etudiants.php" class="nav-link "><i class="fa fa-users"></i> Gérer étudiants</a></li>
      <li><a href="enseignants.php" class="nav-link active"><i class="fa fa-chalkboard-teacher"></i> Gérer enseignants</a></li>
      <li><a href="cours.php" class="nav-link"><i class="fa fa-book"></i> Gérer cours</a></li>
      <li><a href="notes.php" class="nav-link"><i class="fa fa-pen-to-square"></i> Gérer notes</a></li>
    </ul>
  </aside>
<main class="content">
    <div class="container-fluid">
<h2 class="mb-4 fw-bold text-dark"><i class="fa fa-chalkboard-teacher text-primary"></i> Gérer les enseignants</h2>

<?= $message ?>

<div class="row">

<!-- Formulaire d'ajout -->
<div class="col-lg-5">
    <div class="card-custom mb-4">
        <h4 class="mb-4"><i class="fa fa-user-plus"></i> Ajouter un enseignant</h4>

        <form method="post">
            <input type="hidden" name="add_teacher" value="1">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nom complet</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Département</label>
                <input type="text" name="department" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Cours enseignés</label>
                <select name="course_id[]" class="form-select" multiple required size="6">
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Maintenez CTRL (Windows) ou CMD (Mac) pour sélectionner plusieurs cours.</small>
            </div>

            <button class="btn btn-primary w-100">
                <i class="fa fa-plus"></i> Ajouter l'enseignant
            </button>
        </form>
    </div>
</div>

<!-- Liste des enseignants -->
<div class="col-lg-7">
    <div class="card-custom">
        <h4 class="mb-4"><i class="fa fa-list"></i> Liste des enseignants (<?= count($teachers) ?>)</h4>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Département</th>
                    <th>Cours enseignés</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($teachers as $t): ?>
                <tr>
                    <td>#<?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td><?= htmlspecialchars($t['department']) ?></td>
                    <td><?= $t['courses'] ? htmlspecialchars($t['courses']) : "<em>Aucun</em>" ?></td>
                    <td>
                        <a href="edit_teacher.php?id=<?= $t['id'] ?>" class="btn btn-warning btn-sm">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a href="delete_teacher.php?id=<?= $t['id'] ?>" onclick="return confirm('Supprimer cet enseignant ?');"
                           class="btn btn-danger btn-sm">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>

            </table>
        </div>

    </div>
</div>

</div>

</div>

</body>
</html>
