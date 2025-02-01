<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>404 Not Found</title>
    <link rel="stylesheet" href="../assets/css/style.css" /> <!-- Adjust if necessary -->
    <style>
        .game-container {
            position: relative;
            width: 400px;
            height: 400px;
            margin: 40px auto 0;
            border: 2px solid #0f0;
        }
        canvas {
            position: absolute;
            top: 0;
            left: 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>404 - Page Not Found</h1>
    <p>
        The page you are looking for might have been removed, had its name changed,
        or is temporarily unavailable.
    </p>
    <a href="/" class="btn">Go to Homepage</a>

    <div class="game-container">
        <h2 style="color: #0f0;">Hack the System</h2>
        <!-- Background canvas for Matrix code rain -->
        <canvas id="matrixCanvas" width="400" height="400"></canvas>
        <!-- Foreground canvas for the game -->
        <canvas id="gameCanvas" width="400" height="400"></canvas>
        <p id="scoreText" style="color: #fff; font-size: 16px; position: absolute; top: 5px; left: 10px;">Data Collected: 0</p>
        <p style="color: #fff; font-size: 12px; position: absolute; bottom: 5px; right: 10px;">
            Use arrow keys to collect data
        </p>
    </div>
</div>

<script>
    // -------------------------
    // Matrix Background Effect
    // -------------------------
    const matrixCanvas = document.getElementById("matrixCanvas");
    const matrixCtx = matrixCanvas.getContext("2d");
    const matrixLetters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()";
    const fontSize = 16;
    const columns = matrixCanvas.width / fontSize;
    const drops = [];
    for (let i = 0; i < columns; i++) {
        drops[i] = 1;
    }

    function drawMatrix() {
        // Translucent black background to create trail effect
        matrixCtx.fillStyle = "rgba(0, 0, 0, 0.05)";
        matrixCtx.fillRect(0, 0, matrixCanvas.width, matrixCanvas.height);
        matrixCtx.fillStyle = "#0f0"; // Neon green text
        matrixCtx.font = fontSize + "px monospace";
        for (let i = 0; i < drops.length; i++) {
            const text = matrixLetters.charAt(Math.floor(Math.random() * matrixLetters.length));
            matrixCtx.fillText(text, i * fontSize, drops[i] * fontSize);
            // Reset drop position randomly after it passes the screen height
            if (drops[i] * fontSize > matrixCanvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
        requestAnimationFrame(drawMatrix);
    }
    drawMatrix();

    // -------------------------
    // Hacker-Themed Game Logic
    // -------------------------
    const gameCanvas = document.getElementById("gameCanvas");
    const gameCtx = gameCanvas.getContext("2d");
    const scoreText = document.getElementById("scoreText");
    let score = 0;

    // Define the hacker player object (neon green square with glow)
    const player = {
        x: 50,
        y: 50,
        size: 20,
        speed: 5
    };

    // Define the target "data packet" (glowing red square)
    const target = {
        x: Math.random() * (gameCanvas.width - 20),
        y: Math.random() * (gameCanvas.height - 20),
        size: 20
    };

    const keys = {};

    function updateGame() {
        // Move player with arrow keys
        if (keys["ArrowUp"] && player.y > 0) player.y -= player.speed;
        if (keys["ArrowDown"] && player.y + player.size < gameCanvas.height) player.y += player.speed;
        if (keys["ArrowLeft"] && player.x > 0) player.x -= player.speed;
        if (keys["ArrowRight"] && player.x + player.size < gameCanvas.width) player.x += player.speed;

        // Collision detection between player and target
        if (
            player.x < target.x + target.size &&
            player.x + player.size > target.x &&
            player.y < target.y + target.size &&
            player.y + player.size > target.y
        ) {
            score++;
            scoreText.textContent = "Data Collected: " + score;
            // Respawn target at a random location
            target.x = Math.random() * (gameCanvas.width - target.size);
            target.y = Math.random() * (gameCanvas.height - target.size);
        }
    }

    function drawGame() {
        // Clear only the game canvas (background remains from matrixCanvas)
        gameCtx.clearRect(0, 0, gameCanvas.width, gameCanvas.height);

        // Draw player with neon glow effect
        gameCtx.save();
        gameCtx.shadowColor = "#0f0";
        gameCtx.shadowBlur = 20;
        gameCtx.fillStyle = "#0f0";
        gameCtx.fillRect(player.x, player.y, player.size, player.size);
        gameCtx.restore();

        // Draw target "data packet" with neon red glow
        gameCtx.save();
        gameCtx.shadowColor = "#f00";
        gameCtx.shadowBlur = 20;
        gameCtx.fillStyle = "#f00";
        gameCtx.fillRect(target.x, target.y, target.size, target.size);
        gameCtx.restore();
    }

    function gameLoop() {
        updateGame();
        drawGame();
        requestAnimationFrame(gameLoop);
    }

    window.addEventListener("keydown", (e) => {
        keys[e.key] = true;
    });
    window.addEventListener("keyup", (e) => {
        keys[e.key] = false;
    });

    gameLoop();
</script>
</body>
</html>
