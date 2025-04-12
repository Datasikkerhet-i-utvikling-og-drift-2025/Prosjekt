
<?php
require_once __DIR__ . '/../partials/header.php';

$message = '';
$message_type = ''; // f.eks. 'success' eller 'error'

?>

<div class="form-container">
    <h1>Reset Your Password</h1>

    <div id="response-message" class="alert" style="display: none;"></div>

    <form id="reset-request-form" method="POST" class="form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter your email"
                autocomplete="email"
                required
            >
        </div>

        <div class="form-actions">
            <button type="submit" id="submit-button" class="btn btn-primary">Request Password Reset</button>
        </div>
    </form>

    <p>Remember your password? <a href="/">Login here</a></p>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('reset-request-form');
        const emailInput = document.getElementById('email');
        const messageDiv = document.getElementById('response-message');
        const submitButton = document.getElementById('submit-button');

        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Stopp standard skjemainnsending

                const email = emailInput.value;
                // Enkel validering på klientsiden (kan utvides)
                if (!email || !email.includes('@')) {
                    displayMessage('Please enter a valid email address.', 'error');
                    return;
                }

                // Vis "laster"-tilstand
                submitButton.disabled = true;
                submitButton.textContent = 'Sending Request...';
                messageDiv.style.display = 'none'; // Skjul gamle meldinger

                // API Endepunkt URL - ENDRE DENNE TIL DIN KORREKTE RUTE!
                const apiUrl = '/api/v1/auth/password-reset/request'; // VIKTIG: Bruk din faktiske API-rute

                fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // Send som JSON
                        'Accept': 'application/json' // Forvent JSON tilbake
                    },
                    body: JSON.stringify({ email: email }) // Send e-post i JSON-body
                })
                .then(response => {
                    // Sjekk om responsen er OK (status 2xx), men vi trenger å lese JSON uansett
                    // for å få suksess/feilmelding fra vår API-struktur.
                    return response.json(); // Prøv alltid å parse JSON
                })
                .then(data => {
                    // Vi forventer et objekt som ligner på ApiResponse
                    // f.eks. { success: true, message: "..." }
                    if (data.success) {
                        displayMessage(data.message || 'Request sent successfully. Check your email.', 'success');
                        // Valgfritt: Tøm e-postfeltet ved suksess
                        // emailInput.value = '';
                    } else {
                        // Vis feilmelding fra API-et
                        displayMessage(data.message || 'An unknown error occurred.', 'error');
                    }
                })
                .catch(error => {
                    // Håndter nettverksfeil eller JSON parse-feil
                    console.error('Error during fetch:', error);
                    // Dette inkluderer "Invalid JSON handling: Syntax error" hvis serveren
                    // sender HTML/Warning i stedet for gyldig JSON.
                    if (error instanceof SyntaxError) {
                         displayMessage('Error: Received an invalid response from the server. Please check server logs.', 'error');
                    } else {
                         displayMessage('An error occurred while connecting to the server. Please try again later.', 'error');
                    }
                })
                .finally(() => {
                    // Gjenopprett knappen uansett utfall
                    submitButton.disabled = false;
                    submitButton.textContent = 'Request Password Reset';
                });
            });
        }

        function displayMessage(message, type) {
            messageDiv.textContent = message;
            // Fjern gamle klasser, legg til nye
            messageDiv.className = 'alert'; // Reset til baseklasse
            if (type === 'success') {
                messageDiv.classList.add('alert-success'); // Legg til suksess-klasse
            } else if (type === 'error') {
                messageDiv.classList.add('alert-error'); // Legg til feil-klasse
            }
            messageDiv.style.display = 'block'; // Vis meldingsfeltet
        }
    });
</script>

</body>
</html>

<?php include __DIR__ . '/../partials/footer.php'; ?>