<?php

    $myevents_times     =  [];
    $myevents_error     =  [];
    $myevents_startdate =  false;
    $myevents_enddate   =  false;
    $complete           =  false;

    # prepare some request vars
    $func               =  strip_tags(rex_request('func', 'string'));
    $myevents_dates     =  strip_tags(rex_request('myevents_dates', 'string'));
    $myevents_hour      =  (int)strip_tags(rex_request('myevents_hour', 'string'));
    $myevents_min       =  (int)strip_tags(rex_request('myevents_min', 'string'));
    $myevents_id        =  strip_tags(rex_request('myevents_id', 'string'));
    $myevents_dpltime   =  strlen(rex_request('myevents_dpltime', 'string'))? 1 : 0;

    # confirm update and available event-id... do sql-update
    $confirm_upd        =  (isset($_REQUEST['confirm_upd']) && $myevents_id)? true : false;

    $myevents_title     =  [];
    $myevents_content   =  [];
    $myevents_local     =  [];
    $myevents_languages =  [];

    foreach(rex_clang::getAll() as $langKey => $langObj) {
        $myevents_title[$langKey]      =  strip_tags(rex_request('myevents_title_' . $langKey, 'string'));
        $myevents_content[$langKey]    =  htmlspecialchars(rex_request('myevents_content_' . $langKey, 'string'));
        $myevents_local[$langKey]      =  strip_tags(rex_request('myevents_local_' . $langKey, 'string'));

        array_push($myevents_languages, $langObj->getValue('name'));
    }


    # --------------------------------
    # no data
    # or data from request
    # --------------------------------
    if ($func === "is_send") {

        # check dates-string
        if (!$myevents_dates) {
            array_push($myevents_error, 'Bitte Termine eingeben!');
        } else {

            $event_timestrings =  explode(",", $myevents_dates);
            foreach($event_timestrings as $event_timestring) {
                $time   =  trim($event_timestring/1000);
                $day    =  date('j', $time);
                $mon    =  date('m', $time);
                $year   =  date('Y', $time);
                array_push($myevents_times, mktime ($myevents_hour, $myevents_min, 0, $mon, $day, $year));
            }
            # order dates
            sort($myevents_times);

            # get first and last date of all event-dates and create mysql timestamp
            $myevents_startdate  =  date("Y-m-d", $myevents_times[0]);
            $myevents_enddate    =  date("Y-m-d", $myevents_times[count($myevents_times) -1]);

        }
        # do we have a title for each language?
        foreach(rex_clang::getAll() as $langKey => $langObj) {
            if (!$myevents_title[$langKey]) {
                array_push($myevents_error, 'Bitte Veranstaltungs-Titel in ' . $langObj->getValue('name') . ' eingeben!');
            }
        }

        # if no error write to db
        if (!count($myevents_error)) {

            $myEventsCreate     =  new myEvents();

            # insert/update values for table myevents_dates
            $insert_array_dates =  array(
                "id"            => array(9, 0),
                "startdate"     => array(0, $myevents_startdate),
                "enddate"       => array(0, $myevents_enddate),
                "dates"         => array(0, implode(',', $myevents_times)),
                "online"        => array(1, 1),
                "dpltime"       => array(1, $myevents_dpltime),
                "adddates"      => array(0, ""),
            );

            $sql_dates = rex_sql::factory();
            if ($confirm_upd) {
                $sql_dates->setQuery( $myEventsCreate->createUpdateQuery($insert_array_dates, $this->getProperty('table_dates'), "`id`=" . $myevents_id) );
            } else {
                $sql_dates->setQuery( $myEventsCreate->createInsertQuery($insert_array_dates, $this->getProperty('table_dates')) );
                $myevents_id =  $sql_dates->getLastId();
            }

            # dates insert went fine
            # continue inserting contents for each language
            if (!$sql_dates->getError()) {

                foreach(rex_clang::getAll() as $langKey => $langObj) {

                    # insert/update values for table myevents_content
                    $insert_array_content =  array(
                        "cid"           => array(9, 0),
                        "event_id"      => array(2, $myevents_id),
                        "clang"         => array(1, $langKey),
                        "title"         => array(0, $myevents_title[$langKey]),
                        "content"       => array(0, $myevents_content[$langKey]),
                        "local"         => array(0, $myevents_local[$langKey]),
                        "addcontent"    => array(0, ""),
                    );

                    $sql_content =  rex_sql::factory();
                    if ($confirm_upd) {
                        $sql_content->setQuery( $myEventsCreate->createUpdateQuery($insert_array_content, $this->getProperty('table_content'), "`event_id`=" . $myevents_id . "&&`clang`=" . $langKey) );
                    } else {
                        $sql_content->setQuery( $myEventsCreate->createInsertQuery($insert_array_content, $this->getProperty('table_content')) );
                    }

                    # something went wrong inserting contents
                    if ($sql_content->getError()) {
                        array_push($myevents_error, 'DB-Fehler ' . $sql_content->getError());
                        break;
                    }
                }

            # something went wrong inserting dates
            } else {
                array_push($myevents_error, 'DB-Fehler ' . $sql_dates->getError());
            }

            #jep! all done
            if (!count($myevents_error)) {
                $complete =  true;
            }
        }
    # --------------------------------
    # load data from given id
    # --------------------------------
    } elseif ($myevents_id) {

        # get data from table myevents_dates
        $sql = rex_sql::factory();
        $sql->setQuery( "select * from `" . $this->getProperty('table_dates') . "` a left join `" . $this->getProperty('table_content') . "` b on a.id = b.event_id where a.id = " . $myevents_id );

        if ($sql->getRows() > 0 ) {

            $myevents_dates =  "";
            for ($i = 1; $i <= $sql->getRows(); $i++) {

                # we need to get this from the first row only
                if (!$myevents_dates) {

                    $myevents_times     =  explode(",", $sql->getValue('dates'));
                    foreach($myevents_times as $key => $time) {

                        // get hour and minutes in the first loop
                        if ($key === 0) {
                            $myevents_hour  =  (int)date("G", $time);
                            $myevents_min   =  (int)date("i", $time);
                        }
                        // create timestamps without hour and minutes for datepicker date selection
                        $myevents_dates .= ($myevents_dates? "," : "") .
                                            mktime(0, 0, 0, date("m", $time), date("j", $time), date("Y", $time)) * 1000;
                    }
                }

                # get current language and create vars
                $lang_key                       =  $sql->getValue('clang');
                $myevents_dpltime               =  (int)$sql->getValue('dpltime');
                $myevents_title[$lang_key]      =  $sql->getValue('title');
                $myevents_content[$lang_key]    =  $sql->getValue('content');
                $myevents_local[$lang_key]      =  $sql->getValue('local');
                $myevents_additional[$lang_key] =  $sql->getValue('addcontent');

                $sql->next();
            }
        } else {
            array_push($myevents_error, 'Die ID ' . $myevents_id . ' ist nicht verfügbar!');
            $myevents_id = false;
        }
    }

