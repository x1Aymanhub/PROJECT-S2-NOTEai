<?php
// password/verify_code.php
require_once 'config.php';

// Vérifier si l'email est en session
if (!isset($_SESSION['reset_email'])) {
    header('Location: forgot_password.php');
    exit;
}

$email = $_SESSION['reset_email'];
$message = '';
$messageType = '';

if ($_POST) {
    $code = trim($_POST['code']);
    
    if (empty($code)) {
        $message = 'Veuillez entrer le code de vérification.';
        $messageType = 'error';
    } else {
        // Vérifier le code
        $stmt = $pdo->prepare("SELECT id FROM password_reset_tokens WHERE email = ? AND token = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$email, $code]);
        
        if ($stmt->rowCount() > 0) {
            // Code valide, rediriger vers la page de nouveau mot de passe
            $_SESSION['reset_code'] = $code;
            header('Location: reset_password.php');
            exit;
        } else {
            $message = 'Code incorrect ou expiré.';
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
    <title>Vérification du code - NOTEaii</title>
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
        
        input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            letter-spacing: 2px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
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
        
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            color: #0066cc;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
            <p>Vérification du code</p>
        </div>
        
        <div class="info">
            Un code de vérification a été envoyé à :<br>
            <strong><?= htmlspecialchars($email) ?></strong>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="code">Code de vérification (6 chiffres) :</label>
                <input type="text" id="code" name="code" required 
                       placeholder="000000" maxlength="6" pattern="[0-9]{6}">
            </div>
            
            <button type="submit" class="btn">Vérifier le code</button>
        </form>
        
        <div class="back-link">
            <a href="forgot_password.php">← Renvoyer un code</a>
        </div>
    </div>

    <script>
        // Permettre seulement les chiffres
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>