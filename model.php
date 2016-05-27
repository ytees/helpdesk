<?php

$host = 'localhost';
$user = 'test';
$pass = 't3st3r123';
$db = 'test';
$prefix = 'ytees__';

$l = mysqli_connect($host, $user, $pass, $db);
mysqli_query($l, 'SET CHARACTER SET UTF8');

/**
 * Laeb andmebaasist kirjed valitud lehekülje kohta ja tagastab need massiivina.
 *
 * @param int $page Lehekülje number, mille kirjeid kuvada
 *
 * @return array Andmebaasi read
 */
function model_load($page, $filter)
{
    global $l, $prefix;
    
    $max = 5;
    $start = ($page - 1) * $max;
    $query = 'SELECT probleemi_id, probleemi_kirjeldus, kas_on_lahendatud FROM '.$prefix.'probleemid ORDER BY probleemi_id DESC LIMIT ?,?';

    if($filter == 'solved') {
        $query = 'SELECT probleemi_id, probleemi_kirjeldus, kas_on_lahendatud FROM '.$prefix.'probleemid WHERE kas_on_lahendatud = 1 ORDER BY probleemi_id DESC LIMIT ?,?';
    }
    else if($filter == 'not_solved') {
        $query = 'SELECT probleemi_id, probleemi_kirjeldus, kas_on_lahendatud FROM '.$prefix.'probleemid WHERE kas_on_lahendatud = 0 ORDER BY probleemi_id DESC LIMIT ?,?';
    }

    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'ii', $start, $max);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $probleem, $kas_on_lahendatud);

    $rows = array();
    while (mysqli_stmt_fetch($stmt)) {
        $rows[] = array(
            'id' => $id,
            'probleem' => $probleem,
            'kas_on_lahendatud' => $kas_on_lahendatud,
        );
    }

    mysqli_stmt_close($stmt);

    return $rows;
}

/**
 * Lisab andmebaasi uue rea.
 *
 * @param string $probleemi kirjeldus Kirje nimetus
 *
 * @return int Lisatud rea ID
 */
function model_add($kirjeldus)
{
    global $l, $prefix;

    //kas_on_lahendatud ei pea eraldi sisestama, sest see on andmebaasis default 0
    $query = 'INSERT INTO '.$prefix.'probleemid (probleemi_kirjeldus) VALUES (?)';
    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $kirjeldus);
    mysqli_stmt_execute($stmt);

    $id = mysqli_stmt_insert_id($stmt);

    mysqli_stmt_close($stmt);

    return $id;
}

/**
 * Kustutab valitud rea andmebaasist.
 *
 * @param int $id Kustutatava rea ID
 *
 * @return int Mitu rida kustutati
 */
function model_delete($id)
{
    global $l, $prefix;

    $query = 'DELETE FROM '.$prefix.'probleemid WHERE probleemi_id=? LIMIT 1';
    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    $deleted = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return $deleted;
}

/**
 * Muudab probleemi lahendatuks.
 *
 * @param int $id    Muudetava rea ID
 *
 * @return bool Tagastab true kui uuendamine õnnestus
 */
function model_solve($id)
{
    global $l, $prefix;

    $query = 'UPDATE '.$prefix.'probleemid SET kas_on_lahendatud=1 WHERE probleemi_id=? LIMIT 1';
    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_error($stmt)) {
        return false;
    }
    mysqli_stmt_close($stmt);

    return true;
}

/**
 * Lisab andmebaasi uue kasutaja. Õnnestub vaid juhul kui sellist kasutajat veel pole.
 * Parool salvestatakse BCRYPT räsina.
 *
 * @param string $kasutajanimi Kasutaja nimi
 * @param string $parool       Kasutaja parool
 *
 * @return int lisatud kasutaja ID
 */
function model_user_add($kasutajanimi, $parool)
{
    global $l, $prefix;

    $hash = password_hash($parool, PASSWORD_DEFAULT);

    $query = 'INSERT INTO '.$prefix.'kasutajad (kasutajanimi, parool) VALUES (?, ?)';
    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $kasutajanimi, $hash);
    mysqli_stmt_execute($stmt);

    $id = mysqli_stmt_insert_id($stmt);

    mysqli_stmt_close($stmt);

    return $id;
}

/**
 * Tagastab kasutaja ID, kelle kasutajanimi ja parool klapivad sisendiga.
 *
 * @param string $kasutajanimi Otsitava kasutaja kasutajanimi
 * @param string $parool       Otsitava kasutaja parool
 *
 * @return int Kasutaja ID
 */
function model_user_get($kasutajanimi, $parool)
{
    global $l, $prefix;

    $query = 'SELECT kasutaja_id, parool FROM '.$prefix.'kasutajad WHERE kasutajanimi=? LIMIT 1';
    $stmt = mysqli_prepare($l, $query);
    if (mysqli_error($l)) {
        echo mysqli_error($l);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $kasutajanimi);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id, $hash);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // kontrollime, kas vabateksti $parool klapib baasis olnud räsiga $hash
    if (password_verify($parool, $hash)) {
        return $id;
    }

    return false;
}
