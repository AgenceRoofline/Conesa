<?php
declare(strict_types=1);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// ── Autoloader Composer + variables d'environnement ──────────────────────────
// En dev  : dirname(__DIR__) = racine du projet (où se trouve .env et storage/)
// En prod : dirname(__DIR__) = dossier parent du web root (hors accès public)
$root = dirname(__DIR__);
require "$root/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

$smtpHost = (string)($_ENV['SMTP_HOST'] ?? '');
$smtpPort = (int)($_ENV['SMTP_PORT']    ?? 587);
$smtpUser = (string)($_ENV['SMTP_USER'] ?? '');
$smtpPass = (string)($_ENV['SMTP_PASS'] ?? '');
$mailFrom = (string)($_ENV['MAIL_FROM'] ?? '');
$mailTo   = (string)($_ENV['MAIL_TO']   ?? '');

// ── Méthode POST uniquement ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// ── 1. Rate limiting : max 5 envois par heure par session ────────────────────
$now = time();
if (!isset($_SESSION['cf_attempts'])) {
    $_SESSION['cf_attempts'] = [];
}
$_SESSION['cf_attempts'] = array_values(array_filter(
    $_SESSION['cf_attempts'],
    fn(int $t): bool => $now - $t < 3600
));
if (count($_SESSION['cf_attempts']) >= 5) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'message' => 'Trop de tentatives. Merci de réessayer dans une heure ou de nous appeler au 05 63 54 16 97.',
    ]);
    exit;
}

// ── 2. CSRF — Double Submit Cookie ───────────────────────────────────────────
$csrf_post   = $_POST['csrf_token'] ?? '';
$csrf_cookie = $_COOKIE['cf_token'] ?? '';

if (
    empty($csrf_post) ||
    empty($csrf_cookie) ||
    !hash_equals($csrf_cookie, $csrf_post)
) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Token de sécurité invalide. Merci de recharger la page.',
    ]);
    exit;
}

// ── 3. Honeypot anti-spam ────────────────────────────────────────────────────
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

// Enregistrer la tentative (après CSRF et honeypot)
$_SESSION['cf_attempts'][] = $now;

// ── 4. Sanitisation ──────────────────────────────────────────────────────────
$nom        = mb_substr(trim(strip_tags($_POST['nom']        ?? '')), 0, 100);
$telephone  = mb_substr(trim(strip_tags($_POST['telephone']  ?? '')), 0, 20);
$email      = mb_substr(trim(strip_tags($_POST['email']      ?? '')), 0, 254);
$ville      = mb_substr(trim(strip_tags($_POST['ville']      ?? '')), 0, 100);
$prestation = mb_substr(trim(strip_tags($_POST['prestation'] ?? '')), 0, 100);
$message    = mb_substr(trim(strip_tags($_POST['message']    ?? '')), 0, 2000);

$prestations_valides = [
    'Isolation Thermique Extérieure (ITE)',
    'Ravalement de façade',
    'Peinture intérieure',
    'Peinture extérieure',
    'Entretien de toiture',
    'Revêtements de sols',
    'Travaux après sinistre',
    'Autre / Je ne sais pas encore',
];

// ── 5. Validation serveur ────────────────────────────────────────────────────
$errors = [];

if (empty($nom) || !preg_match('/^[\p{L}\s\'\'\-\.]{2,100}$/u', $nom)) {
    $errors['nom'] = "Merci d'indiquer votre nom (2 à 100 caractères).";
}

$tel_clean = preg_replace('/[\s\.\-\(\)]/', '', $telephone);
if (empty($tel_clean) || !preg_match('/^(0|\+33)[1-9][0-9]{8}$/', $tel_clean)) {
    $errors['telephone'] = 'Numéro invalide (ex : 06 12 34 56 78).';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Adresse e-mail invalide.';
}

if (!empty($prestation) && !in_array($prestation, $prestations_valides, true)) {
    $errors['prestation'] = 'Valeur de prestation invalide.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// ── 6. SQLite — initialisation et insertion du lead ──────────────────────────
$lead_id = 0;
$pdo     = null;

// Hash SHA-256 de l'IP — jamais l'IP brute stockée ni loguée
$ipHash = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');

// Page source issue du champ caché rempli côté JS (window.location.pathname)
$pageSource = mb_substr(strip_tags($_POST['page_source'] ?? ''), 0, 255);

try {
    $storageDir = "$root/storage";
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0750, true);
    }

    $pdo = new PDO("sqlite:$storageDir/leads.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Création de la table avec toutes les colonnes pour les nouvelles installations
    $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        created_at  TEXT    NOT NULL,
        name        TEXT    NOT NULL,
        email       TEXT    NOT NULL,
        phone       TEXT    NOT NULL,
        city        TEXT    NOT NULL DEFAULT '',
        service     TEXT    NOT NULL DEFAULT '',
        message     TEXT    NOT NULL DEFAULT '',
        page_source TEXT    NOT NULL DEFAULT '',
        status      TEXT    NOT NULL DEFAULT 'pending',
        ip_hash     TEXT    NOT NULL DEFAULT ''
    )");

    // Migration automatique — ajoute les colonnes manquantes sur les bases existantes
    $existingCols = array_column(
        $pdo->query("PRAGMA table_info(leads)")->fetchAll(),
        'name'
    );
    foreach (['ip_hash', 'page_source'] as $col) {
        if (!in_array($col, $existingCols, true)) {
            $pdo->exec("ALTER TABLE leads ADD COLUMN {$col} TEXT NOT NULL DEFAULT ''");
        }
    }

    $stmt = $pdo->prepare(
        "INSERT INTO leads
            (created_at, name, email, phone, city, service, message, page_source, status, ip_hash)
         VALUES
            (datetime('now','localtime'), :name, :email, :phone, :city, :service, :message, :page_source, 'pending', :ip_hash)"
    );
    $stmt->execute([
        ':name'        => $nom,
        ':email'       => $email,
        ':phone'       => $telephone,
        ':city'        => $ville,
        ':service'     => $prestation,
        ':message'     => $message,
        ':page_source' => $pageSource,
        ':ip_hash'     => $ipHash,
    ]);
    $lead_id = (int)$pdo->lastInsertId();

} catch (\Throwable $e) {
    // L'échec SQLite ne bloque pas l'envoi du mail
    error_log("[Conesa leads] SQLite error: {$e->getMessage()}");
}

