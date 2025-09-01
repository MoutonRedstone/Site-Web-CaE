<?php
$COOKIE_NAME = "affiches"; // L'avancement de l'utilisateur est sauvegardé dans un cookie nommé "affiches"



// //////////////  MOTS DE PASSE  ////////////// 
$affiches = array(
    "Résidences" => "DIEGO",
    "Bat.5 (C.R.I.)" => "FELICITATIONS",
    "Kfêt & FOY'" => "MOJITO",
    "Halle Francis Querné" => "19",
    "Bat.2 (STPI)" => "ENIGME",
    "Restaurant Universitaire" => "PAELLA",
    "Bat. 9 (S.I.)" => "ROBOT",
    "Amphi M. Drissi" => "SAVOIR",
    "Amphis A,B,C" => "HELP",
    "Bibl'INSA" => "RENNES"
); // Liste des affiches et du mot de passe lié

function normalize($str)
{
    $str = trim($str);
    $str = mb_strtoupper($str, 'UTF-8');
    $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str); // enlève les accents
    return $str;
}

// //////////////  CHIFFRAGE / DÉCHIFFRAGE DES COOKIES ////////////// 
function encodeAndSetCookie($values): void
{
    global $COOKIE_NAME;
    $res = openssl_encrypt($values, "aes-128-cbc", "bienvenue à l'INSA", 0, "INSCAPE>FOGSALAN");
    setcookie($COOKIE_NAME, $res, time() + (86400 * 7), "/");
}

function readCookie(): string
{
    global $COOKIE_NAME;
    $values = openssl_decrypt($_COOKIE[$COOKIE_NAME], "aes-128-cbc", "bienvenue à l'INSA", 0, "INSCAPE>FOGSALAN");
    return $values;
}

// Si l'utilisateur n'a pas encore utilisé le site, envoyer le cookie correspondant à aucune affiche trouvée ("0000000000"), sinon récupérer et déchiffrer le cookie
if (!isset($_COOKIE[$COOKIE_NAME])) {
    $cookie = str_repeat("0", count($affiches));
    $affichesTrouvées = $cookie;
    encodeAndSetCookie($cookie);
} else {
    $affichesTrouvées = readCookie();
}
// La chaîne affichesTrouvées stocke la valeur de chaque affiche : "0" = pas trouvée, "1" = trouvée

// ////////////// VÉRIFICATION DES MOTS DE PASSE //////////////
$feedbackMessage = ""; // Message de retour affiché sous l'input pour le mot de passe

if (isset($_POST["password"])) {
    $input = $_POST["password"];
    if ($input == "") {
        $feedbackMessage = "Veuillez entrer un mot de passe !";
    } else {
        $feedbackMessage = "Mot de passe incorrect !";
        for ($i = 0; $i < count($affiches); $i++) {
            $nomAffiche = array_keys($affiches)[$i];
            $password = $affiches[$nomAffiche];
            if ($password === $input) {
                $affichesTrouvées[$i] = '1';
                encodeAndSetCookie($affichesTrouvées);
                $feedbackMessage = "Bravo ! Vous avez déverrouillé le cadenas \"$nomAffiche\"";
                break;
            }
        }
    }
    // Purger le contenu de la requête POST pour les prochaines requêtes
    $_POST = array();
}

// Vérifier si l'utilisateur a résolu toutes les affiches (aucun "0" dans affichesTrouvées)
$hasSolvedEverything = !str_contains($affichesTrouvées, "0");
if ($hasSolvedEverything) {
    $feedbackMessage = "";
}
?>

<head>
    <title>Bienvenue à l'INSA</title>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
</head>

<body>
    <!-- HEADER -->
    <header>
        <h1>Ins'cape Game</h1>
        <img src="/LOGO.png" alt="Logo" class="logo">
    </header>

    <!-- PASSWORD BOX -->
    <section class="password-box">
        <p>Entrez un mot de passe que vous avez découvert :</p>
        <form action="/index.php" method="post">
            <input type="password" name="password" placeholder="Mot de passe">
            <input type="submit" value="Valider">
        </form>
    </section>

    <!-- RÈGLES -->
    <p class="rules">
        Rappel des règles : <a href="#" id="showImage" data-img="/rules.png">ici</a>
    </p>

    <p id="resultMessage"><?php echo $feedbackMessage ?></p>

    <?php
    if ($hasSolvedEverything) {
        echo "<h3>Bravo ! Tu as complété toutes les énigmes ! Contacte-nous sur instagram pour recevoir ta récompense<br>".
             "<a href=\"https://www.instagram.com/inscape_game_rennes?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==\">@inscape_game_rennes</h3>";
    }
    ?>

    <!-- LOCKS -->
    <div id="locks">
        <?php
        for ($i = 0; $i < count($affiches); $i++) {
            $nomAffiche = array_keys($affiches)[$i];
            if ($affichesTrouvées[$i] == "0") {
                $texte = "Verrouillé";
                $class = "closed";
                $tooltip = "Vous n'avez pas encore trouvé le mot de passe de $nomAffiche !";
            } else {
                $texte = "Ouvert";
                $class = "open";
                $tooltip = "Vous avez déjà trouvé le mot de passe pour $nomAffiche.";
            }
            echo "<div class=\"$class lock-card\" data-tooltip=\"$tooltip\">
                        <h2>$nomAffiche</h2>
                        <p>$texte</p>
                        <span class='custom-tooltip'></span>
                    </div>";
        }
        ?>
    </div>

    <footer>
        <p>Site web créé par Mouton pour le club INS'Cape Game</p>
        <img src="/LOGO.png">
    </footer>

    <script>
        const allCards = document.querySelectorAll('.lock-card');
        let currentTooltip = null;

        const isMobile = window.matchMedia("(pointer: coarse)").matches;

        allCards.forEach(card => {
            const tooltip = card.querySelector('.custom-tooltip');
            const message = card.getAttribute('data-tooltip');
            tooltip.textContent = message;

            const showTooltip = () => {
                if (currentTooltip && currentTooltip !== tooltip) {
                    currentTooltip.style.opacity = 0;
                }
                tooltip.style.opacity = 1;
                currentTooltip = tooltip;
            };

            const hideTooltip = () => {
                tooltip.style.opacity = 0;
                if (currentTooltip === tooltip) currentTooltip = null;
            };

            if (isMobile) {
                card.addEventListener('click', (e) => {
                    e.stopPropagation();
                    tooltip.style.opacity == 1 ? hideTooltip() : showTooltip();
                });
            } else {
                card.addEventListener('mouseenter', showTooltip);
                card.addEventListener('mouseleave', hideTooltip);
                card.addEventListener('click', (e) => {
                    e.stopPropagation();
                    tooltip.style.opacity == 1 ? hideTooltip() : showTooltip();
                });
            }
        });

        document.addEventListener('click', () => {
            if (currentTooltip) {
                currentTooltip.style.opacity = 0;
                currentTooltip = null;
            }
        });

        /* ========== IMAGE OVERLAY ========== */
        const link = document.getElementById('showImage');

        const overlay = document.createElement('div');
        overlay.id = 'overlayImage';
        const img = document.createElement('img');
        overlay.appendChild(img);
        document.body.appendChild(overlay);

        link.addEventListener('click', (e) => {
            e.preventDefault();
            const src = link.getAttribute('data-img');
            img.src = src;
            overlay.style.opacity = 1;
            overlay.style.pointerEvents = 'auto';
        });

        overlay.addEventListener('click', () => {
            overlay.style.opacity = 0;
            overlay.style.pointerEvents = 'none';
        });
    </script>

</body>