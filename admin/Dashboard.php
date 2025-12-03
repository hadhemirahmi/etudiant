<?php
session_start();


if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../Database.php';
$pdo = connectDatabase();

try {
    $total_etudiants   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'etudiant'")->fetchColumn();
    $total_enseignants = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'enseignant'")->fetchColumn();
    $total_cours       = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $total_notes       = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();
} catch (Exception $e) {
    $total_etudiants = $total_enseignants = $total_cours = $total_notes = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de bord - Admin</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f4f7fc;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Navbar */
    .navbar {
      background: #ffffff !important;
      box-shadow: 0 2px 15px rgba(0,0,0,0.06);
      position: fixed;
      top: 0;
      width: 100%;
      z-index: 1000;
    }

    .navbar-brand {
      font-size: 26px;
      font-weight: 700;
      color: #0d1b3e;
    }

    /* Sidebar */
    .sidebar {
      width: 260px;
      background: #ffffff;
      min-height: 100vh;
      position: fixed;
      left: 0;
      top: 76px; /* hauteur navbar */
      box-shadow: 2px 0 18px rgba(0,0,0,0.07);
      padding-top: 30px;
      z-index: 999;
    }

    .sidebar h4 {
      margin-left: 25px;
      margin-bottom: 25px;
      font-weight: 700;
      color: #4f46e5;
    }

    .sidebar .nav-link {
      color: #0d1b3e;
      padding: 14px 25px;
      font-size: 15px;
      font-weight: 500;
      border-radius: 8px;
      margin: 5px 15px;
      transition: all 0.3s;
      display: flex;
      align-items: center;
    }

    .sidebar .nav-link i {
      font-size: 18px;
      margin-right: 12px;
      width: 25px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: #eef3ff;
      color: #4f46e5;
      padding-left: 30px;
    }

    .sidebar .nav-link.active {
      background: #4f46e5;
      color: white !important;
    }

    /* Main Content */
    .content {
      margin-left: 260px;
      padding: 100px 40px 40px;
      min-height: 100vh;
    }

    /* Cards */
    .stat-card {
      background: white;
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: transform 0.3s;
      height: 100%;
    }

    .stat-card:hover {
      transform: translateY(-8px);
    }

    .stat-icon {
      font-size: 40px;
      opacity: 0.15;
      position: absolute;
      right: 20px;
      top: 20px;
    }

    .stat-number {
      font-size: 38px;
      font-weight: 700;
      color: #0d1b3e;
    }

    .stat-label {
      font-size: 16px;
      color: #6c757d;
      margin-top: 8px;
    }
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
      <li class="nav-item">
        <a href="Dashboard.php" class="nav-link active">
          <i class="fa fa-tachometer-alt"></i> Tableau de bord
        </a>
      </li>
      <li class="nav-item">
        <a href="etudiants.php" class="nav-link">
          <i class="fa fa-users"></i> Gérer étudiants
        </a>
      </li>
      <li class="nav-item">
        <a href="enseignants.php" class="nav-link">
          <i class="fa fa-chalkboard-teacher"></i> Gérer enseignants
        </a>
      </li>
      <li class="nav-item">
        <a href="cours.php" class="nav-link">
          <i class="fa fa-book"></i> Gérer cours
        </a>
      </li>
      <li class="nav-item">
        <a href="notes.php" class="nav-link">
          <i class="fa fa-pen-to-square"></i> Gérer notes
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="content">
    <div class="container-fluid">
      <h2 class="mb-2 fw-bold text-dark">Tableau de bord Administrateur</h2>
      <p class="text-muted mb-5">Vue d'ensemble du système de gestion des étudiants</p>

      <div class="row g-4">
        <!-- Étudiants -->
        <div class="col-md-3">
          <div class="stat-card position-relative text-primary border-start border-primary border-5">
            <i class="fa fa-users stat-icon"></i>
            <div class="stat-number"><?= $total_etudiants ?></div>
            <div class="stat-label">Étudiants inscrits</div>
          </div>
        </div>

        <!-- Enseignants -->
        <div class="col-md-3">
          <div class="stat-card position-relative text-success border-start border-success border-5">
            <i class="fa fa-chalkboard-teacher stat-icon"></i>
            <div class="stat-number"><?= $total_enseignants ?></div>
            <div class="stat-label">Enseignants</div>
          </div>
        </div>

        <!-- Cours -->
        <div class="col-md-3">
          <div class="stat-card position-relative text-warning border-start border-warning border-5">
            <i class="fa fa-book-open stat-icon"></i>
            <div class="stat-number"><?= $total_cours ?></div>
            <div class="stat-label">Cours disponibles</div>
          </div>
        </div>

        <!-- Notes -->
        <div class="col-md-3">
          <div class="stat-card position-relative text-info border-start border-info border-5">
            <i class="fa fa-clipboard-check stat-icon"></i>
            <div class="stat-number"><?= $total_notes ?></div>
            <div class="stat-label">Notes saisies</div>
          </div>
        </div>
      </div>

      <div class="mt-5 p-4 bg-white rounded shadow-sm">
        <h5><i class="fa fa-info-circle text-primary"></i> Dernières actions</h5>
        <p class="text-muted">Utilisez le menu de gauche pour gérer les étudiants, enseignants, cours et notes.</p>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>