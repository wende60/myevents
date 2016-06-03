## Was macht MyEvents? ##

Mit MyEvents lassen sich mehrsprachig Veranstaltungstermine einpflegen.
Dabei kann jede Veranstaltung mehrere Termine haben. Es lassen sich also z.B. solche Ausgaben erzeugen:


*22, 23 November und 02, 03, 06 Dezember, 20:15 Uhr*

### Wagner - Tristan und Isolde ###

Violeta Urmana als Isolde
Dirigent Zubin Mehta
Orchestra e Coro del Teatro di San Carlo

*Madrid, Teatro Réal*

Die Veranstaltungen können in beliebiger Form aus der Datenbank geholt werden.
Im Order &quot;examples&quot; finden Sie dafür 2 Beispiele.
Mit einem Modul können Sie die Veranstaltungen für ein bestimmtes  Jahr ausgeben.
Weiter finden Sie ein Modul, um die kommenden Veranstaltungen für die nächsten 1-6 Monate anzuzeigen.
Dabei werden für eine Veranstaltung nur noch die Termine angezeigt, die noch nicht vorbei sind.

Die Module sind beispielhaft für 2 Sprachen angelegt. Da ja multiple Termine wie *02, 04, 05 Januar* erzeugt werden können,
geschieht die Formatierung in den Modulen nicht über *date()*,
statt dessen sind Monatsnamen etc. in Arrays hinterlegt.