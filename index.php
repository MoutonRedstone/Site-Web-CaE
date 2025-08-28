<?php
    $COOKIE_NAME = "affiches"; // L'avancement de l'utilisateur est sauvegardé dans un cookie nommé "affiches"

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

    function encodeAndSetCookie($values): void // La fonction chiffre les affiches trouvées (pour pas qu'il trichent) et envoie le cookie
    {
        global $COOKIE_NAME;
        $res = openssl_encrypt($values, "aes-128-cbc", "bienvenue à l'INSA", 0, "INSCAPE>FOGSALAN"); //les clés de chiffrement ont évidemment été générées aléatoirement
        setcookie($COOKIE_NAME, $res, time() + (86400 * 7), "/");
    }
    
    function readCookie(): string // récupérer et déchiffrer le cookie 
    {
        global $COOKIE_NAME;
        $values = openssl_decrypt($_COOKIE[$COOKIE_NAME], "aes-128-cbc", "bienvenue à l'INSA", 0, "INSCAPE>FOGSALAN");
        return $values;
    }
    
    //Si ils n'ont pas encore utilisé le site, leur envoyer le cookie qui correspond à aucune affiche trouvée ("0000000000"), sinon récupérer leur cookie et le déchiffrer
    if(!isset($_COOKIE[$COOKIE_NAME])) {
        $cookie = "";
        for($i = 0; $i < count($affiches); $i++){
            $cookie = $cookie . "0";
        }
        $affichesTrouvées = $cookie;
        encodeAndSetCookie($cookie);
    }else{
        $affichesTrouvées = readCookie();
    }
    //La string affichesTrouvées stocke la valeur de chaque affiche "0" = pas trouvée "1" = trouvée
    
    $feedbackMessage=""; // Message de feedback affiché en dessous de l'input pour le mot de passe
    //On checke si l'utilisateur a entré un mot de passe
    if (isset($_POST["password"])) {
        $input = $_POST["password"];
        if ($input == "") {
            $feedbackMessage = "Veuillez entrer un mot de passe !";
        }
        else{
            $feedbackMessage = "Mot de passe incorrect !";
            //On parcourt chaque affiche
            for($i = 0; $i < count($affiches); $i++){
                $nomAffiche = array_keys($affiches)[$i];
                $password = $affiches[$nomAffiche];
                if ($password === $input) {
                    $affichesTrouvées[$i] = '1';
                    encodeAndSetCookie($affichesTrouvées);
                    $feedbackMessage = "Bravo ! Tu as déverrouillé le cadenas \"$nomAffiche\"";
                    break;
                }
            }
        }
        //Purger le contenu de la requète POST pour les prochaines requêtes
        $_POST = array();
    }

    // Vérifier si l'utilisateur a solutionné toutes les affiches (affichesTrouvées ne contient pas de 0)
    $hasSolvedEverything = !str_contains($affichesTrouvées, "0");
    if ($hasSolvedEverything){
        $feedbackMessage = "";
    }

?>
<head>
    <title>Bienvenue à l'INSA</title>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
</head>
<body>
    <div id="locks">
        <?php
            //On parcourt chaque affiche et on crée une div pour
            for($i = 0; $i < count($affiches); $i++){
                $nomAffiche = array_keys($affiches)[$i];
                if($affichesTrouvées[$i] == "0"){
                    $texte = "Verrouillé";
                    $class = "closed";
                }else{
                    $texte = "Ouvert";
                    $class = "open";
                }
                //La classe sert à afficher de la bonne couleur la div
                echo "<div class=\"$class\">
                        <h2>$nomAffiche</h2>
                        <p>$texte</p>
                    </div>";
            }
        ?>
    </div>
    
    <br>

    <form action="/index.php" method="post">
        <label>Mot de passe :</label><br>
        <input type="password" name="password">
        <input type="submit" value="Valider">
    </form>
    <p id="resultMessage"><?php echo $feedbackMessage ?></p>
    <?php
        //Si l'utilisateur a résolu toutes les énigmes, lui afficher le lien vers la page de récompense
        if($hasSolvedEverything){
            echo "<h3>Bravo ! Tu as résolu toutes les énigmes !!!</h3>".
                "<button onclick='window.location.href=\"/resultat_chasse_au_trésor.html\"'>Ta récompense</button>";
        }
    ?>
    <footer><p>Site web créé par Mouton pour le club INS'Cape Game</p><img src="/LOGO.png"></footer>
</body>