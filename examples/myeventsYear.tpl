
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

    if (rex_addon::get('markitup')->isAvailable()) {

        $myeventsCurrentYear    =  (int)date("Y");
        $myeventsSelectedYear   =  (int)"REX_VALUE[1]";
        if (!$myeventsSelectedYear) {
            $myeventsSelectedYear =  $myeventsCurrentYear;
        }

?>
    <h4>Termine des folgenden Jahres anzeigen</h4>

    <label>Alle Veranstaltungen im Jahr:</label>
    <div class="entry-wrapper">
        <select class="form-control" name="REX_INPUT_VALUE[1]">
            <?php
                for($year = $myeventsCurrentYear -5; $year < $myeventsCurrentYear +10; $year ++) {
                    $selected =  "";
                    if((int)$year === (int)$myeventsSelectedYear) {
                        $selected =  "selected=\"selected\"";
                    }
                ?>
                <option value="<?php echo $year?>" <?php echo $selected?>><?php echo $year?></option>
            <?php }?>
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

        $myeventsList           =  array();
        $tableDates             =  rex_addon::get('myevents')->getProperty('table_dates');
        $tableContent           =  rex_addon::get('myevents')->getProperty('table_content');

        $myeventsSelectedYear   =  (int)"REX_VALUE[1]";

        # create mysql timestamps to get events by it's startdate
        $myeventsStartdate      =  ($myeventsSelectedYear -1) . "-12-31";
        $myeventsEnddate        =  ($myeventsSelectedYear +1) . "-01-01";

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
        # load data if start or end is
        # within selected year
        # order by startdate
        # --------------------------------
        $sql =  rex_sql::factory();
        $sql->setQuery( "select * from `" . $tableDates . "` " .
                "a left join `" . $tableContent . "` b on a.id = b.event_id " .
                "where (a.startdate between \"" . $myeventsStartdate . "\" and \"" . $myeventsEnddate . "\" ".
                "or a.enddate between \"" . $myeventsStartdate . "\" and \"" . $myeventsEnddate . "\") ".
                "and b.clang = " . $languageId . " order by a.startdate");

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
                        $myeventsTimeString =  date($timeFormat[$languageId], $time);
                    }

                    # this event date is an other year than the selected one, ignore
                    $myeventsYear =  (int)date("Y", $time);
                    if ($myeventsYear !== $myeventsSelectedYear) {
                        continue;
                    }

                    # store days again in an array, month-index is the key
                    # we might ignore here some dates of myeventsTimes
                    # cause they are in the past already...
                    # so it is easier to format later
                    $monthIndex =  ((int)date("n", $time) -1);
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
                    'myeventsAddContent'    =>  $sql->getValue('addcontent'),
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
                    <?php foreach ($myevents_list as $myevent) { ?>
                        <li class="myevents-wrapper">
                            <p class="myevent-dates">
                                <?php echo $myevent['myeventsDates']?><?php if($myevent['myeventsDisplayTime']) {?>, <?php echo $myevent['myeventsTimeString']?><?php }?>
                            </p>
                            <h3><?php echo $myevent['myeventsTitle']?></h3>
                            <?php if($myevent['myeventsLocal']) { ?>
                                <p><?php echo $myevent['myeventsLocal'] ?></p>
                            <?php }?>
                            <?php if($myevent['myeventsAddContent']) { ?>
                                <p>Kategorie <?php echo $myevent['myeventsAddContent'] ?></p>
                            <?php }?>
                            <div class="myevents-content">
                                <?php echo myEvents::returnMarkitup('textile', $myevent['myeventsContent']); ?>
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
        echo rex_view::warning('Dieses Modul benötigt das "markitup" Addon!');
    }
?>
