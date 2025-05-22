<?php
// password/forgot_password.php
require_once 'config.php';

$message = '';
$messageType = '';

if ($_POST) {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $message = 'Veuillez entrer votre adresse email.';
        $messageType = 'error';
    } else {
        // Vérifier si l'email existe dans la base de données
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Générer un code de réinitialisation
            $resetCode = generateResetCode();
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            // Supprimer les anciens tokens pour cet email
            $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE email = ?");
            $stmt->execute([$email]);
            
            // Insérer le nouveau token
            $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $resetCode, $expiresAt]);
            
            // Préparer l'email
            $subject = "Code de réinitialisation de mot de passe - NOTEaii";
            $emailMessage = "
            <html>
            <body>
                <h2>Réinitialisation de mot de passe</h2>
                <p>Bonjour,</p>
                <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                <p>Voici votre code de réinitialisation :</p>
                <h3 style='color: #ff6b35; font-size: 24px; text-align: center; background: #f4f4f4; padding: 15px; border-radius: 5px;'>$resetCode</h3>
                <p><strong>Ce code expire dans 15 minutes.</strong></p>
                <p>Si vous n'avez pas demandé cette réinitialisation, ignorez ce message.</p>
                <p>Cordialement,<br>L'équipe NOTEaii</p>
            </body>
            </html>
            ";
            
            // Envoyer l'email
            if (sendEmail($email, $subject, $emailMessage)) {
                $message = 'Un code de réinitialisation a été envoyé à votre adresse email.';
                $messageType = 'success';
                
                // Rediriger vers la page de vérification du code
                $_SESSION['reset_email'] = $email;
                header('Location: verify_code.php');
                exit;
            } else {
                $message = 'Erreur lors de l\'envoi de l\'email. Veuillez réessayer.';
                $messageType = 'error';
            }
        } else {
            $message = 'Cette adresse email n\'est pas enregistrée.';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - NOTEaii</title>
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
        
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus {
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
            <p>Récupération de mot de passe</p>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Adresse email :</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Entrez votre adresse email">
            </div>
            
            <button type="submit" class="btn">Envoyer le code</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Retour à la connexion</a>
        </div>
    </div>
</body>
</html>