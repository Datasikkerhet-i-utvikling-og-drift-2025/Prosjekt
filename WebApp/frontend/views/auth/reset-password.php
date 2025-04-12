
<?php
require_once __DIR__ . '/../partials/header.php';
// Start session for å håndtere meldinger
//if (session_status() === PHP_SESSION_NONE) {
//    session_start();
//}
use managers\ApiManager;

define('API_PASSWORD_RESET_REQUEST_ENDPOINT', '/api/v1/auth/password-reset/request'); // Endpoint for å be om tilbakestilling
define('API_PASSWORD_RESET_ENDPOINT', '/api/v1/auth/password-reset');
// Initialiser ApiManager
    $apiManager = null; // Initialiser til null
    try {
    $apiManager = new ApiManager();
    } catch (Throwable $e) {
    // Lagre feil i session for visning på siden
    $_SESSION['errors'] = 'Systemfeil: Kunne ikke initialisere API-tilkobling. ' . $e->getMessage();
    // Ikke fortsett hvis ApiManager feiler
    }

    // Håndter POST-forespørsler hvis ApiManager er tilgjengelig
    if ($apiManager && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    // Sjekk om det er en forespørsel om tilbakestilling (Steg 1) eller selve tilbakestillingen (Steg 2)
    if (isset($_POST['email']) && !isset($_POST['token'])) {
    // *** STEG 1: Be om passordtilbakestilling ***

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['errors'] = 'Ugyldig e-postformat.';
    } else {
    $responseData = $apiManager->post(API_PASSWORD_RESET_REQUEST_ENDPOINT, ['email' => $email]);

    if ($responseData['success'] === true) {
    // API-et bør returnere en suksessmelding
    $_SESSION['success'] = $responseData['data']['message'] ?? 'Instruksjoner for tilbakestilling av passord er sendt til din e-post (hvis kontoen eksisterer).';
    // IKKE redirect her. Brukeren skal se meldingen på samme side.
    } else {
    // Hent feilmelding fra API-responsen hvis tilgjengelig
    $apiErrors = isset($responseData['errors']) ? implode(', ', (array)$responseData['errors']) : 'Ukjent feil fra API.';
    $_SESSION['errors'] = 'Kunne ikke be om tilbakestilling av passord: ' . $apiErrors;
    }
    }

    } elseif (isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
    // *** STEG 2: Utfør passordtilbakestilling ***

    $token = trim($_POST['token']);
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Enkel validering på serversiden (API-et bør også validere)
    if (empty($token)) {
    $_SESSION['errors'] = 'Token mangler.';
    } elseif (empty($newPassword)) {
    $_SESSION['errors'] = 'Nytt passord kan ikke være tomt.';
    } elseif ($newPassword !== $confirmPassword) {
    $_SESSION['errors'] = 'Passordene stemmer ikke overens.';
    } else {
    // Forbered data for API-kallet
    $data = [
    'token' => $token,
    'new_password' => $newPassword,
    // Du trenger vanligvis ikke sende confirm_password til API-et
    // 'confirm_password' => $confirmPassword // Send kun hvis API-et krever det
    ];

    $responseData = $apiManager->post(API_PASSWORD_RESET_ENDPOINT, $data);

    if ($responseData['success'] === true) {
    $_SESSION['success'] = $responseData['data']['message'] ?? 'Passordet ditt er nå tilbakestilt. Du kan logge inn.';
    // Omdiriger til innloggingssiden etter vellykket tilbakestilling
    header('Location: /'); // Tilpass URL til din innloggingsside
    exit;
    } else {
    // Hent feilmelding fra API (f.eks. "ugyldig token", "utløpt token", "passord oppfyller ikke krav")
    $apiErrors = isset($responseData['errors']) ? implode(', ', (array)$responseData['errors']) : 'Ukjent feil fra API.';
    $_SESSION['errors'] = 'Kunne ikke tilbakestille passord: ' . $apiErrors;
    // Ikke redirect, vis feilmeldingen på reset-siden med token
    // Legg token tilbake i URL-en hvis du vil at brukeren skal kunne prøve igjen uten å klikke på lenken på nytt
    // (Vurder sikkerhetsimplikasjonene av dette)
    // header('Location: ?token=' . urlencode($token)); // Eventuelt
    // exit;
    }
    }
    } else {
    // Håndterer tilfeller der POST-data er ufullstendige eller uventede
    $_SESSION['errors'] = 'Ugyldig forespørsel.';
    }

    } catch (Throwable $e) {
    // Generell feilhåndtering for uventede feil under API-kommunikasjon eller logikk
    $_SESSION['errors'] = 'En uventet feil oppstod: ' . $e->getMessage();
    }
}

?>

<div class="form-container">
    <h1>Reset Your Password</h1>

    <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert alert-error">
            <?= nl2br(htmlspecialchars((is_array($_SESSION['errors']) ? implode("\n", $_SESSION['errors']) : $_SESSION['errors']), ENT_QUOTES, 'UTF-8')) ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php
    // Hent token fra GET-parameteren for å vise riktig skjema
    $tokenFromUrl = isset($_GET['token']) ? trim($_GET['token']) : null;
    ?>

    <?php if (empty($tokenFromUrl)): ?>
        <form action="" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Request Password Reset</button>
            </div>
        </form>
    <?php else: ?>
        <form action="" method="POST" class="form">
            <input
                    type="hidden"
                    name="token"
                    value="<?= htmlspecialchars($tokenFromUrl, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="Enter your new password"
                        autocomplete="new-password"
                        required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm your new password"
                        autocomplete="new-password"
                        required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </div>
        </form>
    <?php endif; ?>

    <p>Remember your password? <a href="/">Login here</a></p> </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Din JavaScript for passordvalidering kan beholdes her.
        // Den gir rask feedback til brukeren, men husk at server-side validering er viktigst.
        const form = document.querySelector('form[action=""][method="POST"]'); // Mer spesifikk selektor
        if (form && form.querySelector('#new_password')) { // Sjekk om det er reset-skjemaet
            form.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');

                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault(); // Stopp innsending
                    // Vis en feilmelding for brukeren (du kan legge til et eget div for dette)
                    alert('Passwords do not match.'); // Enkel alert, forbedre gjerne
                    // Kanskje sette fokus på confirmPassword-feltet
                    confirmPassword.focus();
                    return false; // Stopp videre behandling
                }
                // Legg gjerne til flere klient-side sjekker (f.eks. passordlengde)
            });
        }
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; // Inkluder footer ?>