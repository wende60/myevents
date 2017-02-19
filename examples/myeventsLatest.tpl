
<!-- //////////// in ////////////////// -->

<?php
    /**
     * Addon MyEvents
     *
     * module latest events
     * example to display upcoming events in 2 languages like de and en
     * instead of language arrays you could use the addon opf_lang
     *
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     */

    if (rex_addon::get('markitup')->isAvailable()) {
?>

    <h4>Aktuelle Termine anzeigen</h4>

    <label>Anzahl Veranstaltungen:</label>
    <div class="entry-wrapper">
        <select class="form-control" name="REX_INPUT_VALUE[1]">
            <option value="">Alle Veranstaltungen</option>
            <?php
                for($i = 1; $i < 11; $i ++):
                    $selected =  "";
                    if((int)$i === (int)"REX_VALUE[1]") {
                        $selected =  "selected=\"selected\"";
                    }
                ?>
                <option value="<?php echo $i?>" <?php echo $selected?>><?php echo $i?> Veranstaltung<?php if($i > 1):?>en<?php endif?></option>
            <?php endfor ?>
        </select>
    </div>

    <label>Zeitraum in Monaten:</label>
    <div class="entry-wrapper">
        <select class="form-control" name="REX_INPUT_VALUE[2]">
            <?php
                for($j = 1; $j < 7; $j ++):
                    $selected =  "";
                    if((int)$j === (int)"REX_VALUE[2]") {
                        $selected =  "selected=\"selected\"";
                    }
                ?>
                <option value="<?php echo $j?>" <?php echo $selected?>><?php echo $j?> Monat<?php if($j > 1):?>e<?php endif?></option>
            <?php endfor ?>
        </select>
    </div>

<?php
    } else {
        echo rex_view::warning('Dieses Modul benötigt das "markitup" Addon!');
    }
?>

<!-- //////////// out ///////////////// -->

