<?php

    /**
     * Addon MyEvents
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     * @version $Id: events_list.php, v 2.1.0
     */
    $myevents_error     =  [];
    $myevents_message   =  [];

    # use current or selected year
    $myeventsCurrentYear       =  (int)date("Y");
    $myeventsSelectedYear      =  (int)htmlspecialchars(rex_request('myevents_year', 'string'));
    if (!$myeventsSelectedYear || $myeventsSelectedYear === 0) {
        $myeventsSelectedYear  =  $myeventsCurrentYear;
    }

    # prepare some request vars
    $func               =  strip_tags(rex_request('func', 'string'));

    # create mysql timestamps to get events by it's startdate
    $myevents_startdate =  ($myeventsSelectedYear) . "-01-01";
    $myevents_enddate   =  ($myeventsSelectedYear) . "-12-31";

    # display month-names as string
    $month_names        =  $this->getProperty('month_names');

    if ($func == "do_delete") {
        $myevents_id    =  htmlspecialchars(rex_request('myevents_id', 'string'));
        if ($myevents_id && is_numeric($myevents_id)) {
            $sql_dates  =  rex_sql::factory();
            //echo "delete `" . $table_dates . "`,`" . $table_content . "` from `" . $table_dates . "`, `" . $table_content . "` where `" . $table_dates . ".id` = `" . $table_content . ".event_id` and `" . $table_dates . ".id` = " . $myevents_id;
            $sql_dates->setQuery(
                "delete " .
                "`" . $this->getProperty('table_dates') . "`, " .
                "`" . $this->getProperty('table_content') . "` from " .
                "`" . $this->getProperty('table_dates') . "`, " .
                "`" . $this->getProperty('table_content') . "` where " .
                $this->getProperty('table_dates') . ".id = " .
                $this->getProperty('table_content') . ".event_id and " .
                $this->getProperty('table_dates') . ".id = " . $myevents_id );

            if ($sql_dates->getRows() > 0 ) {
                array_push($myevents_message, 'Event ID' . $myevents_id . ': ' . $sql_dates->getRows() . '  Zeilen gelöscht!');
            } elseif ($sql_dates->error) {
                array_push($myevents_error, 'DB-Fehler ' . $sql_dates->getError());
            }
        }
    }

    # --------------------------------
    # load data from given year
    # ordered by startdate
    # --------------------------------
    $sql_dates = rex_sql::factory();
    $sql_dates->setQuery(
        "select * from `" . $this->getProperty('table_dates') . "` " .
        "a left join `" . $this->getProperty('table_content') . "` " .
        "b on a.id = b.event_id where a.startdate between \"" . $myevents_startdate . "\" and \"" . $myevents_enddate . "\" " .
        "and b.clang = " . rex_clang::getCurrentId() .
        " order by a.startdate");

    if ($sql_dates->getRows() > 0 ) {

        # loop rows
        for ($i = 1; $i <= $sql_dates->getRows(); $i++) {

            $myevents_dates     =  '';
            $myevents_hour      =  false;
            $myevents_min       =  false;
            $myevents_month       =  false;
            $past_event_month   =  false;
            $past_event_year    =  false;

            # turn Unix-Timestamps-string into parts
            $myevents_times =  explode(",", $sql_dates->getValue('dates'));
            foreach($myevents_times as $time) {

                # these data we need to get only once
                if (!$myevents_hour) {
                    $myevents_hour  =  date("G", $time);
                    $myevents_min   =  date("i", $time);
                }
                # create dates string with month names
                $myevents_month =  ((int)date("n", $time) -1);
                $myevents_date  =  date("d", $time);
                $myevents_year  =  date("Y", $time);

                if ($myevents_year !== $past_event_year) {
                    $myevents_dates .= (strlen($myevents_dates)? ' und ' : '') .
                                        $myevents_year . ' - ' .
                                        $month_names[$myevents_month] . ' ' .
                                        $myevents_date;
                } elseif ($myevents_month !== $past_event_month) {
                    $myevents_dates .= (strlen($myevents_dates)? ' und ' : '') .
                                        $month_names[$myevents_month] . ' ' .
                                        $myevents_date;
                } else {
                    $myevents_dates .= ", " . date("d", $time);
                }

                $past_event_month   =  $myevents_month;
                $past_event_year    =  $myevents_year;
            }

            $myevents_list[$sql_dates->getValue('id')] =  array(
                'myevents_dates'  =>  $myevents_dates,
                'myevents_times'  =>  $myevents_times,
                'myevents_hour'   =>  $myevents_hour,
                'myevents_min'    =>  $myevents_min,
                'myevents_title'  =>  $sql_dates->getValue('title'),
            );
            $sql_dates->next();
        }

    } else {
        array_push($myevents_error, 'Keine Veranstaltungen im Jahr ' . $myeventsSelectedYear . ' gefunden!');
        $myevents_id = false;
    }