// ── 7. Construction du mail HTML ─────────────────────────────────────────────
function ligne(string $label, string $valeur): string {
    return "
    <tr>
      <td style='padding:10px 16px;background:#f8faff;border-bottom:1px solid #e8edf5;width:38%;'>
        <span style='font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;font-family:Arial,sans-serif;'>{$label}</span>
      </td>
      <td style='padding:10px 16px;background:#ffffff;border-bottom:1px solid #e8edf5;'>
        <span style='font-size:14px;color:#1f2937;font-family:Arial,sans-serif;'>" . nl2br(htmlspecialchars($valeur)) . "</span>
      </td>
    </tr>";
}

$lignes  = ligne('Nom', $nom);
$lignes .= ligne('Téléphone', $telephone);
$lignes .= ligne('Email', $email);
if (!empty($ville))      $lignes .= ligne('Ville', $ville);
if (!empty($prestation)) $lignes .= ligne('Prestation', $prestation);
if (!empty($message))    $lignes .= ligne('Message', $message);

$html = "<!DOCTYPE html>
<html lang='fr'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#f0f5fc;font-family:Arial,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f5fc;padding:32px 16px;'>
  <tr><td align='center'>
    <table width='100%' style='max-width:580px;' cellpadding='0' cellspacing='0'>

      <!-- En-tête -->
      <tr>
        <td style='background:#1E4B8C;border-radius:12px 12px 0 0;padding:28px 32px;'>
          <table width='100%' cellpadding='0' cellspacing='0'>
            <tr>
              <td>
                <span style='font-size:22px;font-weight:800;color:#ffffff;font-family:Arial,sans-serif;letter-spacing:-.02em;'>Conesa</span>
                <span style='font-size:13px;color:#93c5fd;font-family:Arial,sans-serif;margin-left:6px;'>Rénovation</span>
              </td>
              <td align='right'>
                <span style='background:#E8650A;color:#ffffff;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;font-family:Arial,sans-serif;text-transform:uppercase;letter-spacing:.05em;'>Nouvelle demande</span>
              </td>
            </tr>
          </table>
        </td>
      </tr>

      <!-- Intro -->
      <tr>
        <td style='background:#153566;padding:16px 32px;'>
          <p style='margin:0;font-size:14px;color:#93c5fd;font-family:Arial,sans-serif;'>Un particulier vient de remplir le formulaire de contact sur votre site.</p>
        </td>
      </tr>

      <!-- Tableau des champs -->
      <tr>
        <td style='background:#ffffff;border-radius:0 0 12px 12px;overflow:hidden;'>
          <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
            {$lignes}
          </table>
        </td>
      </tr>

      <!-- CTA répondre -->
      <tr>
        <td style='padding:24px 0 8px;text-align:center;'>
          <a href='mailto:{$email}' style='display:inline-block;background:#E8650A;color:#ffffff;font-size:14px;font-weight:700;padding:12px 28px;border-radius:8px;text-decoration:none;font-family:Arial,sans-serif;'>
            Répondre à {$nom}
          </a>
        </td>
      </tr>

      <!-- Pied de page -->
      <tr>
        <td style='padding:16px 0 0;text-align:center;border-top:1px solid #e5e7eb;margin-top:8px;'>
          <p style='margin:0;font-size:11px;color:#9ca3af;font-family:Arial,sans-serif;'>
            Conesa Rénovation · Albi, Tarn (81) · 05 63 54 16 97
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>";

// Version texte brut (fallback)
$texte  = "Nouvelle demande de contact — Conesa\n\n";
$texte .= "Nom        : {$nom}\n";
$texte .= "Téléphone  : {$telephone}\n";
$texte .= "Email      : {$email}\n";
if (!empty($ville))      $texte .= "Ville      : {$ville}\n";
if (!empty($prestation)) $texte .= "Prestation : {$prestation}\n";
if (!empty($message))    $texte .= "\nMessage :\n{$message}\n";

// ── 8. Envoi PHPMailer SMTP ──────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = $smtpPort === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtpPort;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;

    $mail->setFrom($mailFrom, 'Conesa Rénovation');
    $mail->addAddress($mailTo);
    $mail->addReplyTo($email, $nom);

    $mail->Subject  = "Nouvelle demande de contact — Conesa";
    $mail->isHTML(true);
    $mail->Body     = $html;
    $mail->AltBody  = $texte;

    $mail->send();

    // Mail envoyé — mise à jour du statut SQLite
    if ($pdo !== null && $lead_id > 0) {
        $pdo->prepare("UPDATE leads SET status = 'sent' WHERE id = :id")
            ->execute([':id' => $lead_id]);
    }

    echo json_encode(['success' => true]);

} catch (MailerException $e) {
    error_log("[Conesa contact] SMTP error: {$mail->ErrorInfo}");

    // Mail échoué — mise à jour du statut SQLite
    if ($pdo !== null && $lead_id > 0) {
        $pdo->prepare("UPDATE leads SET status = 'failed' WHERE id = :id")
            ->execute([':id' => $lead_id]);
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur lors de l'envoi. Merci de nous appeler directement au 05 63 54 16 97.",
    ]);
}