<?php

    $languageId =  rex_clang::getCurrentId();
    if (rex_addon::get('markitup')->isAvailable()) {

        $myeventsList       =  array();
        $tableDates         =  rex_addon::get('myevents')->getProperty('table_dates');
        $tableContent       =  rex_addon::get('myevents')->getProperty('table_content');

        $myeventsMaximum    =  strlen("REX_VALUE[1]")? (int)"REX_VALUE[1]" : 0;
        $myeventsMonths     =  (int)"REX_VALUE[2]";

        $periodTimeStart    =  time();
        $periodMonthEnd     =  (int)date("n", $periodTimeStart)  + $myeventsMonths;
        $periodTimeEnd      =  mktime (date("H", $periodTimeStart), date("i", $periodTimeStart), date("s", $periodTimeStart), $periodMonthEnd, date("j", $periodTimeStart), date("Y", $periodTimeStart));

        # create mysql timestamps to get events by it's enddate
        $myeventsStartDate  =  date("Y-m-d", $periodTimeStart);
        $myeventsEndDate    =  date("Y-m-d", $periodTimeEnd);

        # display month-names as string
        $monthNames =  array(
            1 => array('Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember',),
            2 => array('January','February','March','April','May','June','July','August','September','October','November','December',),
        );
        $timeFormat =  array(
            1 => 'H:i \U\h\r',
            2 => 'h:i a',
        );
        $andStringLocalized =  array(
            1 => 'und',
            2 => 'and',
        );

        # --------------------------------
        # load data from given year
        # ordered by startdate
        # --------------------------------
        $sql =  rex_sql::factory();
        $sql->setQuery( "select * from `" . $tableDates . "` " .
                "a left join `" . $tableContent . "` b on a.id = b.event_id " .
                "where (a.enddate > \"" . $myeventsStartDate . "\" and a.startdate < \"" . $myeventsEndDate . "\") " .
                "and b.clang = " . $languageId . " order by a.startdate");

        if ($sql->getRows() > 0 ) {

            # loop rows
            for ($i = 1; $i <= $sql->getRows(); $i++) {

                $myeventsDates      =  "";
                $myeventsDatesArray =  array();
                $myeventsHour       =  false;
                $myeventsMinutes    =  false;
                $myeventsTimeString =  false;
                $myeventsSortKey    =  false;

                # turn Unix-Timestamps-string into parts
                $myeventsTimes =  explode(",", $sql->getValue('dates'));
                foreach($myeventsTimes as $time) {

                    # ignore all dates of an event in the past or future
                    if ((int)$time < $periodTimeStart || (int)$time > $periodTimeEnd) {
                        continue;
                    }

                    # these data we need to get only once
                    if ($myeventsHour === false) {

                        $myeventsHour       =  date("G", $time);
                        $myeventsMinutes    =  date("i", $time);
                        $myeventsYear       =  date("Y", $time);
                        $myeventsTimeString =  date($timeFormat[$languageId], $time);

                        # use the first upcomming date of an event as sort-key
                        $myeventsSortKey    =  $time + $i;
                    }

                    # store days again in an array, month-index is the key
                    # we might ignore here some dates of myeventsTimes
                    # cause they are in the past already...
                    # so it is easier to format later
                    $monthIndex                         =  ((int)date("n", $time) -1);
                    $myeventsDatesArray[$monthIndex][]  =  date("d", $time);
                }

                # create formatted day(s) strings depending on language
                # we do not use "date" cause we might deal with multiple dates...
                foreach($myeventsDatesArray as $month_idx => $days) {

                    switch($languageId) {
                        case 1:
                            $myeventsDates .= (strlen($myeventsDates)? " " . $andStringLocalized[$languageId] . " " : "");
                            $myeventsDates .= implode(", ", $days) . " " . $monthNames[$languageId][$month_idx];
                            break;
                        default:
                            $myeventsDates .= (strlen($myeventsDates)? " " . $andStringLocalized[$language_id] . " " : "");
                            $myeventsDates .= $monthNames[$language_id][$month_idx] . " " . implode(", ", $days);
                    }
                }

                # you never know...
                if ($myeventsSortKey) {
                    $myeventsList[$myeventsSortKey] =  array(
                        'myeventsId'            =>  $sql->getValue('id'),
                        'myeventsDates'         =>  $myeventsDates,
                        'myeventsTimes'         =>  $myeventsTimes,
                        'myeventsHour'          =>  $myeventsHour,
                        'myeventsMinutes'       =>  $myeventsMinutes,
                        'myeventsYear'          =>  $myeventsYear,
                        'myeventsTimeString'    =>  $myeventsTimeString,
                        'myeventsTitle'         =>  $sql->getValue('title'),
                        'myeventsLocal'         =>  $sql->getValue('local'),
                        'myeventsContent'       =>  $sql->getValue('content'),
                        'myeventsDisplayTime'   =>  $sql->getValue('dpltime'),
                        'myeventsAddContent'    =>  $sql->getValue('addcontent'),

                    );
                }

                # next result row
                $sql->next();
            }

            # sort by first upcomming date
            ksort($myeventsList);
        } else {
            $myeventsList =  false;
        }

        # ---------------------------------
        # output
        # ---------------------------------
    ?>
        <div class="myevents-container">
            <?php if ($myeventsList) { ?>
                <ul class="myevents-list">
                    <?php
                        $eventCounter =  1;
                        foreach ($myeventsList as $myevent) {

                            # all we need only
                            if ($myeventsMaximum && $eventCounter > $myeventsMaximum) {
                                break;
                            }
                            $eventCounter ++;
                    ?>
                        <li class="myevents-wrapper">
                            <p class="myevent-dates">
                                <?php echo $myevent['myeventsDates']?>
                                <?php if ($myevent['myeventsDisplayTime']) {?>,
                                    <?php echo $myevent['myeventsTimeString']?>
                                <?php }?>
                            </p>
                            <h3><?php echo $myevent['myeventsTitle']?></h3>
                            <?php if ($myevent['myeventsLocal']) { ?>
                                <p><?php echo $myevent['myeventsLocal'] ?></p>
                            <?php } ?>
                            <?php if($myevent['myeventsAddContent']) { ?>
                                <p>Kategorie <?php echo $myevent['myeventsAddContent'] ?></p>
                            <?php }?>
                            <div class="myevents-content">
                                <?php echo myEvents::returnMarkitup('textile', $myevent['myeventsContent']); ?>
                            </div>
                        </li>
                    <?php }?>
                </ul>
            <?php } ?>
        </div>
    <?php
        # ---------------------------------
        # end output
        # --------------------------------

        # no textile
    } else {
        echo rex_view::warning('Dieses Modul benötigt das "markitup" Addon!');
    }
?>