?>
<div>
    <?php
        # we need textile to format descriptions
        if ( !rex_addon::get('markitup')->isAvailable() ) {
            echo rex_view::warning('Dieses Modul benötigt das "markitup" Addon!');
        }
        # errors or confirm
        if (count($myevents_error)) {
            echo rex_view::error( implode('<br>', $myevents_error)  );
        }
        if (count($myevents_message)) {
            echo rex_view::info( implode('<br>', $myevents_message) );
        }
    ?>
    <form action="<?php echo rex_url::currentBackendPage()?>" method="post" class="rex-form">
        <div class="panel panel-edit">
            <header class="panel-heading">
                Alle Events in <?php echo $myeventsSelectedYear?>
            </header>
            <div class="panel-body">
                <fieldset>
                    <dl class="rex-form-group form-group">
                        <dt>
                            <label class="control-label">Jahr anzeigen</label>
                        </dt>
                        <dd>
                            <select class="form-control" name="myevents_year">
                                <?php
                                    for($year = $myeventsCurrentYear -1; $year < $myeventsCurrentYear +6; $year ++) {
                                        $selected =  ($year === $myeventsSelectedYear)? "selected" : "";
                                ?>
                                    <option value="<?php echo $year?>" <?php echo $selected?>><?php echo $year?></option>
                                <?php } ?>
                            </select>
                        </dd>
                    </dl>
                </fieldset>
            </div>
            <footer class="panel-footer">
                <div class="rex-form-panel-footer">
                    <div class="btn-toolbar">
                        <button value="sel" name="select_year" type="submit" class="btn btn-save rex-form-aligned">Jahr ändern</button>
                    </div>
                </div>
            </footer>
        </div>
    </form>
    <div class="myevents-list-wrapper">
        <ul>
            <?php foreach($myevents_list as $myevents_id => $myevents_data): ?>
                <li>
                    <h5>
                        Event-ID <?php echo $myevents_id?>
                    </h5>
                    <h3>
                        <?php echo $myevents_data['myevents_title']?>
                    </h3>
                    <p>
                        <?php echo $myevents_data['myevents_dates']?>,
                        jeweils <?php echo $myevents_data['myevents_hour']?>:<?php echo $myevents_data['myevents_min']?> Uhr
                    </p>
                    <p>
                        <a class="myevents-edit-link" href="<?php echo rex_be_controller::getPageObject('myevents/event_add')->getHref()?>&myevents_id=<?php echo $myevents_id?>">
                            <span>[Event ID <?php echo $myevents_id?> bearbeiten]</span>
                        </a>
                        <a class="myevents-delete-link" href="<?php echo rex_url::currentBackendPage()?>&func=do_delete&myevents_id=<?php echo $myevents_id?>&myevents_year=<?php echo $myeventsSelectedYear?>" onclick="return confirm('Termin ID <?php echo $myevents_id?> löschen?')">
                            <span>[Event ID <?php echo $myevents_id?> löschen]</span>
                        </a>
                    </p>
                </li>
            <?php endforeach?>
        </ul>
    </div>
</div>

