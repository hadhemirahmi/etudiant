<?php
session_start();
include 'Database.php';
$pdo = connectDatabase();

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] == 'admin') {
            header('Location: /etudiant/admin/indexadmin.php');
        } elseif ($user['role'] == 'enseignant') {
            header('Location: /etudiant/enseignant/indexenseignant.php');
        } elseif ($user['role'] == 'etudiant') {
            header('Location: /etudiant/etudiant/indexetudiant.php');
        }
        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>systeme gestion des etudiants - Login</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background: #f7faff; font-family: 'Poppins', sans-serif; }
    .login-container { max-width: 400px; margin: 80px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
    .login-title { font-size: 28px; font-weight: 700; color: #0d1b3e; margin-bottom: 20px; }
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
  <div class="login-container">
    <h2 class="login-title">Sign In</h2>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>