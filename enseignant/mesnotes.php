<!DOCTYPE html> 
<html lang="fr">
<head>  
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gérer notes</title>
    
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
        }
    </style>
</head> 
<body>
  <nav class="navbar navbar-expand-lg navbar-light fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#">Espace Enseignant</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto ">
              <li class="nav-item"><a class="nav-link" href="indexenseignant.php">Home</a></li>
              <li class="nav-item"><a class="nav-link" href="Dashboard.php">tableaux de bord</a></li>
            </ul>
        </div>
    </div>
    </nav>
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
    }
    </style>
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
    <div class="container" style="margin-left: 270px; padding-top: 100px;">
        <h2>Gérer les notes des étudiants</h2>
        <form>
            <div class="mb-3">
                <label for="courseSelect" class="form-label">Sélectionner le cours</label>
                <select class="form-select" id="courseSelect">
                    <?php foreach ($courses as $course): ?>
                    <option value="<?= htmlspecialchars($course['id'], ENT_QUOTES, 'UTF-8') ;?>"><?= htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="studentSelect" class="form-label">Sélectionner l'étudiant</label>
                <select class="form-select" id="studentSelect">
                    <?php foreach ($students as $student): ?>
                    <option value="<?=   htmlspecialchars($student['id'], ENT_QUOTES, 'UTF-8') ;?>"><?=  htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="gradeInput" class="form-label">Entrer la note</label>
                <input type="number" class="form-control" id="gradeInput" min="0" max="20" step="0.1">
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    </div>
</body>
</html>