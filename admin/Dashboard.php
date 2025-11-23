<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Systeme Gestion des Étudiants - Dashboard Admin</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f4f7fc;
      font-family: 'Poppins', sans-serif;
    }

    /* Navbar */
    .navbar {
      background: #ffffff !important;
      box-shadow: 0 2px 15px rgba(0,0,0,0.06);
    }

    .navbar-brand {
      font-size: 26px;
      font-weight: 700;
      color: #0d1b3e;
    }

    .navbar .nav-link {
      font-weight: 500;
      margin-left: 12px;
      transition: 0.25s;
    }

    .navbar .nav-link:hover {
      color: #4f46e5 !important;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: #ffffff;
      min-height: 100vh;
      padding-top: 80px;
      position: fixed;
      left: 0;
      top: 0;
      box-shadow: 2px 0 18px rgba(0,0,0,0.07);
      margin-top: 20px;
    }

    .sidebar h4 {
      margin-left: 22px;
      margin-bottom: 20px;
      font-weight: 700;
    }

    .sidebar .nav-link {
      color: #0d1b3e;
      padding: 12px 20px;
      font-size: 15px;
      font-weight: 500;
      border-radius: 6px;
      transition: 0.25s;
      
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: #eef3ff;
      color: #3b5bff;
      padding-left: 26px;
    }

    .sidebar i {
      font-size: 17px;
      margin-right: 8px;
    }

    /* Main Content */
    .content {
      margin-left: 250px;
      padding: 40px;
    }

    /* Card container */
    .register-container {
      max-width: 500px;
      background: #ffffff;
      padding: 30px;
      border-radius: 10px;
      margin: 80px auto;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      animation: fadeIn 0.6s ease-in-out;
    }

    .register-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 15px;
      color: #0d1b3e;
    }

    /* Fade-in animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Mobile */
    @media(max-width: 992px){
      .sidebar {
        display: none;
      }
      .content {
        margin-left: 0;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg py-3 fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">
        Système Gestion des Étudiants<span class="text-primary">.</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">

        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link" href="./indexadmin.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="./Dashboard.php">tableaux de bord</a></li>
        </ul>

        <div class="d-flex">
          <a href="logout.php" class="btn btn-outline-primary rounded-pill px-4">Sign Out</a>
        </div>

      </div>
    </div>
  </nav>

  <!-- Sidebar -->
  <aside class="sidebar">
    <h4 class="text-primary">Admin Panel</h4>

    <ul class="nav flex-column px-3">
      <li class="nav-item">
        <a href="./etudiants.php" class="nav-link">
          <i class="fa fa-users"></i> Gérer étudiants
        </a>
      </li>

      <li class="nav-item">
        <a href="./enseignants.php" class="nav-link">
          <i class="fa fa-chalkboard-teacher"></i> Gérer enseignants
        </a>
      </li>

      <li class="nav-item">
        <a href="./cours.php" class="nav-link">
          <i class="fa fa-book"></i> Gérer cours
        </a>
      </li>

      <li class="nav-item">
        <a href="./notes.php" class="nav-link">
          <i class="fa fa-pen-to-square"></i> Gérer notes
        </a>
      </li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="content">
    <div class="register-container">
      <h2 class="register-title">Bienvenue dans le Dashboard</h2>
      <p class="text-muted">Sélectionnez une section dans le menu de gauche pour commencer.</p>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
