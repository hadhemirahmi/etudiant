<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gérer cours</title>
  
  <!-- ICONS + BOOTSTRAP -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

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
        transition: 0.25s;
    }
    .sidebar .nav-link:hover {
      background: #f0f4ff;
      color: #4f46e5;
      text-decoration: none;
    }
    .sidebar .nav-link.active {
      background: #eef3ff;
      color: #4f46e5;
      text-decoration: none;
    }
    .sidebar i {
      margin-right: 12px;
    }
    </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
    <a class="navbar-brand" href="#">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="./indexenseignant.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="./Dashboard.php">tableaux de bord</a></li>
        </ul>
        <div class="d-flex gap-3">
          <a href="logout.php" class="btn btn-light px-4 py-2 rounded-pill fw-semibold">Sign Out</a>
        </div>
    </div>
  </nav>
    <!-- Sidebar -->
    <div class="sidebar">
    <h4 class="text-primary fw-bold">enseignant Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
            <a href="./mescours.php" class="nav-link"><i class="fa fa-book me-2"></i> mes cours</a>
            </li>
            <li class="nav-item">
            <a href="./mesetudiants.php" class="nav-link"><i class="fa fa-users me-2"></i> mes étudiants</a>
            </li>
            <li class="nav-item">
            <a href="./mesnotes.php" class="nav-link"><i class="fa fa-pen-to-square me-2"></i> saisir notes</a>
            </li>
            <li class="nav-item">
                <a href="./absence.php" class="nav-link"><i class="fa fa-calendar-check me-2"></i> absence</a>
            </li>

        </ul>
    </div>
    <div class="content" style="margin-left: 250px; padding: 40px;">
        <h2 class="mb-4">Bienvenue dans votre espace d'enseignant</h2>
        <p>Utilisez le menu de gauche pour naviguer entre vos cours, étudiants et notes.</p>
    </div>
</body>
</html>
