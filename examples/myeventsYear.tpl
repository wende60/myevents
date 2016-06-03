
<!-- //////////// in ////////////////// -->

<?php
    /**
     * Addon MyEvents
     *
     * module events by year
     * example to display all events of a year in 2 languages like de and en
     * instead of language arrays you could use the addon opf_lang
     *
     * @author  kgde@wendenburg.de
     * @package redaxo 5
     */

    if (rex_addon::get('textile')->isAvailable()) {

        $currentYear    =  date("Y");
        $myeventsYear   =  (int)"REX_VALUE[1]";
        if (!$myeventsYear) {
            $myeventsYear =  $currentYear;
        }

?>
    <h4>Termine des folgenden Jahres anzeigen</h4>

    <label>Alle Veranstaltungen im Jahr:</label>
    <div class="entry-wrapper">
        <select name="REX_INPUT_VALUE[1]">
            <?php
                for($year = $currentYear -5; $year < $currentYear +10; $year ++) {
                    $selected =  "";
                    if((int)$year === (int)$myeventsYear) {
                        $selected =  "selected=\"selected\"";
                    }
                ?>
                <option value="<?php echo $year?>" <?php echo $selected?>><?php echo $year?></option>
            <?php }?>
        </select>
    </div>
<?php
    } else {
        echo rex_view::warning('Dieses Modul benötigt das "textile" Addon!');
    }
?>

<!-- //////////// out ///////////////// -->

<?php

    $languageId =  rex_clang::getCurrentId();
    if (rex_addon::get('textile')->isAvailable()) {

        $myeventsList       =  array();
        $tableDates         =  rex_addon::get('myevents')->getProperty('table_dates');
        $tableContent       =  rex_addon::get('myevents')->getProperty('table_content');

        $myeventsYear       =  (int)"REX_VALUE[1]";

        # create mysql timestamps to get events by it's startdate
        $myeventsStartdate  =  ($myeventsYear -1) . "-12-31";
        $myeventsEnddate    =  ($myeventsYear +1) . "-01-01";

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
        $sql->setQuery( "select * from `" . $tableDates . "` a left join `" . $tableContent . "` b on a.id = b.event_id where a.startdate between \"" . $myeventsStartdate . "\" and \"" . $myeventsEnddate . "\" and b.clang = " . $languageId . " order by a.startdate");

        if ($sql->getRows() > 0 ) {

            # loop rows
            for ($i = 1; $i <= $sql->getRows(); $i++) {

                $myeventsDates      =  "";
                $myeventsDatesArray =  array();
                $myeventsHour       =  false;
                $myeventsMinute     =  false;
                $myeventsMonth      =  false;
                $myeventsTimeString =  false;

                # turn Unix-Timestamps-string into parts
                $myeventsTimes =  explode(",", $sql->getValue('dates'));
                foreach($myeventsTimes as $time) {

                    # these data we need to get only once
                    if($myeventsHour === false) {
                        $myeventsHour       =  date("G", $time);
                        $myeventsMinute     =  date("i", $time);
                        $myeventsYear       =  date("Y", $time);
                        $myeventsTimeString =  date($timeFormat[$languageId], $time);
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
                            $myeventsDates .= (strlen($myeventsDates)? " " . $andStringLocalized[$languageId] . " " : ""); // add " and "
                            $myeventsDates .= implode(", ", $days) . " " . $monthNames[$languageId][$month_idx];
                            break;
                        default:
                            $myeventsDates .= (strlen($myeventsDates)? " " . $andStringLocalized[$languageId] . " " : ""); // add " and "
                            $myeventsDates .= $monthNames[$languageId][$month_idx] . " " . implode(", ", $days);
                    }
                }

                $myevents_list[$sql->getValue('id')] =  array(
                    'myeventsDates'         =>  $myeventsDates,
                    'myeventsTimes'         =>  $myeventsTimes,
                    'myeventsHour'          =>  $myeventsHour,
                    'myeventsMinute'        =>  $myeventsMinute,
                    'myeventsYear'          =>  $myeventsYear,
                    'myeventsTimeString'    =>  $myeventsTimeString,
                    'myeventsTitle'         =>  $sql->getValue('title'),
                    'myeventsLocal'         =>  $sql->getValue('local'),
                    'myeventsContent'       =>  $sql->getValue('content'),
                    'myeventsDisplayTime'   =>  $sql->getValue('dpltime'),
                );
                $sql->next();
            }

        } else {
            $myevents_list =  false;
        }


        # ---------------------------------
        # output
        # ---------------------------------
    ?>
        <div class="myevents-container">
            <?php if($myevents_list) { ?>
                <ul class="myevents-list">
                    <?php
                        foreach ($myevents_list as $myevent) {

                            # allow html, chars must be decoded
                            # replace br-tags
                            # replace "\r"
                            # replace leading whitespace after double line-break
                            #
                            $textile = htmlspecialchars_decode($myevent['myeventsContent']);
                            $textile = str_replace("<br />","",$textile);
                            $textile = preg_replace("#\r#","",$textile);
                            $textile = preg_replace("#\n\s*\n\s*#","\n\n",$textile);
                            $textile = rex_textile::parse($textile);
                    ?>
                        <li class="myevents-wrapper">
                            <p class="myevent-dates">
                                <?php echo $myevent['myeventsDates']?><?php if($myevent['myeventsDisplayTime']) {?>, <?php echo $myevent['myeventsTimeString']?><?php }?>
                            </p>
                            <h3><?php echo $myevent['myeventsTitle']?></h3>
                            <?php if($myevent['myeventsLocal']) { ?>
                                <p><?php echo $myevent['myeventsLocal'] ?></p>
                            <?php }?>
                            <div class="myevents-content">
                                <?php echo $textile ?>
                            </div>
                        </li>
                    <?php }?>
                </ul>
            <?php }?>
        </div>
    <?php
        # ---------------------------------
        # end output
        # --------------------------------

        # no textile
    } else {
        echo rex_view::warning('Dieses Modul benötigt das "textile" Addon!');
    }
?>
