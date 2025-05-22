<?php
// password/config.php
session_start();

// Configuration de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "noteai_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Configuration email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noteaicontact@gmail.com');
define('SMTP_PASSWORD', 'wdtn udrf kytu jjgl');
define('FROM_EMAIL', 'noteaicontact@gmail.com');
define('FROM_NAME', 'NOTEaii');

// Fonction pour générer un code de 6 chiffres
function generateResetCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message) {
    // Vérifier si PHPMailer existe
    $phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
    
    if (!file_exists($phpmailerPath)) {
        // Si PHPMailer n'existe pas, afficher un message d'erreur utile
        error_log("PHPMailer non trouvé à : " . $phpmailerPath);
        return false;
    }
    
    try {
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Désactiver la vérification SSL pour les tests locaux
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Destinataire et expéditeur
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $message;
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Erreur email : " . $e->getMessage());
        return false;
    }
}
?>