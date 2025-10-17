<?php
session_start();
require 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT password FROM utilisateurs WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["username"] = $username;
            header("Location: dashboard.php"); // Redirection après connexion
            exit();
        } else {
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système Pharma</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            animation: fadeIn 0.6s ease-in-out;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .message {
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-weight: 500;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        
        .message.error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .message.success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .footer-links {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
        
        .footer-links a {
            color: #7f8c8d;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-lock"></i>
            <h2 class="mb-3">Authentification</h2>
            <p class="text-muted">Veuillez vous connecter pour accéder au système</p>
        </div>
        
        <form method="POST">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required>
            </div>
            
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login mb-3">
                <i class="fas fa-sign-in-alt me-2"></i> Se connecter
            </button>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="rememberMe">
                <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
            </div>
        </form>
        
        <?php if(!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'erreur') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="footer-links">
            <a href="#"><i class="fas fa-question-circle me-1"></i> Aide</a> | 
            <a href="#"><i class="fas fa-key me-1"></i> Mot de passe oublié?</a> | 
            <a href="#"><i class="fas fa-envelope me-1"></i> Support</a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Masquer le message après 5 secondes
        setTimeout(() => {
            const message = document.querySelector('.message');
            if(message) {
                message.style.opacity = '0';
                setTimeout(() => message.style.display = 'none', 500);
            }
        }, 5000);
        
        // Basculer la visibilité du mot de passe
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');
                
                if(input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.login-container').style.animation = 'fadeIn 0.6s ease-in-out';
        });
    </script>
</body>
</html>