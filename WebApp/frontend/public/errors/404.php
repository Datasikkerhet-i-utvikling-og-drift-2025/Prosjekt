<?php
//require_once __DIR__ . '/../../src/config/versionURL.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 - Page not found</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://img.icons8.com/color/96/anonymous-mask.png" type="image/x-icon">
    <style>
        /* -------- FIXED SIZE TERMINAL WITH CRT STYLE -------- */
        html, body {
            margin: 0;
            padding: 0;
            background: #000;
            color: #0f0;
            font-family: "Courier New", Courier, monospace;
            height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Terminal window with fixed size and rounded CRT corners */
        #terminal-container {
            position: relative;
            width: 800px;  /* Fixed width */
            min-height: 500px; /* Fixed height */
            background: rgba(0, 0, 0, 0.9);
            padding: 1rem;
            box-sizing: border-box;
            border: 2px solid #0f0;
            box-shadow: 0 0 15px #0f0;
            text-shadow: 0 0 5px #0f0, 0 0 10px rgba(0, 255, 0, 0.8);
            transform: perspective(600px) rotateX(3deg);
            overflow: hidden;

            /* Makes it look like an old CRT screen */
            border-radius: 35px;
        }

        /* Subtle glass reflection for CRT effect */
        #terminal-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 35px;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.05) 20%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }

        /* CRT Scanline Effect */
        #terminal-container::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 35px;
            background: repeating-linear-gradient(
                    rgba(0, 255, 0, 0.1) 0px,
                    rgba(0, 255, 0, 0.05) 2px,
                    transparent 4px
            );
            pointer-events: none;
            animation: scanlines 1s linear infinite;
        }

        /* CRT flicker effect */
        #terminal-flicker {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 35px;
            background: rgba(0, 255, 0, 0.05);
            opacity: 0.02;
            pointer-events: none;
            animation: flicker 0.1s infinite;
        }

        /* Ensures text stays within the terminal bounds */
        #hacking-symbols, #feedback {
            overflow: hidden;
            white-space: pre-wrap;
        }

        /* Text formatting */
        .pointer, .symbol, .word, .bracketroot {
            color: #0f0;
            text-shadow: 0 0 5px #0f0, 0 0 10px rgba(0, 255, 0, 0.8);
        }

        /* Cursor effect */
        .cursor-on::after {
            content: "█";
        }

        .cursor-off::after {
            content: "█";
            color: transparent;
        }

        .cursor-flash::after {
            content: "█";
            animation: blink 1.2s infinite;
        }

        /* Anims */
        @keyframes scanlines {
            from { transform: translateY(0); }
            to { transform: translateY(-4px); }
        }

        @keyframes flicker {
            0%   { opacity: 0.03; }
            50%  { opacity: 0.06; }
            100% { opacity: 0.02; }
        }

        @keyframes blink {
            from { opacity: 1; }
            to   { opacity: 0; }
        }

        /* Slik at alt ser ut som en terminal: monospaced, hvit bakgrunn for debug = off */
        #hacking {
            display: flex;
            flex-direction: column;
            align-items: center;  /* sentrer radene horisontalt */
            margin-top: 1rem;
        }
        #hacking-symbols {
            margin-top: 1rem;
        }

        /* Hver rad vises som en grid med faste spalter,
           slik at pointer- og symbolspalter alltid stiller opp pent. */
        .terminal-row {
            display: grid;
            grid-template-columns:
          8ch  /* venstre pointer */
          1ch  /* spacing */
          repeat(12, 1ch)  /* venstre 12 symboler */
          2ch  /* spacing */
          8ch  /* høyre pointer */
          1ch  /* spacing */
          repeat(12, 1ch) /* høyre 12 symboler */
          auto; /* feedback-spalte til slutt (kan utvide) */
            column-gap: 0;  /* Du kan justere for ekstra spacing mellom spalter */
            white-space: pre; /* sørger for monospaced */
        }

        /* Korte definisjoner for klikkbare <span> */
        .pointer {
            cursor: default;
            text-align: left;
        }
        .symbol, .word, .bracketroot {
            cursor: pointer;
            text-align: left;
        }
        .symbol {
            color: #0f0;
        }
        .word {
            color: #0f0;
        }
        .bracketroot {
            color: #0f0;
        }
        .highlight {
            outline: 1px solid #0f0;
            outline-offset: -1px;
        }

        /* Forsøk-blink ved lav attempts */
        .blinker {
            animation: blink 1s infinite alternate;
        }
        @keyframes blink {
            from { opacity: 1; }
            to   { opacity: 0; }
        }

        /* "Kommando-linje" med blinking block-char (█) */
        .cursor-on::after {
            content: "█";
        }
        .cursor-off::after {
            content: "█";
            color: transparent;
        }
        .cursor-flash::after {
            content: "█";
            animation: blink 1.2s infinite;
        }

        #feedback {
            margin-top: 1rem;
            min-height: 3rem;
            text-align: left;
            width: 100%;
            white-space: pre-wrap;
        }

        #fallback-404 {
            margin-top: 2rem;
        }
        .hidden { display: none; }

        #always-visible-404 {
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            width: 100%;
        }

        /* Styling for links */
        a {
            color: #3f3; /* A slightly lighter green */
            text-decoration: underline; /* Remove underline for a cleaner look */
            font-weight: bold;
        }

        /* Hover effect for a cool retro glow */
        a:hover {
            color: #5f5; /* Even lighter green when hovered */
            text-shadow: 0 0 5px #5f5, 0 0 10px #5f5;
            text-decoration: underline;
        }

        /* Ensure the "return to homepage" text is properly styled */
        #home-link {
            color: #3f3; /* Keep it distinct from standard text */
            text-shadow: 0 0 5px #3f3, 0 0 10px rgba(0, 255, 0, 0.8);
            font-weight: bold;
        }

    </style>
