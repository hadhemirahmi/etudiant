<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Espace Étudiant</title>

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f7faff;
      font-family: 'Poppins', sans-serif;
    }

    .nav-link:hover {
      color: #5a4ff3 !important;
    }

    .hero {
      padding: 80px 0;
    }
    .hero-title {
      font-size: 48px;
      font-weight: 700;
      color: #0d1b3e;
      line-height: 1.2;
    }

    .search-box {
      background: white;
      border-radius: 50px;
      padding: 12px 25px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 15px;
    }

    .feature-item i {
      color: #4f46e5;
      font-size: 22px;
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">

      <a class="navbar-brand fs-3 fw-bold" href="#">
        Espace Étudiant <span class="text-primary">.</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">

        <!-- Menu -->
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link active" href="indexetudiant.php">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="mescours.php">Mes Cours</a></li>
          <li class="nav-item"><a class="nav-link" href="mesabsences.php">Mes Absences</a></li>
          <li class="nav-item"><a class="nav-link" href="mesnotes.php">Mes Notes</a></li>
        </ul>

        <!-- Espace déconnexion -->
        <div class="d-flex align-items-center gap-3">
           
          <a href="../logout.php" class="btn btn-outline-danger rounded-pill px-4">Déconnexion</a> 
        </div>

      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="row align-items-center">

        <div class="col-lg-6">
          <h1 class="hero-title">Bienvenue dans votre espace Étudiant</h1>
          <p class="text-muted mt-3 mb-4">
            Consultez vos cours, vos absences, vos notes et restez toujours informé de votre progression.
          </p>

          <div class="search-box d-flex align-items-center mt-4">
            <input type="text" class="form-control border-0" placeholder="Rechercher un cours...">
            <button class="btn btn-primary rounded-circle ms-2">
              <i class="fa fa-search"></i>
            </button>
          </div>

          <!-- Features -->
          <div class="mt-4">
            <div class="feature-item">
              <i class="fa fa-book"></i>
              <span>Accéder à tous vos cours en un clic</span>
            </div>
            <div class="feature-item">
              <i class="fa fa-calendar-xmark"></i>
              <span>Consulter vos absences et retards</span>
            </div>
            <div class="feature-item">
              <i class="fa fa-star"></i>
              <span>Voir vos notes et votre progression</span>
            </div>
          </div>

        </div>

        <!-- Image -->
        <div class="col-lg-6 text-center mt-4 mt-lg-0">
          <img src="../téléchargement.png" width="460" alt="Student Image">
        </div>

      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
