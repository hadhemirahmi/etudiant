<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — notes</title>

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
    <nav class="navbar navbar-expand-lg py-3 fixed-top">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">systeme gestion des etudiants <span class="text-primary">.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav mx-auto ">
          <li class="nav-item"><a class="nav-link " href="./indexadmin.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="./Dashboard.php">tableaux de bord</a></li>
        </ul>
        

        <div class="d-flex gap-3">
         <a href="logout.php" class="btn btn-light px-4 py-2 rounded-pill fw-semibold">Sign Out</a>
        </div>
      </div>
    </div>
  </nav>

  <div class="sidebar">
    <h4 class="text-primary fw-bold">Admin Panel</h4>

    <ul class="nav flex-column px-2">
      <li class="nav-item">
        <a href="./etudiants.php" class="nav-link">
          <i class="fa fa-users me-2"></i> Gérer étudiants
        </a>
      </li>

      <li class="nav-item">
        <a href="./enseignants.php" class="nav-link">
          <i class="fa fa-chalkboard-teacher me-2"></i> Gérer enseignants
        </a>
      </li>

      <li class="nav-item">
        <a href="./cours.php" class="nav-link">
          <i class="fa fa-book me-2"></i> Gérer cours
        </a>
      </li>

      <li class="nav-item">
        <a href="./notes.php" class="nav-link">
          <i class="fa fa-pen-to-square me-2"></i> Gérer notes
        </a>
      </li>

    </ul>
  </div>
  <div class="content">
    <div class="register-container">
      <h2 class="register-title">Gérer notes</h2>
      <form action="" method="post">
      <div class="mb-3">
          <label for="name" class="form-label">Nom matière</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Entrez le nom complet" required />
        </div>
        <div class="mb-3">
          <label for="grade" class="form-label">Note</label>
          <input type="number" class="form-control" id="grade" name="grade" placeholder="Entrez la note" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Ajouter la note</button>
      </form>
    </div>
  </div>

</body>
</html>
