# LernmodulePlugin

Stud.IP ist normalerweise kein Ort für Lernmodule, obwohl Stud.IP ein LMS (learning management system) ist. Da wird in der Regel auf Ilias verwiesen, das man mit Stud.IP koppeln kann. Aber auch mit diesem Plugin kann man ziemlich coole Lernmodule in Stud.IP einbauen.

Das Plugin bringt einen eigenen Editor für Lernmodule mit - den H5P-Editor. Die Buchstaben H5P tauchen zwar im Plugin für den Lehrenden selbst nicht auf, aber das ist ein quelloffener Editor für Lernmodule, der in Norwegen entwickelt worden ist und mithilfe dieses Plugins auch in Stud.IP funktioniert. Um den H5P-Editor zu aktivieren, muss man nach der Installation des Plugins unter Admin -> Standort -> H5P-Bibliotheken noch H5P-Bibliotheken hochladen und aktivieren. Das kann man machen, indem man H5P-Lernmodule hochlädt oder dieses ZIP: https://server2.data-quest.de/owncloud/index.php/s/PPUVpseooJr95XO

HTML-Lernmodule können aber auch mit externen Editoren wie Lectora, Articulate Storyline, easy Prof oder auch nur Keynote oder Libre Office Impress erstellt werden und als HTML Ordner gespeichert, gezippt und hier hochgeladen werden. Man kann aber auch genauso gut externe Webseiten einbinden. Und es gibt Repositorys im Internet wie https://learningapps.org/ , wo man frei verfügbare Lernmodule herunterladen kann (beim Beispiel learningapps.org als iBooks Author herunterladen).

Es gibt auch eine eigene Schnittstelle, um HTML-Lernmodule um einen Feedback-Kanal zu erweitern. Falls man ein wenig Programmierfähigkeit mitbringt, kann man das HTML-Lernmodul mit drei Codezeilen mit dem Stud.IP interagieren lassen.

Die Lernmodule lassen sich zeitsteuern (erst ab der zweiten Semesterwoche abspielbar machen) oder in Abhängigkeiten zueinander setzen (erst nach Modul x abspielen).

Ab Stud.IP 5.4 bietet das LernmodulePlugin neue, verbesserte Versionen von manchen existierenden Lernmodulen. Diese wurden auf Vue neu umgesetzt, um eine bessere Integration in Stud.IP zu gewährleisten.  Die Vue-Lernmodule stehen auch als Blöcke in dem Courseware zur Verfügung.

## Vue-Projektstruktur
Es gibt in diesem Plugin zwei Vue-Projekte: `vue/` (Vue 3) und `courseware-blocks-vue2/` (Vue 2). Die Viewer und Editor für die neuen Lernmodule sind in Vue 3 umgesetzt.  Da im Courseware und in dem Rest vom Stud.IP-Kern Vue 2 noch verwendet wird, sind die Courseware-Blöcke für die neuen Lernmodule notwendigerweise auf Vue 2 umgesetzt. Die Courseware-Blöcke binden die Lernmodule über Iframes ein.

## Localisation
Dieses Plugin wird derzeit über Weblate von Deutsch auf Englisch übersetzt.  Das Weblate-Projekt für das Plugin ist auf der Weblate-Instanz der Universität Vechta zu finden: https://weblate.uni-vechta.de/projects/rasmusfuhse-lernmoduleplugin/

### Verwaltung von Locale-Dateien
Die Übersetzung wird aus technischen Gründen in zwei Komponenten aufgeteilt: PHP und JS.  PHP-Strings kann man mit den Scripts `locale/extractTranslationStrings` und `locale/compileTranslation` extrahieren und kompilieren.  JS-Strings kann man analog mit den Befehlen `npm run gettext:extract` und `npm run gettext:compile` im `vue/`-Verzeichnis verwalten.

<a href="https://weblate.uni-vechta.de/engage/rasmusfuhse-lernmoduleplugin/en/">
<img src="https://weblate.uni-vechta.de/widgets/rasmusfuhse-lernmoduleplugin/en/php/svg-badge.svg" alt="Translation status" />
</a>

