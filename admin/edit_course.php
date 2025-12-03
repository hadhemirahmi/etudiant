<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include '../Database.php';
$pdo = connectDatabase();
$message = '';
$course = null;

if (isset($_GET['id'])) {
    $course_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        $message = '<div class="alert alert-danger">Cours non trouvé.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $course_id = (int)$_POST['course_id'];
    $name = trim($_POST['course_name']);
    $code = strtoupper(trim($_POST['course_code']));
    $credits = (int)$_POST['credits'];
    $desc = trim($_POST['description'] ?? '');

    if (!empty($name) && !empty($code) && $credits > 0) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM courses WHERE code = ? AND id != ?");
            $stmt->execute([$code, $course_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                $message = '<div class="alert alert-danger">Ce code de cours est déjà utilisé par un autre cours.</div>';
            } else {
                $stmt = $pdo->prepare("UPDATE courses SET name = ?, code = ?, credits = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $code, $credits, $desc, $course_id]);
                $message = '<div class="alert alert-success">Cours modifié avec succès !</div>';
                $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
                $stmt->execute([$course_id]);
                $course = $stmt->fetch();
            }
            header("Location: cours.php");
            exit;
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Tous les champs obligatoires doivent être remplis.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modifier le cours - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
    .btn-back {
      background: #6c757d;
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 8px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
    }
    .btn-back:hover {
      background: #5a6268;
      color: white;
    }
  </style>
</head>
<body>
  <main class="content">
    <div class="container-fluid">
      
      <h2 class="mb-4 fw-bold text-dark">Modifier le cours</h2>

      <?php if ($message) echo $message; ?>

      <?php if ($course): ?>
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="card-custom">
              <h4 class="mb-4">Modifier les informations du cours</h4>
              <form method="post">
                <input type="hidden" name="update_course" value="1">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Nom du cours</label>
                  <input type="text" name="course_name" class="form-control" 
                         value="<?= htmlspecialchars($course['name']) ?>" 
                         placeholder="Ex: Programmation Web" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Code du cours</label>
                  <input type="text" name="course_code" class="form-control" 
                         value="<?= htmlspecialchars($course['code']) ?>" 
                         placeholder="Ex: WEB301" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Crédits ECTS</label>
                  <input type="number" name="credits" class="form-control" 
                         value="<?= $course['credits'] ?>" 
                         min="1" max="30" required>
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Description (facultatif)</label>
                  <textarea name="description" class="form-control" rows="4" 
                            placeholder="Description du cours..."><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
                </div>
                
                <div class="d-flex gap-3">
                  <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fa fa-save me-2"></i> Enregistrer les modifications
                  </button>
                  <a href="cours.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
              </form>
            </div>
            
            
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle me-2"></i>
          Impossible de charger les données du cours. Veuillez vérifier que le cours existe.
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>