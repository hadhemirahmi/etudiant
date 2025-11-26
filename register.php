<?php
include 'Database.php';
$pdo = connectDatabase();

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role]);
        $success = 'Inscription réussie ! Connectez-vous.';
    } catch (PDOException $e) {
        $error = 'Email déjà utilisé ou erreur.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>systeme gestion des etudiants - Register</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background: #f7faff; font-family: 'Poppins', sans-serif; }
    .register-container { max-width: 400px; margin: 80px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .register-title { font-size: 28px; font-weight: 700; color: #0d1b3e; margin-bottom: 20px; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg bg-white py-3 shadow-sm">
    <div class="container">
      <a class="navbar-brand fs-3 fw-bold" href="#">systeme gestion etudiant <span class="text-primary">.</span></a>
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
  <div class="register-container">
    <h2 class="register-title">Sign Up</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required />
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required />
      </div>
      <div class="mb-3">
        <label for="role" class="form-label fw-semibold">Rôle</label>
        <select name="role" id="role" class="form-select shadow-sm" required>
          <option value="etudiant">Étudiant</option>
          <option value="enseignant">Enseignant</option>
          <option value="admin">Administrateur</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>