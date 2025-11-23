<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>systeme gestion des etudiants</title>
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
      font-size: 55px;
      font-weight: 700;
      color: #0d1b3e;
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
      gap: 8px;
      margin-right: 25px;
    }
    .feature-item i {
      color: #4ec3ff;
      font-size: 20px;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">systeme gestion des etudiants <span class="text-primary">.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        </ul>

        <div class="d-flex gap-3">
          <a href="login.php" class="btn btn-light px-4 py-2 rounded-pill fw-semibold">Sign In</a>
          <a href="register.php" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">Sign Up</a>
        </div>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="container">
      <div class="row align-items-center">

        <div class="col-lg-6">

         <h1 class="hero-title">Rejoignez-nous et développez vos compétences</h1>
        <p class="text-muted mt-3 mb-4">Chaque réussite commence par une initiative.
             Faites le choix d’apprendre, d’innover et d’avancer.</p>

          <div class="search-box d-flex align-items-center mt-4">
            <input type="text" class="form-control border-0" placeholder="Search courses...">
            <button class="btn btn-primary rounded-circle ms-2"><i class="fa fa-search"></i></button>
          </div>
        </div>

        <div class="col-lg-6 text-center mt-4 mt-lg-0">
          <img src="téléchargement.png"  alt="Teacher Image">
        </div>

      </div>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>