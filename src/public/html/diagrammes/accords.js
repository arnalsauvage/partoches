const tableauAccords = {};

// A faire  : 7 familles à renseigner Les accords Majeurs 7 augmentés M7+ , les accords Majeurs 7 de quinte diminuée  maj7(5b) ,
// Les accords mineurs Majeur7 mMaj7, les accords de 7è augmentés aug7 , Les accords majeurs avec quinte diminuée b5,
// Les accords de sixte /neuvième 6/9 , Les accords de neuvième 9,

// Fait  : 13 familles renseignées maj min maj7 min7 7M sus2 sus4 aug dim dim7 5 6 m6 - 144 accords de base

// Accords majeurs

tableauAccords["C"] = "0003";
tableauAccords["C#"] = "1114";
tableauAccords["Db"] = "1114";
tableauAccords["D"] = "2220";
tableauAccords["D#"] = "0331";
tableauAccords["Eb"] = "0331";
tableauAccords["E"] = "1402";
tableauAccords["F"] = "2010";
tableauAccords["F#"] = "3121";
tableauAccords["Gb"] = "3121";
tableauAccords["G"] = "0232";
tableauAccords["G#"] = "5343";
tableauAccords["Ab"] = "5343";
tableauAccords["A"] = "2100";
tableauAccords["A#"] = "3211";
tableauAccords["Bb"] = "3211";
tableauAccords["B"] = "4322";

// Accords mineurs

tableauAccords["Cm"] = "0333";
tableauAccords["C#m"] = "1104";
tableauAccords["Dbm"] = "1104";
tableauAccords["Dm"] = "2210";
tableauAccords["D#m"] = "3321";
tableauAccords["Ebm"] = "3321";
tableauAccords["Em"] = "0432";
tableauAccords["Fm"] = "1013";
tableauAccords["F#m"] = "2120";
tableauAccords["Gbm"] = "2120";
tableauAccords["Gm"] = "0231";
tableauAccords["G#m"] = "4342";
tableauAccords["Abm"] = "4342";
tableauAccords["Am"] = "2000";
tableauAccords["A#m"] = "3111";
tableauAccords["Bbm"] = "3111";
tableauAccords["Bm"] = "4222";

// Accords de 7è

tableauAccords["C7"] = "0001";
tableauAccords["C#7"] = "1112";
tableauAccords["Db7"] = "1112";
tableauAccords["D7"] = "2020";
tableauAccords["D#7"] = "3334";
tableauAccords["Eb7"] = "3334";
tableauAccords["E7"] = "1202";
tableauAccords["F7"] = "2313";
tableauAccords["F#7"] = "3424";
tableauAccords["Gb7"] = "3424";
tableauAccords["G7"] = "0212";
tableauAccords["G#7"] = "1323";
tableauAccords["Ab7"] = "1323";
tableauAccords["A7"] = "0100";
tableauAccords["A#7"] = "1211";
tableauAccords["Bb7"] = "1211";
tableauAccords["B7"] = "2322";

// Accords mineurs 7

tableauAccords["Cm7"] = "3333";
tableauAccords["C#m7"] = "1102";
tableauAccords["Dbm7"] = "1102";
tableauAccords["Dm7"] = "2213";
tableauAccords["D#m7"] = "3324";
tableauAccords["Ebm7"] = "3324";
tableauAccords["Em7"] = "0202";
tableauAccords["Fm7"] = "1313";
tableauAccords["F#m7"] = "2424";
tableauAccords["Gbm7"] = "2424";
tableauAccords["Gm7"] = "0211";
tableauAccords["G#m7"] = "1322";
tableauAccords["Abm7"] = "1322";
tableauAccords["Am7"] = "0000";
tableauAccords["A#m7"] = "1111";
tableauAccords["Bbm7"] = "1111";
tableauAccords["Bm7"] = "2222";

// Accords 7è majeure

tableauAccords["C7M"] = "0002";
tableauAccords["C#7M"] = "1113";
tableauAccords["Db7M"] = "1113";
tableauAccords["D7M"] = "2224";
tableauAccords["D#7M"] = "3335";
tableauAccords["Eb7M"] = "3335";
tableauAccords["E7M"] = "1302";
tableauAccords["F7M"] = "5500";
tableauAccords["F#7M"] = "3524";
tableauAccords["Gb7M"] = "3524";
tableauAccords["G7M"] = "0222";
tableauAccords["G#7M"] = "1333";
tableauAccords["Ab7M"] = "1333";
tableauAccords["A7M"] = "1100";
tableauAccords["A#7M"] = "3210";
tableauAccords["Bb7M"] = "3210";
tableauAccords["B7M"] = "4321";

// Accords 5 Power chords

tableauAccords["C5"] = "0033";
tableauAccords["C#5"] = "1144";
tableauAccords["Db5"] = "1144";
tableauAccords["D5"] = "2255";
tableauAccords["D#5"] = "134x";
tableauAccords["Eb5"] = "134x";
tableauAccords["E5"] = "4402";
tableauAccords["F5"] = "x013";
tableauAccords["F#5"] = "x124";
tableauAccords["Gb5"] = "x124";
tableauAccords["G5"] = "0235";
tableauAccords["G#5"] = "134x";
tableauAccords["Ab5"] = "134x";
tableauAccords["A5"] = "2400";
tableauAccords["A#5"] = "3x11";
tableauAccords["Bb5"] = "3x11";
tableauAccords["B5"] = "4x22";

