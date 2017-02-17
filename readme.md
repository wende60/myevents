REDAXO-AddOn: MyEvents
=======================

Mit MyEvents lassen sich mehrsprachig Veranstaltungen mit einem oder mehreren Terminen einpflegen oder modifizieren.
Die einzelnen Termine einer Veranstaltung werden per Klick auf einen Datepicker angelegt oder wieder entfernt.

![Screenshot](https://cloud.githubusercontent.com/assets/15124946/16166431/7f754cc0-34eb-11e6-9779-ff78598d0796.png)

Es lassen sich also z.B. solche Ausgaben erzeugen:

----------------------------------------------------

*22, 23 November und 02, 03, 06 Dezember, 20:15 Uhr*

### Wagner - Tristan und Isolde ###

Violeta Urmana als Isolde
Dirigent Zubin Mehta
Orchestra e Coro del Teatro di San Carlo

*Madrid, Teatro Réal*

----------------------------------------------------


Die Veranstaltungen können in beliebiger Form aus der Datenbank geholt werden.
Im Order &quot;examples&quot; findest Du dafür 2 Beispielmodule, die mehrsprachig für deutsch und englisch angelegt sind.
Mit einem Modul kannst Du die Veranstaltungen für ein bestimmtes  Jahr ausgeben.
Weiter findest Du ein Modul, um die kommenden Veranstaltungen für die nächsten 1-6 Monate anzuzeigen.
Dabei werden für eine Veranstaltung nur noch die Termine angezeigt, die noch nicht in der Vergangenheit liegen.

Ein extra Feld kann genutzt werden, um die Events zu kategoriesieren oder Tags zu vergeben.

Das Datepicker-Javascript  (myevents-es6.js) ist in ECMAScript 6 geschrieben, und mit babel (preset es2005) nach ECMAScript 5 transpiled, so dass die meisten halbwegs aktuellen Browser damit klarkommen sollten.
Falls ältere Browser für das Backend zum Einsatz kommen sollte man ggf. bei Version 2.0.0 bleiben (Branch myEventsSimple)

----------------------------------------------------
Edit 27.2.16: Aus Kompatibilitätsgründen mit Rex 5.2/2 textile durch markitup ersetzt (beiden dürfen auf einem REX 5.2/3 System nicht parallel aktiv sein - REX 5.3 prüft das erstmalig korrekt 