?>

<div>
    <?php
        # we need textile to format descriptions
        if (!rex_addon::get('textile')->isAvailable()) {
            echo rex_view::warning('Dieses Modul benötigt das "textile" Addon!');
        }
        # errors or confirm
        if ($func && !count($myevents_error)) {
            echo rex_view::info($this->i18n('event_saved'));
        } elseif (count($myevents_error)) {
            echo rex_view::error( implode('<br>', $myevents_error) );
        }
    ?>
    <form action="<?php echo rex_url::currentBackendPage()?>" method="post" class="rex-form">
        <input type="hidden" name="myevents_id" value="<?php echo $myevents_id?>" />
        <input type="hidden" name="func" value="is_send" />
        <div class="panel panel-edit">
            <header class="panel-heading">
                Event in <?php echo implode(", ", $myevents_languages) ?> anlegen oder verändern
            </header>
            <div class="panel-body">
                <fieldset>
                    <dl class="rex-form-group form-group">
                        <dt>
                            <label class="control-label">Termine und Uhrzeit</label>
                        </dt>
                        <dd>
                            <div class="myevents-datepicker-wrapper" style="display:inline-block">
                                <input id="myevents-dates-entry" type="hidden" name="myevents_dates" value="<?php echo $myevents_dates?>" >
                                <div id="myEventsDatepicker"></div>
                            </div>
                            <div class="myevents-time-wrapper">
                                <div>
                                    <select class="form-control myevents-time" name="myevents_hour">
                                        <?php
                                            for($hour = 0; $hour < 24; $hour ++) {
                                                $selected =  ($hour === $myevents_hour)? "selected" : "";
                                        ?>
                                            <option value="<?php echo $hour?>" <?php echo $selected?>><?php echo ($hour < 10)? "0" . $hour : $hour ?></option>
                                        <?php } ?>
                                    </select> :
                                    <select class="form-control myevents-time" name="myevents_min">
                                        <?php
                                            for($min = 0; $min < 60; $min += 5) {
                                                $selected =  ($min === $myevents_min)? "selected" : "";
                                        ?>
                                            <option value="<?php echo $min?>" <?php echo $selected?>><?php echo ($min < 10)? "0" . $min : $min ?></option>
                                        <?php } ?>
                                    </select> Uhr
                                </div>
                                <div>
                                    <input id="myevents-dpltime-ck" type="checkbox" name="myevents_dpltime" value="1" <?php if ($myevents_dpltime === 1):?>checked<?php endif?> >
                                    <label for="myevents-dpltime-ck">Uhrzeit anzeigen</label>
                                </div>
                                <div class="myevents-display-dates">
                                    <h5>Ausgewählte Termine</h5>
                                    <ul id="myevents-dates-list"></ul>
                                </div>
                            </div>
                            <script type="text/javascript">
                                myEventsDatepicker.init({});
                                myEventsDatepicker.createCalendar('myEventsDatepicker', {
                                    onlyFuture: 0,
                                    eventEntry: 'myevents-dates-entry',
                                    eventList: 'myevents-dates-list'
                                });
                            </script>
                        </dd>
                    </dl>
                </fieldset>
                <?php foreach(rex_clang::getAll() as $langKey => $langObj) { ?>
                    <fieldset class="myevents-language-wrapper">
                        <dl class="rex-form-group form-group">
                            <dt>
                                <label class="control-label">Titel <?php echo $langObj->getValue('name')?></label>
                            </dt>
                            <dd>
                                <input class="form-control" type="text" name="myevents_title_<?php echo $langKey?>" value="<?php echo $myevents_title[$langKey]?>" />
                            </dd>
                        </dl>
                        <dl class="rex-form-group form-group">
                            <dt>
                                <label class="control-label">Ort <?php echo $langObj->getValue('name')?></label>
                            </dt>
                            <dd>
                                <input class="form-control" type="text" name="myevents_local_<?php echo $langKey?>" value="<?php echo $myevents_local[$langKey]?>" />
                                <p class="help-block rex-note">Optionales extra Feld für Ort etc.</p>
                            </dd>
                        </dl>
                        <dl class="rex-form-group form-group">
                            <dt>
                                <label class="control-label">Beschreibung <?php echo $langObj->getValue('name')?></label>
                            </dt>
                            <dd>
                                <textarea class="form-control" name="myevents_content_<?php echo $langKey?>" rows="6"><?php echo stripslashes($myevents_content[$langKey])?></textarea>
                                <p class="help-block rex-note">Textile Formatierung möglich</p>
                            </dd>
                        </dl>
                    </fieldset>
                <?php } ?>
            </div>
            <footer class="panel-footer">
                <div class="rex-form-panel-footer">
                    <div class="btn-toolbar">
                        <?php if ($myevents_id):?>
                            <button value="upd" name="confirm_upd" type="submit" class="btn btn-save rex-form-aligned">Änderungen an bestehendem Event speichern</button>
                            <button value="add" name="add_event" type="submit" class="btn btn-save">Als neuen Event speichern?</button>
                        <?php else: ?>
                            <button value="add" name="add_event" type="submit" class="btn btn-save rex-form-aligned">Neuen Event speichern</button>
                        <?php endif?>
                    </div>
                </div>
                <div class="rex-form-panel-footer">
                    <div class="btn-toolbar">
                        <p class="rex-form-aligned"><br><br><a href="<?php echo rex_url::currentBackendPage()?>">Zurück zu MyEvents neuen Event anlegen</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </form>
</div>
<?php

    // displays help options, mus be activated (user settings)
    rex_textile::showHelpOverview();

?>