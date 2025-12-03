<?php
session_start();

// Vérification : seul un admin peut accéder à cette page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

$message = '';

// === Ajout d'un enseignant ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $name       = trim($_POST['name']);
    $email      = trim($_POST['email']);
    $department = trim($_POST['department']);
    $password   = password_hash('default123', PASSWORD_DEFAULT); // mot de passe par défaut (à changer après)

    if (!empty($name) && !empty($email) && !empty($department)) {
        try {
            // Insertion dans la table users (rôle enseignant)
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'enseignant')");
            $stmt->execute([$name, $email, $password]);

            // Récupérer l'ID du nouvel utilisateur
            $user_id = $pdo->lastInsertId();

            // Insertion dans la table teachers (optionnel si vous avez une table dédiée)
            $stmt2 = $pdo->prepare("INSERT INTO teachers (user_id, department) VALUES (?, ?)");
            $stmt2->execute([$user_id, $department]);

            $message = "<div class='alert alert-success'>Enseignant ajouté avec succès !</div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                $message = "<div class='alert alert-danger'>Cet email est déjà utilisé.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-warning'>Tous les champs sont obligatoires.</div>";
    }
}

// === Récupération de la liste des enseignants ===
$teachers = $pdo->query("
    SELECT u.id, u.name, u.email, t.department 
    FROM users u 
    LEFT JOIN teachers t ON u.id = t.user_id 
    WHERE u.role = 'enseignant'
    ORDER BY u.name ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gérer les enseignants - Admin</title>

  <!-- Bootstrap + FontAwesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body { background: #f4f7fc; font-family: 'Poppins', sans-serif; margin: 0; }
    .navbar { background: #fff !important; box-shadow: 0 2px 15px rgba(0,0,0,0.06); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .navbar-brand { font-size: 26px; font-weight: 700; color: #0d1b3e; }

    /* Sidebar */
    .sidebar {
      width: 260px;
      background: #ffffff;
      min-height: 100vh;
      position: fixed;
      left: 0;
      top: 76px;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07);
      padding-top: 30px;
      z-index: 999;
    }
    .sidebar h4 { margin-left: 25px; margin-bottom: 25px; font-weight: 700; color: #4f46e5; }
    .sidebar .nav-link {
      color: #0d1b3e; padding: 14px 25px; font-size: 15px; font-weight: 500;
      border-radius: 8px; margin: 5px 15px; display: flex; align-items: center;
      transition: all 0.3s;
    }
    .sidebar .nav-link i { font-size: 18px; margin-right: 12px; width: 25px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background: #eef3ff; color: #4f46e5; padding-left: 30px;
    }
    .sidebar .nav-link.active { background: #4f46e5; color: white !important; }

    /* Contenu principal */
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
    .table th { background: #4f46e5; color: white; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">Système gestion des étudiants <span class="text-primary">.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link active" href="indexadmin.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="Dashboard.php">Tableaux de bord</a></li>
        </ul>
        <div class="d-flex align-items-center gap-3">
           
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>
      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a href="Dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Tableau de bord</a></li>
      <li class="nav-item"><a href="etudiants.php" class="nav-link"><i class="fa fa-users"></i> Gérer étudiants</a></li>
      <li class="nav-item"><a href="enseignants.php" class="nav-link active"><i class="fa fa-chalkboard-teacher"></i> Gérer enseignants</a></li>
      <li class="nav-item"><a href="cours.php" class="nav-link"><i class="fa fa-book"></i> Gérer cours</a></li>
      <li class="nav-item"><a href="notes.php" class="nav-link"><i class="fa fa-pen-to-square"></i> Gérer notes</a></li>
    </ul>
  </aside>

  <!-- Contenu principal -->
  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-4 fw-bold text-dark"><i class="fa fa-chalkboard-teacher text-primary"></i> Gérer les enseignants</h2>

      <?php if ($message) echo $message; ?>

      <div class="row">
        <!-- Formulaire d'ajout -->
        <div class="col-lg-5">
          <div class="card-custom mb-4">
            <h4 class="mb-4"><i class="fa fa-user-plus"></i> Ajouter un enseignant</h4>
            <form method="post">
              <input type="hidden" name="add_teacher" value="1">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nom complet</label>
                <input type="text" name="name" class="form-control" placeholder="Ex: Jean Dupont" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" placeholder="jean.dupont@ecole.com" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Département</label>
                <input type="text" name="department" class="form-control" placeholder="Ex: Informatique" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">
                <i class="fa fa-plus"></i> Ajouter l'enseignant
              </button>
            </form>
            <small class="text-muted mt-3 d-block">
              Un mot de passe par défaut (<code>default123</code>) sera généré. L'enseignant pourra le changer à la première connexion.
            </small>
          </div>
        </div>

        <!-- Liste des enseignants -->
        <div class="col-lg-7">
          <div class="card-custom">
            <h4 class="mb-4"><i class="fa fa-list"></i> Liste des enseignants (<?= count($teachers) ?>)</h4>
            <?php if (empty($teachers)): ?>
              <p class="text-muted text-center py-5">Aucun enseignant enregistré pour le moment.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Nom complet</th>
                      <th>Email</th>
                      <th>Département</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($teachers as $t): ?>
                      <tr>
                        <td><strong>#<?= $t['id'] ?></strong></td>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td><?= htmlspecialchars($t['email']) ?></td>
                        <td><?= htmlspecialchars($t['department'] ?? 'Non renseigné') ?></td>
                        <td>
                          <button class="btn btn-sm btn-warning"><i class="fa fa-edit"></i> <a href="edit_teacher.php?id=<?= $t['id'] ?>">Modifier</a></button>
                          <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> <a href="delete_teacher.php?id=<?= $t['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet enseignant ?');">Supprimer</a></button>
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