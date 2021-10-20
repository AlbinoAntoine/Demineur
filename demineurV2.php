<header>
    <script type="text/javascript" src="jQuery.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titan+One&display=swap" rel="stylesheet">
</header>
<style>
    * {
        font-family: 'Titan One', cursive;
        font-weight: bold;
    }
    .col1{
        color: #4995BF;
    }
    .col2{
        color: #5FBF49;
    }
    .col3{
        color: #BF5549;
    }
    .col4{
        color: #9649bf;
    }
    .col5{
        color: #6a49bf;
    }
    .col6{
        color: #bf498c;
    }

    table {
        border: none;
        margin: auto;
        border-collapse: collapse;
    }

    h1 {
        text-align: center;
        margin: 0;
    }

    tr {
        border: none !important;
    }

    td:not(.check):hover {
        background-color: #BCFF7B !important;
    }

    td {
        height: 25px;
        width: 25px;
        text-align: center;
        transition: background-color 0.5s;
        border: none !important;
        margin: 0;
        padding: 0;
    }

    td:nth-child(2n+0) {
        background-color: #93DD4D;
    }

    td:nth-child(2n+1) {
        background-color: #82CB3C;
    }
    .check:nth-child(2n+0){
        background-color: #F5DDA9;
    }
    .check:nth-child(2n+1){
        background-color: #DCCAA3;
    }

    .flag {
        background-color: orange !important;
    }

    .cache {
        position: absolute;
        top: 0;
        left: 0;
        height: 100vh;
        width: 100vw;
        background-color: rgba(0, 0, 0, 0.6);
    }

    .cache div {
        position: relative;
        top: 30vh;
        width: 100vw;
    }

    #infopartie {
        text-align: center;
        font-size: 2em;
        color: aliceblue;
    }
    .btn-retry{
        display: block;
    }
    .btn-restart {
        margin: 20px auto;
        display: block;
        text-align: center;
        font-size: 2em;
        color: aliceblue;
        padding: 20px;
        background-color: #82CB3C;
        border-radius: 5px;
        max-width: 30%;
    }
    header{
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .mine {
        background-color: red !important;
    }


</style>
<header>
    <h1>Démineur</h1>
    <p>Timer : <span id="chrono">0</span></p>
    <p class="toFind"></p>
    <a href="" class="btn-retry">Retry</a>
</header>
<div class="cache" style="display: none">
    <div>
        <p id="infopartie">Partie perdu</p>
        <a href="" class="btn-restart">Try Again</a>
    </div>
</div>
<table id="tabDemine" oncontextmenu="return false;">

</table>
<script>
    $(function () {
            // Variable global
            var tableMine = [];
            var chrono = 0;
            var tableau = $('#tabDemine');
            var nbrMine = 99;
            //var largeur = window.prompt('Donnez la largeur du jeu');
            var largeur = 24;
            var nbrCase = largeur * largeur;
            var funChrono = "";
            var TabCaseCheck = [];
            var toFind = $('.toFind');
            init(largeur);

            /**
             * Initialise la partie
             */
            function init(largeur) {
                for (let i = 0; i < nbrCase; i++) {
                    tableMine[i] = null;
                }
                var iLargeur = 0;
                // Création de la grille affiché
                for (let i = 0; i < nbrCase; i++) {
                    tableau.append('<td id="' + i + '">');
                    iLargeur++;
                    tableau.append('</td>');
                    if (iLargeur >= largeur) {
                        iLargeur = 0;
                        tableau.append('</tr><tr>');
                    }
                }
                toFind.html("To find :" + TabCaseCheck.length +' / '+(nbrCase-nbrMine));
                $("td").click(function () {
                    var posInitial = $(this).attr('id');
                    play(posInitial);
                })
            }

            function play(posInitial) {
                $('td').off();
                var champLibre = FindVoisin(posInitial);
                champLibre.push(parseInt(posInitial))
                console.log(champLibre)
                while (nbrMine > 0) {
                    var posAlea = Math.round((Math.random() * (nbrCase - 1)) + 1)
                    if (champLibre.indexOf(posAlea)===-1) {
                        tableMine[posAlea] = 'mine';
                        nbrMine--;
                    }
                }
                funChrono = window.setInterval(function () {
                    chrono++;
                    $('#chrono').html(chrono);
                }, 1000);
                for (let i = 0; i < nbrCase; i++) {
                    if (tableMine[i] == null) {
                        tableMine[i] = countMine(i);
                    }
                }
                checkVoisin(posInitial)
                caseCheck(posInitial)
                /**
                 * Détection de clic de la sourie sur une case
                 */
                $("td").mousedown(function (event) {
                    var position = $(this).attr('id');
                    if (event.which === 1) { // selement si c'est un clic gauche
                        //si clic sur une mine
                        if (tableMine[position] === 'mine') {
                            $(this).addClass("mine");
                            gameLose();
                        } else {
                            console.log(FindVoisin(position))
                            //si pas de mine autour, dévoile les voisins
                            if (tableMine[position] === 0) {
                                checkVoisin(position)
                            }
                            caseCheck(position)
                            console.log('Cases trouvées : '+TabCaseCheck.length +' / '+(nbrCase-nbrMine));
                            //Si toute les cases ont été checké
                            if (TabCaseCheck.length >= nbrCase-nbrMine) {
                                clearInterval(funChrono);
                                gameWin();
                            }
                        }
                    } else {
                        addFlag(position);
                    }
                });
            }

            //viewAll()
            /**
             * Pour debugger, dévoile se qui se cache sous chaque case
             */
            function viewAll() {
                for (let i = 0; i < nbrCase; i++) {
                    if (tableMine[i] !== 'mine') {
                        $('#' + i).addClass("check").html(tableMine[i]);
                    } else {
                        $('#' + i).addClass("mine").html("*");
                    }
                }
            }


            /**
             * Renvoie le nombre de mine autour de la case donné
             * @param pos
             */
            function countMine(pos) {
                var tabVoisin = FindVoisin(pos);
                var mineAdj = 0;
                for (let i = 0; i < tabVoisin.length; i++) {
                    if (tableMine[tabVoisin[i]] === 'mine') {
                        mineAdj++;
                    }
                }
                return mineAdj;
            }

            /**
             * regarde si les voisin de la case donné sont en contact de mine sinon les check aussi (pour suppression de zone)
             * @param pos
             */
            function checkVoisin(pos) {
                var tabTest = FindVoisin(pos);
                for (let i = 0; i < tabTest.length; i++) {
                    var voisin = tabTest[i];
                    if (tableMine[voisin] === 0 && TabCaseCheck.indexOf(voisin) === -1) {
                        caseCheck(voisin);
                        checkVoisin(voisin);
                    } else {
                        caseCheck(voisin);
                    }
                }
            }

            /**
             * Dévoile la case choisi
             * @param posCase
             */
            function caseCheck(posCase) {
                var idCase = $('#' + posCase);
                idCase.addClass("check");
                if (tableMine[posCase] !== 0) {
                    idCase.html(tableMine[posCase]);
                    idCase.addClass("col"+tableMine[posCase]);
                }
                if (TabCaseCheck[posCase] == null){
                    TabCaseCheck.push(posCase);
                }
            }

            /**
             * Ajoute un drapeau sur la case
             * @param posCase
             */
            function addFlag(posCase) {
                $('#' + posCase).addClass("flag").html("?");
            }

            /**
             * Revoie les voisin de la case donnée
             * @param pos
             * @returns {(number|*)[]}
             * @constructor
             */
            function FindVoisin(pos) {
                pos = parseInt(pos);
                // voir si la case est dans la colone de droite
                var posX = '';
                var posY = '';
                var TabVoisin = [];


                //--------- voisin droite et gauche ---------------
                if (pos % largeur === largeur - 1) {
                    posX = 'right';
                    TabVoisin.push(pos - 1);
                } else if (pos % largeur === 0) {
                    posX = 'left';
                    TabVoisin.push(pos + 1);
                } else {
                    posX = 'center';
                    TabVoisin.push(pos + 1, pos - 1);
                }

                // ------------ voisin haut et bas -----------
                if (pos - largeur < 0) {
                    posY = 'top';
                    TabVoisin.push(pos + largeur);
                } else if (pos + largeur > nbrCase) {
                    posY = 'bot';
                    TabVoisin.push(pos - largeur);
                } else {
                    posY = 'center';
                    TabVoisin.push(pos + largeur, pos - largeur);
                }

                // --------- voisin diagonal ------------
                //angle haut/gauche
                if (posX !== 'left' && posY !== 'top') {
                    TabVoisin.push(pos - largeur - 1)
                }
                //angle haut/droit
                if (posX !== 'right' && posY !== 'top') {
                    TabVoisin.push(pos - largeur + 1)
                }
                //angle bas/droit
                if (posX !== 'right' && posY !== 'bot') {
                    TabVoisin.push(pos + largeur + 1)
                }
                //angle bas/gauche
                if (posX !== 'left' && posY !== 'bot') {
                    TabVoisin.push(pos + largeur - 1)
                }
                return TabVoisin;
            }

            function gameLose() {
                for (let i = 0; i < tableMine.length; i++) {
                    if (tableMine[i] === 'mine') {
                        $('#' + i).addClass("mine").html("*");
                    }
                }
                $('#infopartie').html('Partie perdu !')
                $('.cache').fadeIn('slow');
                $('body').css('overflow','hidden')
            }

            function gameWin() {
                for (let i = 0; i < tableMine.length; i++) {
                    if (tableMine[i] === 'mine') {
                        $('#' + i).addClass("mine").html("*");
                    }
                }
                $('#infopartie').html('Victoire!!!' + '<br>' + 'Votre temps : ' + chrono + 's')
                $('.cache').fadeIn('slow');
                $('body').css('overflow','hidden')

            }

        }
    )
</script>