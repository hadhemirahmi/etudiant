<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {

    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $passwd = trim($_POST['passwd']);
    $courses_selected = $_POST['course_id'] ?? []; 

    if (empty($name) || empty($email) || empty($passwd) || empty($courses_selected)) {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires.</div>";
    } else {
        try {
            $pdo->beginTransaction();

            $password = password_hash($passwd, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role)
                                   VALUES (?, ?, ?, 'etudiant')");
            $stmt->execute([$name, $email, $password]);

            $student_id = $pdo->lastInsertId();

            $stmt2 = $pdo->prepare("INSERT IGNORE INTO enrollments (student_id, course_id)
                                    VALUES (?, ?)");

            foreach ($courses_selected as $course_id) {
                $stmt2->execute([$student_id, (int)$course_id]);
            }

            $pdo->commit();
            $message = "<div class='alert alert-success'>Étudiant ajouté et inscrit à plusieurs cours avec succès !</div>";

        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) {
                $message = "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

$students = $pdo->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.created_at,
        GROUP_CONCAT(c.name SEPARATOR ', ') AS course_names
    FROM users u
    LEFT JOIN enrollments e ON u.id = e.student_id
    LEFT JOIN courses c ON e.course_id = c.id
    WHERE u.role = 'etudiant'
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll();

$courses = $pdo->query("SELECT id, name FROM courses ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gérer les étudiants - Admin</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

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
      <li><a href="etudiants.php" class="nav-link active"><i class="fa fa-users"></i> Gérer étudiants</a></li>
      <li><a href="enseignants.php" class="nav-link"><i class="fa fa-chalkboard-teacher"></i> Gérer enseignants</a></li>
      <li><a href="cours.php" class="nav-link"><i class="fa fa-book"></i> Gérer cours</a></li>
      <li><a href="notes.php" class="nav-link"><i class="fa fa-pen-to-square"></i> Gérer notes</a></li>
    </ul>
  </aside>

  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark"><i class="fa fa-users text-primary"></i> Gérer les étudiants</h2>

      <?php if ($message) echo $message; ?>

      <div class="row g-4">
       
        <!-- AJOUT ÉTUDIANT -->
        <div class="col-lg-5">
          <div class="card-custom mb-4">
            <h4 class="mb-4"><i class="fa fa-user-plus"></i> Ajouter un étudiant</h4>

            <form method="post">
              <input type="hidden" name="add_student" value="1">

              <div class="mb-3">
                <label class="form-label fw-semibold">Nom complet</label>
                <input type="text" name="name" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Inscrire à un ou plusieurs cours</label>
                <select name="course_id[]" class="form-select" multiple required>
                  <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>">
                      <?= htmlspecialchars($c['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Mot de passe</label>
                <input type="password" name="passwd" class="form-control" minlength="6" required>
              </div>

              <button type="submit" class="btn btn-primary w-100">Ajouter & inscrire</button>
            </form>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card-custom">
            <h4 class="mb-4"><i class="fa fa-list"></i> Liste des étudiants (<?= count($students) ?>)</h4>

            <?php if (empty($students)): ?>
              <p class="text-center text-muted py-5">Aucun étudiant enregistré.</p>

            <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Cours inscrits</th>
                    <th>Inscrit le</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($students as $s): ?>
                  <tr>
                    <td><strong>#<?= $s['id'] ?></strong></td>
                    <td><?= htmlspecialchars($s['name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= $s['course_names'] ?: "<em class='text-muted'>Aucun</em>" ?></td>
                    <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                    <td>
                      <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">
                        <i class="fa fa-edit"></i>
                      </a>
                      <a href="delete_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
                         onclick="return confirm('Supprimer cet étudiant ?')">
                        <i class="fa fa-trash"></i>
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