// Accords dim
tableauAccords["Cdim"] = "5323";
tableauAccords["C#dim"] = "6434";
tableauAccords["Dbdim"] = "6434";
tableauAccords["Ddim"] = "7545";
tableauAccords["D#dim"] = "7545";
tableauAccords["Ebdim"] = "7545";
tableauAccords["Edim"] = "2320";
tableauAccords["Fdim"] = "4542";
tableauAccords["F#dim"] = "2020";
tableauAccords["Gbdim"] = "2020";
tableauAccords["Gdim"] = "0131";
tableauAccords["G#dim"] = "1242";
tableauAccords["Abdim"] = "1242";
tableauAccords["Adim"] = "2353";
tableauAccords["A#dim"] = "3101";
tableauAccords["Bbdim"] = "3101";
tableauAccords["Bdim"] = "4212";

// Accords dim7

tableauAccords["Cdim7"] = "3434";
tableauAccords["C#dim7"] = "0101";
tableauAccords["Dbdim7"] = "0101";
tableauAccords["Ddim7"] = "1212";
tableauAccords["D#dim7"] = "2323";
tableauAccords["Ebdim7"] = "2323";
tableauAccords["Edim7"] = "0101";
tableauAccords["Fdim7"] = "1212";
tableauAccords["F#dim7"] = "2323";
tableauAccords["Gbdim7"] = "2323";
tableauAccords["Gdim7"] = "0101";
tableauAccords["G#dim7"] = "1212";
tableauAccords["Abdim7"] = "1212";
tableauAccords["Adim7"] = "2323";
tableauAccords["A#dim7"] = "0101";
tableauAccords["Bbdim7"] = "0101";
tableauAccords["Bdim7"] = "1212";

// Accords 5è augmentée

tableauAccords["Caug"] = "1003";
tableauAccords["C#aug"] = "2110";
tableauAccords["Dbaug"] = "2110";
tableauAccords["Daug"] = "3221";
tableauAccords["D#aug"] = "0332";
tableauAccords["Ebaug"] = "0332";
tableauAccords["Eaug"] = "1003";
tableauAccords["Faug"] = "2110";
tableauAccords["F#aug"] = "3221";
tableauAccords["Gbaug"] = "3221";
tableauAccords["Gaug"] = "0332";
tableauAccords["G#aug"] = "1003";
tableauAccords["Abaug"] = "1003";
tableauAccords["Aaug"] = "2110";
tableauAccords["A#aug"] = "3221";
tableauAccords["Bbaug"] = "3221";
tableauAccords["Baug"] = "0332";

// Accords 57 aug

// Accords 6

tableauAccords["C6"] = "0000";
tableauAccords["C#6"] = "1111";
tableauAccords["Db6"] = "1111";
tableauAccords["D6"] = "2222";
tableauAccords["D#6"] = "3333";
tableauAccords["Eb6"] = "3333";
tableauAccords["E6"] = "4444";
tableauAccords["F6"] = "2213";
tableauAccords["F#6"] = "3324";
tableauAccords["Gb6"] = "3324";
tableauAccords["G6"] = "0202";
tableauAccords["G#6"] = "1313";
tableauAccords["Ab6"] = "1313";
tableauAccords["A6"] = "2424";
tableauAccords["A#6"] = "0211";
tableauAccords["Bb6"] = "0211";
tableauAccords["B6"] = "1322";

// Accords m6

tableauAccords["Cm6"] = "2333";
tableauAccords["C#m6"] = "1101";
tableauAccords["Dbm6"] = "1101";
tableauAccords["Dm6"] = "2212";
tableauAccords["D#m6"] = "3323";
tableauAccords["Ebm6"] = "3323";
tableauAccords["Em6"] = "0102";
tableauAccords["Fm6"] = "1213";
tableauAccords["F#m6"] = "2324";
tableauAccords["Gbm6"] = "2324";
tableauAccords["Gm6"] = "0201";
tableauAccords["G#m6"] = "1312";
tableauAccords["Abm6"] = "1312";
tableauAccords["Am6"] = "2423";
tableauAccords["A#m6"] = "0111";
tableauAccords["Bbm6"] = "0111";
tableauAccords["Bm6"] = "1222";



// Accords sus 2
tableauAccords["Csus2"] = "0233";
tableauAccords["C#sus2"] = "1344";
tableauAccords["Dbsus2"] = "1344";
tableauAccords["Dsus2"] = "2200";
tableauAccords["D#sus2"] = "3311";
tableauAccords["Ebsus2"] = "3311";
tableauAccords["Esus2"] = "4422";
tableauAccords["Fsus2"] = "0013";
tableauAccords["F#sus2"] = "1124";
tableauAccords["Gbsus2"] = "1124";
tableauAccords["Gsus2"] = "0230";
tableauAccords["G#sus2"] = "1341";
tableauAccords["Absus2"] = "1341";
tableauAccords["Asus2"] = "4452";
tableauAccords["A#sus2"] = "3011";
tableauAccords["Bbsus2"] = "3011";
tableauAccords["Bsus2"] = "4122";

// Accords sus 4

tableauAccords["Csus4"] = "0013";
tableauAccords["C#sus4"] = "1124";
tableauAccords["Dbsus4"] = "1124";
tableauAccords["Dsus4"] = "0230";
tableauAccords["D#sus4"] = "1341";
tableauAccords["Ebsus4"] = "1341";
tableauAccords["Esus4"] = "4400";
tableauAccords["Fsus4"] = "3011";
tableauAccords["F#sus4"] = "4122";
tableauAccords["Gbsus4"] = "4122";
tableauAccords["Gsus4"] = "0233";
tableauAccords["G#sus4"] = "1344";
tableauAccords["Absus4"] = "1344";
tableauAccords["Asus4"] = "2200";
tableauAccords["A#sus4"] = "3311";
tableauAccords["Bbsus4"] = "3311";
tableauAccords["Bsus4"] = "4422";