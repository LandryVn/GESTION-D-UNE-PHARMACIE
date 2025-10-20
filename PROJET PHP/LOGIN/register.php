<?php
session_start();
require 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = :username");
        $stmt->execute(['username' => $username]);

        if ($stmt->fetch()) {
            $message = "Ce nom d'utilisateur est déjà pris.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password) VALUES (:username, :password)");

            if ($stmt->execute(['username' => $username, 'password' => $hashed_password])) {
                header("Location: login.php"); 
                exit();
            } else {
                $message = "Erreur lors de l'inscription.";
            }
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
    <title>Inscription - Système Pharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            animation: fadeIn 0.6s ease-in-out;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header i {
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
        
        .btn-register {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
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
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        .password-strength {
            height: 5px;
            background: #eee;
            border-radius: 5px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: red;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2 class="mb-3">Créer un compte</h2>
            <p class="text-muted">Rejoignez notre système de gestion pharmaceutique</p>
        </div>
        
        <form method="POST">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required>
            </div>
            
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control" placeholder="Mot de passe" required>
                <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            
            
            
            <button type="submit" class="btn btn-primary btn-register mb-3">
                <i class="fas fa-user-plus me-2"></i> S'inscrire
            </button>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms">J'accepte les <a href="#">conditions d'utilisation</a></label>
            </div>
        </form>
        
        <?php if(!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'erreur') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="footer-links">
            <p>Vous avez déjà un compte? <a href="login.php"><i class="fas fa-sign-in-alt me-1"></i>Se connecter</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        setTimeout(() => {
            const message = document.querySelector('.message');
            if(message) {
                message.style.opacity = '0';
                setTimeout(() => message.style.display = 'none', 500);
            }
        }, 5000);
        
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
        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const bar = document.getElementById('password-strength-bar');
            const text = document.getElementById('password-strength-text');
            
            let strength = 0;
            if (password.length > 0) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const colors = ['red', 'orange', 'yellow', 'lightgreen', 'green'];
            const texts = ['Très faible', 'Faible', 'Moyen', 'Fort', 'Très fort'];
            
            bar.style.width = (strength * 20) + '%';
            bar.style.background = colors[strength - 1] || 'red';
            text.textContent = 'Force du mot de passe: ' + (texts[strength - 1] || 'Très faible');
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('.register-container').style.animation = 'fadeIn 0.6s ease-in-out';
        });
    </script>
</body>
</html>
