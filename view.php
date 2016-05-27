<!doctype HTML>
<html>

<head>
    <title>Kõnekeskus</title>
    <meta charset="utf-8">

    <link href="resources/css/bootstrap.min.css" rel="stylesheet">

    <style>
        #lisa-vorm {
            display: none;
        }
    </style>

</head>

<body>

<div class="container">

    <?php foreach (message_list() as $message):?>
        <p style="border: 1px solid blue; background: #EEE;">
            <?= $message; ?>
        </p>
    <?php endforeach; ?>

    <div style="float: right;">
        <form method="post"  action="<?= $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="action" value="logout">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <button type="submit">Logi välja</button>
        </form>
    </div>

    <div class="page-header">
        <h1>Kõnekeskus <small>Tuvi on loll</small></h1>
    </div>

    <p id="kuva-nupp">
        <button type="button">Kuva lisamise vorm</button>
    </p>

    <form id="lisa-vorm" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">

        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

        <p id="peida-nupp">
            <button type="button">Peida lisamise vorm</button>
        </p>

        <table class="table">
            <tr>
                <td>Probleem</td>
                <td>
                    <textarea cols="50" rows="5" id="probleem" name="probleem"></textarea>
                </td>
            </tr>
        </table>

        <p>
            <button type="submit">Lisa kirje</button>
        </p>

    </form>


    <form role="form" id="filtreeri" method="post" action="<?= $_SERVER['PHP_SELF']; ?>">
        <label class="radio">
            <input type="radio" name="filter" value="all" checked>Kõik probleemid
        </label>
        <label class="radio">
            <input type="radio" name="filter" value="solved" <?php if($_SESSION['filter']=='solved') echo 'checked' ?>>Lahendatud
        </label>
        <label class="radio">
            <input type="radio" name="filter" value="not_solved" <?php if($_SESSION['filter']=='not_solved') echo 'checked' ?>>Mitte lahendatud
        </label>

        <input type="hidden" name="action" value="filter">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
        <button type="submit" class="btn">Filtreeri</button>
    </form>

    <br/>


    <table id="probleem" class="table">
        <thead>
            <tr>
                <th>Probleemi kirjeldus</th>
                <th>Olek</th>
                <th>Tegevused</th>
            </tr>
        </thead>

        <tbody>

        <?php
        // koolon tsükli lõpus tähendab, et tsükkel koosneb HTML osast
        foreach (model_load($page, $_SESSION['filter']) as $rida): ?>

            <tr>
                <td>
                    <?=
                        // vältimaks pahatahtlikku XSS sisu, kus kasutaja sisestab õige
                        // info asemel <script> tag'i, peame tekstiväljundis asendama kõik HTML erisümbolid
                        // nl2br teeb reavahetuse html <br> tagiks
                        nl2br(htmlspecialchars($rida['probleem']));
                    ?>
                </td>
                <td>
                    <?php if ($rida['kas_on_lahendatud']==1): ?>
                        <span style="color:green">Lahendatud</span>
                    <?php else: ?>
                        <form method="post" action="<?= $_SERVER['PHP_SELF'];?>">
                            <input type="hidden" name="action" value="solve">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'];?>">
                            <input type="hidden" name="id" value="<?= $rida['id'];?>">
                            <button type="submit">Märgi lahendatuks</button>
                        </form>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" action="<?= $_SERVER['PHP_SELF'];?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="id" value="<?= $rida['id']; ?>">
                        <button type="submit">Kustuta rida</button>
                    </form>
                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

    <p>
        <a href="<?= $_SERVER['PHP_SELF']; ?>?page=<?= $page - 1; ?>">
            Eelmine lehekülg
        </a>
        |
        <a href="<?= $_SERVER['PHP_SELF']; ?>?page=<?= $page + 1; ?>">
            Järgmine lehekülg
        </a>
    </p>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="resources/js/callcenter.js"></script>
    <script src="resources/js/bootstrap.min.js"></script>

    </div>

</body>

</html>
