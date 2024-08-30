 // Variables globales
 let tableMine = [];
 let chrono = 0;
 const table = document.getElementById('tabDemine');
 let nbrMine = 50;
 const largeur = 24;
 const nbrCase = largeur * largeur;
 let funChrono = null;
 let TabCaseCheck = [];
 const toFind = document.querySelector('.toFind');
 const cellWidth = 30;
var imgUrl = "shrek.jpg";

document.addEventListener('DOMContentLoaded', () => {
    init(largeur);
});

// Initialisation de la partie
function init(largeur) {
    tableMine = Array(nbrCase).fill(null);

    var cellCount = 0;
    for (let i = 0; i < largeur; i++) {
        let row = document.createElement('tr');
        for (let j = 0; j < largeur; j++) {
            let cell = document.createElement('td');
            cell.style.width = `${cellWidth}px`;
            cell.style.height = `${cellWidth}px`;
            cell.style.position = 'relative';
            cell.style.overflow = 'hidden';
            
            // Image découpée
            const divInner = document.createElement('div');
            divInner.style.width = '100%';
            divInner.style.height = '100%';
            divInner.style.backgroundImage = `url(${imgUrl})`;
            divInner.style.backgroundSize = `${largeur * cellWidth}px ${largeur * cellWidth}px`;
            divInner.style.backgroundPosition = `-${j * cellWidth}px -${i * cellWidth}px`;
            divInner.style.clipPath = 'inset(0 100% 100% 0)'; // Masquer tout sauf la partie visible
            divInner.style.transition = 'clip-path 0.5s'; // Animation pour la révélation
            
            cell.appendChild(divInner);
            cell.id = cellCount;
            cell.addEventListener('click', function () {
                const posInitial = parseInt(this.id, 10);
                play(posInitial);
            });
            row.appendChild(cell);
            cellCount++;
        }
        table.appendChild(row);
    }

    toFind.textContent = `To find: ${TabCaseCheck.length} / ${(nbrCase - nbrMine)}`;

    document.querySelectorAll('td').forEach(cell => {
        cell.addEventListener('click', function () {
            const posInitial = parseInt(this.id, 10);
            play(posInitial);
        });
    });
}

function play(posInitial) {
    const champLibre = FindVoisin(posInitial);
    champLibre.push(posInitial);

    while (nbrMine > 0) {
        const posAlea = Math.floor(Math.random() * nbrCase);
        if (!champLibre.includes(posAlea)) {
            tableMine[posAlea] = 'mine';
            nbrMine--;
        }
    }

    funChrono = setInterval(() => {
        chrono++;
        document.getElementById('chrono').textContent = chrono;
    }, 1000);

    for (let i = 0; i < nbrCase; i++) {
        if (tableMine[i] === null) {
            tableMine[i] = countMine(i);
        }
    }

    checkVoisin(posInitial);
    caseCheck(posInitial);

    document.querySelectorAll('td').forEach(cell => {
        cell.addEventListener('mousedown', function (event) {
            const position = parseInt(this.id, 10);
            if (event.button === 0) { // Left click
                if (tableMine[position] === 'mine') {
                    this.classList.add("mine");
                    gameLose();
                } else {
                    if (tableMine[position] === 0) {
                        checkVoisin(position);
                    }
                    caseCheck(position);
                    if (TabCaseCheck.length >= nbrCase - nbrMine) {
                        clearInterval(funChrono);
                        gameWin();
                    }
                }
            } else if (event.button === 2) { // Right click
                addFlag(position);
            }
        });
    });
}

// Affiche tout pour debug
function viewAll() {
    for (let i = 0; i < nbrCase; i++) {
        const cell = document.getElementById(i);
        if (tableMine[i] !== 'mine') {
            cell.classList.add("check");
            cell.textContent = tableMine[i];
        } else {
            cell.classList.add("mine");
            cell.textContent = "*";
        }
    }
}

// Compte les mines autour d'une case
function countMine(pos) {
    const voisins = FindVoisin(pos);
    let mineAdj = 0;
    voisins.forEach(voisin => {
        if (tableMine[voisin] === 'mine') mineAdj++;
    });
    return mineAdj;
}

// Vérifie les voisins
function checkVoisin(pos) {
    console.log('Voisin check')
    const voisins = FindVoisin(pos);
    voisins.forEach(voisin => {
        if (tableMine[voisin] === 0 && !TabCaseCheck.includes(voisin)) {
            caseCheck(voisin);
            checkVoisin(voisin);
            revealCell(pos); // Révéler l'image dans la cellule
        } else {
            if(tableMine[voisin] != 'mine'){
                revealCell(pos); // Révéler l'image dans la cellule
                caseCheck(voisin);
            }
        }
    });
}

// Dévoile une case
function caseCheck(pos) {
    const cell = document.getElementById(pos);
    cell.classList.add("check");
    if (tableMine[pos] !== 0) {
        cell.textContent = tableMine[pos];
        cell.classList.add(`col${tableMine[pos]}`);
    }
    if (!TabCaseCheck.includes(pos)) {
        TabCaseCheck.push(pos);
    }
}


// Fonction pour révéler une cellule spécifique
function revealCell(index) {
    const cell = document.getElementById(index);
    const divInner = cell ? cell.querySelector('div') : null;
    if (divInner) {
        divInner.style.clipPath = 'inset(0 0 0 0)'; // Révèle l'image
    }
}


// Ajoute un drapeau sur la case
function addFlag(pos) {
    const cell = document.getElementById(pos);
    cell.classList.add("flag");
    cell.textContent = "?";
}

// Renvoie les voisins d'une case
function FindVoisin(pos) {
    const voisins = [];
    const row = Math.floor(pos / largeur);
    const col = pos % largeur;

    // Ajoute les cases adjacentes
    if (col > 0) voisins.push(pos - 1); // gauche
    if (col < largeur - 1) voisins.push(pos + 1); // droite
    if (row > 0) voisins.push(pos - largeur); // haut
    if (row < Math.floor(nbrCase / largeur) - 1) voisins.push(pos + largeur); // bas

    // Ajoute les diagonales
    if (row > 0 && col > 0) voisins.push(pos - largeur - 1); // haut gauche
    if (row > 0 && col < largeur - 1) voisins.push(pos - largeur + 1); // haut droite
    if (row < Math.floor(nbrCase / largeur) - 1 && col > 0) voisins.push(pos + largeur - 1); // bas gauche
    if (row < Math.floor(nbrCase / largeur) - 1 && col < largeur - 1) voisins.push(pos + largeur + 1); // bas droite

    return voisins;
}

// Défaite
function gameLose() {
    tableMine.forEach((mine, i) => {
        if (mine === 'mine') {
            const cell = document.getElementById(i);
            cell.classList.add("mine");
            cell.textContent = "*";
        }
    });
    document.getElementById('infopartie').textContent = 'Partie perdue !';
    document.querySelector('.cache').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Victoire
function gameWin() {
    tableMine.forEach((mine, i) => {
        if (mine === 'mine') {
            const cell = document.getElementById(i);
            cell.classList.add("mine");
            cell.textContent = "*";
        }
    });
    document.getElementById('infopartie').innerHTML = `Victoire!!!<br>Votre temps : ${chrono}s`;
    document.querySelector('.cache').style.display = 'block';
    document.body.style.overflow = 'hidden';
}