</head>
<body>
<!-- Alltid synlig 404-melding -->
<div id="always-visible-404" style="text-align: center; padding: 1rem;">
    <h1>404 - Page Not Found</h1>
    <p>If you get stuck, <a href="/">return to homepage</a></p>
</div>


<div id="terminal-container">

    <!-- Klikk for å starte -->
    <div id="click-to-start" style="cursor:pointer; text-align:center;">
        <p>Click anywhere or press enter to start the hacking mini-game!</p>
    </div>

    <!-- Litt "oppstart" - CLI-lignende -->
    <div id="loading" class="hidden">
        <div id="loading-lines"></div>
    </div>

    <!-- Selve hacking-spillet -->
    <div id="hacking" class="hidden">
        <div id="termlink"></div>
        <div id="message"></div>
        <div>
            <span id="attemptstext"></span>
            <span id="attemptsblocks"></span>
            <span id="attemptsnewline"></span>
        </div>

        <!-- Alle radene med pointer + symboler, i 17 rader. -->
        <div id="hacking-symbols"></div>

        <!-- Linje for feedback, f.eks. klikket ord + "Entry Denied" -->
        <div id="feedback"></div>

        <!-- "Kommando-linjen" nederst i minispillet -->
        <div>
            <span>></span>
            <span id="entry"></span><span id="hack-cursor2" class="cursor-off"></span>
        </div>
    </div>

    <!-- Ved suksess: "innlogget" visning. Her en dummy-liste/tekst. -->
    <div id="loggedin" class="hidden">
        <h2>RobCo Terminal Access Granted</h2>
        <p>Date: <span id="todays-date"></span></p>
        <p>Time to head home to actually try to hack this website, <a href="/">return to homepage</a></p>
    </div>

</div>


