<?php
// password/reset_password.php
require_once 'config.php';

// Vérifier si l'email et le code sont en session
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_code'])) {
    header('Location: forgot_password.php');
    exit;
}

$email = $_SESSION['reset_email'];
$code = $_SESSION['reset_code'];
$message = '';
$messageType = '';

if ($_POST) {
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'Veuillez remplir tous les champs.';
        $messageType = 'error';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Le mot de passe doit contenir au moins 6 caractères.';
        $messageType = 'error';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Les mots de passe ne correspondent pas.';
        $messageType = 'error';
    } else {
        // Vérifier que le token est toujours valide
        $stmt = $pdo->prepare("SELECT id FROM password_reset_tokens WHERE email = ? AND token = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$email, $code]);
        
        if ($stmt->rowCount() > 0) {
            try {
                // Commencer une transaction
                $pdo->beginTransaction();
                
                // Hacher le nouveau mot de passe
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe de l'utilisateur
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashedPassword, $email]);
                
                // Marquer le token comme utilisé
                $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE email = ? AND token = ?");
                $stmt->execute([$email, $code]);
                
                // Valider la transaction
                $pdo->commit();
                
                // Nettoyer les sessions
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_code']);
                
                $message = 'Votre mot de passe a été mis à jour avec succès !';
                $messageType = 'success';
                
                // Redirection après 3 secondes
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '../index.html';
                    }, 3000);
                </script>";
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = 'Erreur lors de la mise à jour du mot de passe.';
                $messageType = 'error';
            }
        } else {
            $message = 'Session expirée. Veuillez recommencer.';
            $messageType = 'error';
            
            // Nettoyer les sessions
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_code']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - NOTEaii</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #8B4513 0%, #4A2C17 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #8B4513;
            font-size: 28px;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="password"]:focus {
            outline: none;
            border-color: #ff6b35;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #e55a2b;
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .password-requirements ul {
            margin: 5px 0 0 20px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #8B4513;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>NOTEaii</h1>
            <p>Nouveau mot de passe</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
                <?php if ($messageType === 'success'): ?>
                    <br><small>Redirection vers la page de connexion...</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($messageType !== 'success'): ?>
        <div class="password-requirements">
            <strong>Exigences du mot de passe :</strong>
            <ul>
                <li>Au moins 6 caractères</li>
                <li>Les deux mots de passe doivent être identiques</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe :</label>
                <input type="password" id="new_password" name="new_password" required 
                       placeholder="Entrez votre nouveau mot de passe" minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe :</label>
                <input type="password" id="confirm_password" name="confirm_password" required 
                       placeholder="Confirmez votre nouveau mot de passe" minlength="6">
            </div>
            
            <button type="submit" class="btn">Mettre à jour le mot de passe</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="../index.php">← Retour à la connexion</a>
        </div>
    </div>

    <script>
        // Vérifier que les mots de passe correspondent
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>