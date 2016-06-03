<?php

    $myevents_error     =  [];
    $myevents_message   =  [];

    # prepare some request vars
    $func =  strip_tags(rex_request('func', 'string'));

    $myEventsBackup =  new myEventsBackup();
    if ($func === "do_bak") {

        $success =  $myEventsBackup->createBackup();
        if ($success) {
            $myevents_message   =  $myEventsBackup->message;
        } else {
            $myevents_error     =  $myEventsBackup->error;
        }
    } elseif ($func === "del_bak") {

        if ($myEventsBackup->deleteBackup()) {
            $myevents_message   =  $myEventsBackup->message;
        } else {
            $myevents_error     =  $myEventsBackup->error;
        }
    }
    $myevents_baklist =  $myEventsBackup->listBackups();
?>
<div>
    <?php
        # errors or confirm
        if (count($myevents_error)) {
            echo rex_view::error( implode('<br>', $myevents_error)  );
        }
        if (count($myevents_message)) {
            echo rex_view::info( implode('<br>', $myevents_message) );
        }
    ?>
    <div class="panel panel-default">
        <header class="panel-heading">
            <div class="panel-title">MyEvents Hilfe</div>
        </header>
        <div class="panel-body">
            <div class="myevents-chapter">
                <h3>Was macht MyEvents?</h3>
                <p>
                    Mit MyEvents lassen sich mehrsprachig Veranstaltungstermine einpflegen. Dabei kann jede Veranstaltung mehrere Termine haben. Es lassen sich also z.B. solche Ausgaben erzeugen:
                </p>

                <div class="myevents-example">
                    <p class="myevent-dates">22, 23 November und 02, 03, 06 Dezember, 20:15 Uhr</p>
                    <h4>Wagner - Tristan und Isolde</h4>
                    <p>
                        Violeta Urmana als Isolde<br>
                        Dirigent Zubin Mehta<br>
                        Orchestra e Coro del Teatro di San Carlo
                    </p>
                    <p><i>Madrid, Teatro Réal</i><p>
                </div>

                <p>
                    Unter dem Link <a href="<?php echo rex_be_controller::getPageObject('myevents/event_list')->getHref()?>">MyEvents Event Liste</a> sind die Veranstaltungen pro Jahr aufgelistet. Sie können dort zur Bearbeitung ausgewählt oder gelöscht werden.
                    Unter dem Link <a href="<?php echo rex_be_controller::getPageObject('myevents/event_add')->getHref()?>">MyEvents Neuer Event</a> kann man neue Veranstaltungen anlegen. Dorthin gelangt man auch, wenn man eine Veranstaltung aus der Liste bearbeiten möchte.
                    Dann kann man diese entweder als neue Veranstaltung anlegen, oder die Veränderungen speichern.
                </p>
            </div>

            <div class="myevents-chapter">
                <h3>Module erstellen</h3>
                <p>
                    Die Veranstaltungen können in beliebiger Form aus der Datenbank geholt werden.
                    Im Order &quot;examples&quot; finden Sie dafür 2 Beispiele.
                    Mit einem Modul können Sie die Veranstaltungen für ein bestimmtes  Jahr ausgeben.
                    Weiter finden Sie ein Modul, um die kommenden Veranstaltungen für die nächsten 1-6 Monate anzuzeigen.
                    Dabei werden für eine Veranstaltung nur noch die Termine angezeigt, die noch nicht vorbei sind.
                </p>
                <p>
                    Die Module sind beispielhaft für 2 Sprachen angelegt. Da ja multiple Termine wie <strong>02, 04, 05 Januar</strong> erzeugt werden können, geschieht die Formatierung in den Modulen nicht über <strong>date()</strong>,
                    statt dessen sind Monatsnamen etc. in Arrays hinterlegt.
                </p>
            </div>

            <div class="myevents-chapter">
                <h3>Backup erstellen</h3>
                <p>
                    Eine Deinstallation dieses Plugins löscht auch die Datenbank Tabellen. Sie können aber Backups der betroffenen Tabellen erstellen,
                    die im folgenden Verzeichnis gezippt abgelegt werden:
                </p>
                <p><i><?php echo rex_path::addonData('myevents') . "sqldump"?></i></p>
                <p>
                    Diese können Sie über FTP herunterladen und z.B per phpMyAdmin wieder importieren.
                    Bei Fehlermeldung können Sie das Backup natürlich ebenso über den Export von phpMyAdmin oder die Console machen.
                </p>
                <p><a href="<?php echo rex_url::currentBackendPage()?>&func=do_bak">Jetzt ein Backup erstellen</a></p>

                <?php if ($myevents_baklist): ?>
                    <h3>Verfügbare Backups, können per FTP heruntergeladen werden</h3>
                    <ul>
                        <?php foreach($myevents_baklist as $bak): ?>
                            <li><?php echo $bak ?></li>
                        <?php endforeach?>
                    </ul>
                    <p><a href="<?php echo rex_url::currentBackendPage()?>&func=del_bak">Jetzt alle Backups löschen</a></p>
                <?php endif?>
            </div>
        </div>
    </div>
</div>