<script>
    // ==========================
    // GLOBALE VARIABLER
    // ==========================
    var clickedBegin = false;
    var minigameSetupBegun = false;
    var minigameStarted = false;
    var finishedLoading = false;
    var enableMinigame = false;
    var terminalLocked = false;

    // For hacking-lignende symboler/ord
    var symbolSpansLeft = [];
    var symbolSpansRight = [];
    var pointerSpansLeft = [];
    var pointerSpansRight = [];
    var allWords = [];
    var words = [];
    var goalWord = "";
    var wordlength = 0;
    var attempts = 4;
    var hadRefresh = false;
    var bracketCount = 0;

    // Markør for “tastaturnavigering”
    var hackingCursorX = null;
    var hackingCursorY = null;

    // Tekst for “oppstart”
    var commandPromptText = [
        { text: "WELCOME TO ROBCO INDUSTRIES (TM) TERMLINK", isMachine:true, delay: 50},
        { text: ">SET TERMINAL/INQUIRE", isHuman:true, delay: 150},
        { text: "RX-9000", isMachine:true, delay: 50},
        { text: ">SET FILE/PROTECTION=OWNER:RWED ACCOUNTS.F", isHuman:true, delay: 150},
        { text: ">SET HALT RESTART/MAINT", isHuman:true, delay: 150},
        { text: "Initializing RobCo Industries(TM) MF Boot Agent v2.3.0", isMachine:true, delay: 50},
        { text: "RETROS BIOS", isMachine:true, delay: 50},
        { text: "RBIOS-4.02.08.00 52EE5.E7.E8", isMachine:true, delay: 50},
        { text: "Copyright 2075-2077 RobCo Ind.", isMachine:true, delay: 50},
        { text: "Uppermem: 1024 KB", isMachine:true, delay: 50},
        { text: "Root (5A8)", isMachine:true, delay: 50},
        { text: "Maintenance Mode", isMachine:true, delay: 50},
        { text: ">RUN DEBUG/ACCOUNTS.F", isHuman:true, delay: 200}
    ];

    // Tekst for minigame-intro
    var minigameText = [
        { text: "ROBCO INDUSTRIES (TM) TERMLINK PROTOCOL", isMachine:true, delay: 40},
        { text: "ENTER PASSWORD NOW", isMachine:true, delay: 40},
        { text: "", isBreak:true, delay: 100 },
        { text: "4 ATTEMPT(S) LEFT:", isMachine:true, delay: 40},
        { text: " ██ ██ ██ ██", isMachine:true, delay: 0 }
    ];

    // ==========================
    // FUNKSJONER
    // ==========================

    // For “skrivende” effekt
    function printLines(targetId, messages, cb) {
        let i = 0;
        let target = document.getElementById(targetId);
        target.textContent = ""; // Tøm

        function doLine() {
            if (i >= messages.length) {
                cb && cb();
                return;
            }
            let msg = messages[i];
            i++;
            let delay = msg.delay || 50;
            if (msg.isBreak) {
                target.textContent += "\n";
                setTimeout(doLine, delay);
                return;
            }
            // “skrive ut” linjen sakte
            let str = msg.text;
            let idx = 0;
            function typeChar() {
                if (idx < str.length) {
                    target.textContent += str.charAt(idx);
                    idx++;
                    // Litt variasjon for isHuman
                    let varDelay = msg.isHuman ? (50 + Math.random()*100) : (20 + Math.random()*30);
                    setTimeout(typeChar, varDelay);
                } else {
                    target.textContent += "\n";
                    setTimeout(doLine, delay);
                }
            }
            typeChar();
        }
        doLine();
    }

    // Genererer tall i hex (0xF4F0)
    function toHex4(num) {
        let hex = num.toString(16).toUpperCase();
        while (hex.length < 4) hex = "0" + hex;
        return "0x" + hex;
    }

    // Returnerer en int i [min, max]
    function randInt(min, max) {
        return Math.floor(Math.random()*(max-min+1)) + min;
    }

    // Start prosessen
    function turnOn() {
        clickedBegin = true;
        document.removeEventListener("click", turnOnClick);
        document.getElementById("click-to-start").classList.add("hidden");
        document.getElementById("loading").classList.remove("hidden");

        // Kjører “oppstart”-tekst
        printLines("loading-lines", commandPromptText, function(){
            // Gå videre
            setTimeout(beginMinigame, 500);
        });
    }

    // Går til minigame
    function beginMinigame() {
        if (minigameSetupBegun) return;
        minigameSetupBegun = true;
        document.getElementById("loading").classList.add("hidden");
        document.getElementById("hacking").classList.remove("hidden");

        // Skriv minigame-intro
        printLines("termlink", minigameText.slice(0,1), function(){
            printLines("message", minigameText.slice(1,2), function(){
                // 1 blank linje
                printLines("message", [ {text:"", isBreak:true} ], function(){
                    printLines("attemptstext", minigameText.slice(3,4), function(){
                        printLines("attemptsblocks", minigameText.slice(4,5), function(){
                            // deretter genererer vi symbolene
                            createHackingRows();
                        });
                    });
                });
            });
        });
    }

    // Henter ord-liste (API) eller fallback
    function preloadWords() {
        let url = "https://jetroid-hacking.herokuapp.com/";
        let xhr = new XMLHttpRequest();
        xhr.open("GET", url, true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState===4){
                if (xhr.status===200){
                    let data = JSON.parse(xhr.responseText);
                    goalWord = data.goal;
                    allWords = data.words;
                    wordlength = data.length;
                    finishPreload();
                } else {
                    fallbackWords();
                }
            }
        };
        xhr.send();
    }

    function fallbackWords() {
        // Hardcoded seven-letter words
        let allAvailableWords = [
            "CONFIRM","ROAMING","FARMING","GAINING","HEARING","MANKIND",
            "MORNING","HEALING","CONSIST","JESSICA","STERILE","GETTING",
            "TACTICS","ENGLISH","PACKING","FENCING","KEDRICK","EAGERLY",
            "GLANCED","SCIURID","MENTHOL","GONIDIC","BEAMISH","RITUALS",
            "SATIETY","LONGERS","FISSATE","LIMEADE","SINKERS","ODDNESS",
            "LIFTERS","TORRENT","REQUEST","JASPERY","ARMLESS","GOOIEST",
            "LAMBERT","AIRPOST","WIMPISH","WINIEST","WILDEST","DIMNESS",
            "LIMIEST","DAMPEST","DIMMEST","JIVIEST","RIMIEST","LIMPEST",
            "OVERAPT","SWINGBY","ALODIUM","PESSARY","DEVELOP","LIGASES",
            "PANTIES","ANTIRED","CUTLINE","CIRROSE","MODULUS","FITTEST",
            "MUKLUKS","ANNATES","ESCUDOS","SELLERS","INCROSS","VOTRESS",
            "ELMIEST","ENGRAMS","MADNESS","WARLESS","RIDLEYS","BIBLESS",
            "WINLESS","GODLESS","EYELESS","ENDLEAF","INKLESS","EGGLESS",
            "TROPHIC","OVERSUP","RIFLERY","RESELLS","NIRVANA","THERMIC",
            "POCOSON","SHEATHS","PREPAVE","DAVENED","ALPACAS","PALIEST",
            "DAZZLER","PLEURAL","ECTASES","ZANANAS","PALATES","PATTERS",
            "WATAPES","AUTOMAT","SAGAMAN","PYJAMAS","VITAMER","PATAGIA",
            "PACKMAN","PAJAMAS","PANAMAS","PATACAS","CALAMAR","JACAMAR"
        ];

        // Shuffle the array to randomize selection
        let shuffledWords = allAvailableWords.sort(() => Math.random() - 0.5);

        // Select the first 17 words
        allWords = shuffledWords.slice(0, 17);

        // Pick a random word from the selected ones as the goal word
        goalWord = allWords[Math.floor(Math.random() * allWords.length)];

        wordlength = 7;
        finishPreload();
    }

    // Når vi har ordlisten klar
    function finishPreload(){
        if (finishedLoading) return;
        finishedLoading = true;

        // Opprett pointer-lister
        let val = randInt(0, 65000);
        for (let i=0; i<17; i++){
            pointerSpansLeft.push( toHex4(val) );
            val += 12;
        }
        val += 192; // hopp litt
        for (let i=0; i<17; i++){
            pointerSpansRight.push( toHex4(val) );
            val += 12;
        }
    }

    // Lager radene (17 stykker) og putter i #hacking-symbols
    function createHackingRows(){
        let hackingSym = document.getElementById("hacking-symbols");
        hackingSym.innerHTML = ""; // Tøm
        // Fyll opp symbolSpansLeft/Right
        symbolSpansLeft = [];
        symbolSpansRight = [];
        words = []; // reset
        // Bygger opp random symboler + fletter inn ord
        generateSymbols(symbolSpansLeft);
        generateSymbols(symbolSpansRight);
        // Sett inn “goalWord” i stedet for et tilfeldig
        insertGoal();

        // Nå lager vi 17 rader
        for (let i=0; i<17; i++){
            let row = document.createElement("div");
            row.className = "terminal-row";

            // 1) Venstre pointer
            let leftPtrSpan = document.createElement("span");
            leftPtrSpan.className = "pointer";
            leftPtrSpan.textContent = pointerSpansLeft[i];
            row.appendChild(leftPtrSpan);

            // 2) spacing
            let spacing1 = document.createElement("span");
            spacing1.textContent = " ";
            row.appendChild(spacing1);

            // 3) 12 symboler venstre
            for (let j=0; j<12; j++){
                let idx = i*12 + j;
                let sp = symbolSpansLeft[idx];
                row.appendChild(sp);
            }

            // 4) litt spacing
            let spacing2 = document.createElement("span");
            spacing2.textContent = "  ";
            row.appendChild(spacing2);

            // 5) Høyre pointer
            let rightPtrSpan = document.createElement("span");
            rightPtrSpan.className = "pointer";
            rightPtrSpan.textContent = pointerSpansRight[i];
            row.appendChild(rightPtrSpan);

            // 6) spacing
            let spacing3 = document.createElement("span");
            spacing3.textContent = " ";
            row.appendChild(spacing3);

            // 7) 12 symboler høyre
            for (let j=0; j<12; j++){
                let idx = i*12 + j;
                let sp = symbolSpansRight[idx];
                row.appendChild(sp);
            }

            // 8) her kan vi lage en “feedback”-spalte (tomme <span>)
            let fb = document.createElement("span");
            fb.textContent = "";
            row.appendChild(fb);

            hackingSym.appendChild(row);
        }
        enableMinigame = true;
        minigameStarted = true;
    }

    // Fletter inn ord underveis
    function generateSymbols(arr){
        let symbols = ["!", "\"", "`", "$", "%", "^", "&", "*", "(", ")", "-", "_", "+",
            "=", "{", "[", "}", "]", ":", ";", "@", "'", "~", "#", "<", ">", ",",
            "?", "/", "|", "\\"];
        let symbolsSinceWord = 0;
        let totalNeeded = 17*12; // 204
        while (arr.length < totalNeeded){
            // symbol
            let s = document.createElement("span");
            s.className = "symbol";
            s.textContent = symbols[randInt(0, symbols.length-1)];
            s.onmouseenter = function(){ hover(s); };
            s.onmouseleave = function(){ unhighlightAll(); };
            s.onclick = function(){ clicked(s); };
            arr.push(s);
            symbolsSinceWord++;

            // Flett inn ord av og til
            if ((arr.length+wordlength)<(totalNeeded-5) && symbolsSinceWord>4 && randInt(0,17)>15 && allWords.length>0) {
                let widx = randInt(0, allWords.length-1);
                let w = allWords[widx];
                allWords.splice(widx,1);
                words.push(w);
                addWord(arr, w);
                symbolsSinceWord=0;
            }
        }
        // Match brackets i hver rad
        for (let i=0; i<17; i++){
            matchBrackets( arr.slice(i*12, (i+1)*12) );
        }
    }

    // Legg inn ord (7 bokstaver) i array
    function addWord(arr, word){
        for (let i=0; i<word.length; i++){
            let wSpan = document.createElement("span");
            wSpan.className = "word word-"+word;
            wSpan.textContent = word.charAt(i);
            wSpan.onclick = function(){ clicked(wSpan); };
            wSpan.onmouseenter = function(){ hover(wSpan); };
            wSpan.onmouseleave = function(){ unhighlightAll(); };
            wSpan.setAttribute("data-word", word);
            wSpan.setAttribute("data-charpos", i);
            arr.push(wSpan);
        }
    }

    // Bracket matching
    function matchBrackets(rowArr){
        let pairs = { "{":"}", "[":"]", "(" :")", "<":">" };
        for (let i=0; i<rowArr.length-1; i++){
            let startSpan = rowArr[i];
            let chr = startSpan.textContent;
            if (pairs[chr]){
                // let’s see if vi finner en match
                for (let j=i+1; j<rowArr.length; j++){
                    if (rowArr[j].classList.contains("word")) break;
                    if (rowArr[j].textContent===pairs[chr]){
                        // bracket root
                        let bracketClass = "bracketpair-" + bracketCount;
                        let bracketStr = "";
                        for (let k=i; k<=j; k++){
                            rowArr[k].classList.add(bracketClass);
                            bracketStr += rowArr[k].textContent;
                        }
                        startSpan.classList.add("bracketroot");
                        startSpan.setAttribute("data-bracketroot", bracketCount);
                        startSpan.setAttribute("data-bracketstr", bracketStr);
                        bracketCount++;
                        break;
                    }
                }
            }
        }
    }

    // Bytt ut tilfeldig ord med goalWord
    function insertGoal(){
        let widx = randInt(0, words.length-1);
        let oldWord = words[widx];
        words.splice(widx,1);
        // Finn <span>-ene
        let oldSpans = document.querySelectorAll(".word-"+oldWord);
        oldSpans.forEach( (sp, i)=>{
            sp.classList.remove("word-"+oldWord);
            sp.classList.add("word-"+goalWord);
            sp.setAttribute("data-word", goalWord);
            let pos = sp.getAttribute("data-charpos");
            sp.textContent = goalWord.charAt(pos);
        });
    }

    // Hover / highlight
    function hover(sp){
        if(!enableMinigame || terminalLocked) return;
        unhighlightAll();
        if (sp.classList.contains("word")){
            let w = sp.getAttribute("data-word");
            let sameWordSpans = document.querySelectorAll(".word-"+w);
            sameWordSpans.forEach( ws => ws.classList.add("highlight") );
            setEntry(w);
        } else if (sp.classList.contains("bracketroot")){
            let bID = sp.getAttribute("data-bracketroot");
            let allB = document.querySelectorAll(".bracketpair-"+bID);
            allB.forEach( b => b.classList.add("highlight") );
            setEntry(sp.getAttribute("data-bracketstr"));
        } else {
            sp.classList.add("highlight");
            setEntry(sp.textContent);
        }
    }

    // Unhighlight
    function unhighlightAll(){
        let hi = document.querySelectorAll("#hacking .highlight");
        hi.forEach( h => h.classList.remove("highlight") );
    }

    // Klikk
    function clicked(sp){
        if(!enableMinigame || terminalLocked) return;
        // her kan du spille en “enter-lyd”, etc.
        if(sp.classList.contains("word")){
            let w = sp.getAttribute("data-word");
            if (w===goalWord) {
                // Riktig passord
                terminalLocked=true;
                enableMinigame=false;
                addFeedback(">"+w);
                addFeedback(">Exact match!");
                addFeedback(">Please wait while system is accessed.");
                setTimeout(login, 2000);
            } else {
                // Feil
                let corr=0;
                for (let i=0; i<wordlength; i++){
                    if (w.charAt(i)===goalWord.charAt(i)) corr++;
                }
                addFeedback(">"+w);
                addFeedback(">Entry Denied");
                addFeedback(">"+corr+"/"+wordlength+" correct.");
                attempts--;
                setAttempts();
            }
        } else if(sp.classList.contains("bracketroot")) {
            let bracketStr = sp.getAttribute("data-bracketstr");
            addFeedback(">"+bracketStr);
            if(!hadRefresh && randInt(0,10)>7){
                hadRefresh=true;
                attempts=4;
                setAttempts();
                addFeedback(">Allowance replenished.");
            } else {
                // fjern dud
                if (words.length>0){
                    let idx=randInt(0, words.length-1);
                    let dud = words[idx];
                    words.splice(idx,1);
                    let dudSpans = document.querySelectorAll(".word-"+dud);
                    dudSpans.forEach(ds=>{
                        ds.textContent=".";
                        ds.classList.remove("word");
                        ds.classList.add("symbol");
                    });
                    addFeedback(">Dud removed.");
                }
            }
            sp.classList.remove("bracketroot");
        } else if(sp.classList.contains("symbol")){
            addFeedback(">"+sp.textContent);
            addFeedback(">Error");
        }
    }

    // Oppdater attempts
    function setAttempts() {
        document.getElementById("attemptstext").textContent = attempts + " ATTEMPT(S) LEFT:";
        let blocks = "";
        for (let i = 0; i < attempts; i++) {
            blocks += " ██";
        }
        document.getElementById("attemptsblocks").textContent = blocks;

        if (attempts <= 1) {
            document.getElementById("message").textContent = "!!! WARNING: LOCKOUT IMMINENT !!!";
            document.getElementById("message").classList.add("blinker");
        }

        if (attempts <= 0) {
            terminalLocked = true;
            addFeedback(">Lockout in progress.");

            setTimeout(function () {
                let hackingSymbols = document.getElementById("hacking-symbols");
                hackingSymbols.innerHTML = "TERMINAL LOCKED\nPLEASE CONTACT AN ADMINISTRATOR";

                // Create the return link dynamically
                let homeLink = document.createElement("p");
                homeLink.innerHTML = `Time to head home to actually try to hack this website, <a href="/">return to homepage</a>`;
                homeLink.style.marginTop = "20px"; // Optional spacing
                homeLink.style.textAlign = "center"; // Center it

                // Append the link below the "TERMINAL LOCKED" message
                hackingSymbols.appendChild(homeLink);

            }, 2000);
        }
    }


    // Viser hva brukeren "hoverer" i kommando-linjen
    var currentText = "", targetText="";
    function setEntry(txt){
        if (currentText!==txt){
            targetText=txt;
            document.getElementById("entry").textContent="";
            currentText="";
            document.getElementById("hack-cursor2").className="cursor-on";
            typeEntryChar();
        }
    }
    function typeEntryChar(){
        if(currentText===targetText || currentText.length>=targetText.length){
            document.getElementById("hack-cursor2").className="cursor-flash";
            return;
        }
        // valgfri lyd
        currentText += targetText.charAt(currentText.length);
        document.getElementById("entry").textContent=currentText;
        setTimeout(typeEntryChar, 30+randInt(0,40));
    }

    function addFeedback(msg){
        let f = document.getElementById("feedback");
        f.textContent += msg+"\n";
    }

    // Login-skjerm
    function login() {
        terminalLocked = true;
        enableMinigame = false;

        // Fade out hacking interface
        let hackingScreen = document.getElementById("hacking");
        hackingScreen.style.transition = "opacity 0.8s ease-out";
        hackingScreen.style.opacity = "0";

        // Wait for fade-out effect before clearing
        setTimeout(() => {
            hackingScreen.innerHTML = ""; // Completely clear the hacking UI
            hackingScreen.classList.add("hidden"); // Hide the container

            // Show the logged-in message
            let loggedInScreen = document.getElementById("loggedin");
            loggedInScreen.classList.remove("hidden");
            loggedInScreen.style.opacity = "0";
            loggedInScreen.style.transition = "opacity 1s ease-in";
            setTimeout(() => {
                loggedInScreen.style.opacity = "1"; // Fade in success message
            }, 50);

            // Update the date
            let d = new Date();
            let year = d.getFullYear() + 60;
            let m = ("0" + (d.getMonth() + 1)).slice(-2);
            let day = ("0" + d.getDate()).slice(-2);
            document.getElementById("todays-date").textContent = year + "-" + m + "-" + day;
        }, 900); // Wait for fade-out before clearing
    }


    // ==========================
    // EVENT-LYTTERE
    // ==========================
    function turnOnClick(e){
        e.preventDefault();
        if(!clickedBegin) {
            turnOn();
        }
    }

    document.addEventListener("click", turnOnClick);
    document.addEventListener("keydown", function(e){
        if(!clickedBegin && (e.code==="Enter"|| e.code==="Space")){
            turnOn();
        }
    });

    // For fallback
    setTimeout(function(){
        if(!clickedBegin){
            document.getElementById("fallback-404").classList.remove("hidden");
        }
    }, 15000);

    // Start henting av ord
    preloadWords();
</script>
</body>
</html>
