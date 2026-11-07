## Beschreibung
Das phpApp‑Modul bildet das offizielle Framework der phpApp‑Software. 
Es stellt die grundlegende Funktionalität bereit, auf der alle kompatiblen phpApp‑Module aufbauen.

### User‑Verwaltung
Effiziente Verwaltung von Benutzerkonten, Profilen und Zugangsdaten für eine klare und strukturierte Systemorganisation.

### Rechte‑Verwaltung
Feingranulare Steuerung von Berechtigungen, um sicherzustellen, dass jeder Nutzer nur auf die für ihn vorgesehenen Funktionen zugreifen kann.

### Sicherheitsmaßnahmen
Integrierte Schutzmechanismen wie Zugriffskontrollen, Validierungen und Monitoring sorgen für eine sichere und stabile Systemumgebung.

### Datenbank
Zuverlässige und performante Datenhaltung mit optimierten Abfragen für schnelle Ladezeiten und hohe Skalierbarkeit.

### Caching‑Management
Intelligente Zwischenspeicherung von Daten reduziert Serverlast und verbessert die Reaktionsgeschwindigkeit der gesamten Anwendung.

### Modul‑Verwaltung
Flexible Verwaltung einzelner Softwaremodule ermöglicht eine einfache Erweiterung, Aktualisierung und Anpassung des Systems.

### SEO‑Management
Zentrale Werkzeuge zur Optimierung von Suchmaschinen‑Relevanz sorgen dafür, dass Inhalte besser gefunden werden und die Sichtbarkeit der Anwendung nachhaltig steigt.

### Medienverwaltung (z. B. Bilder)
Eine strukturierte Verwaltung von Bildern und anderen Medien ermöglicht schnelles Hochladen, Organisieren und Bereitstellen visueller Inhalte im gesamten System.

### Statistiken
Aussagekräftige Analyse‑ und Auswertungsfunktionen liefern wertvolle Einblicke in Nutzung, Performance und Benutzerverhalten, um fundierte Entscheidungen treffen zu können.

### Sprachverwaltung

### Navigation und Header
Die obere Warenkorb-/Kontoleiste bleibt beim Scrollen sichtbar und folgt dem
aktiven hellen oder dunklen Theme. Die Produktsuche sendet `D[SEARCH]` per GET an
`artikel`; Suchfilter bleiben Aufgabe des Shop-Moduls.

Unter **Frontend → Menüs** werden `top_navigation` (Hauptnavigation mit
Dropdowns) und `footer_navigation` (schlichte, vertikale Linkliste) gepflegt.
Beide unterstuetzen Untereintraege, Sprachtexte und den Aktiv-Schalter.
Die Migration `20261106144000_navigation` legt fehlende IDs an, ohne vorhandene
Menues zu ueberschreiben. Beide Menues starten leer und werden vom Benutzer gefuellt.
Die Save-Leiste der Menueverwaltung liegt ausserhalb des scrollbaren Inhalts im
Backend-Footer; sie bleibt sichtbar, reserviert ihren Platz und folgt dem Theme.
Beim Speichern bleiben nicht gesendete Menuefelder unveraendert. Ein explizites
`Json: []` leert den Baum; nur `__DELETE__` entfernt die gesamte Navigation.

### Themes
Unter **Einstellungen → Template → Frontend** werden Theme (Basis oder Ableitung)
und eigenes CSS je Haupt-Theme eingestellt. Zum Testen stehen `basic` und
`basic__dark_mode` bereit; die Core-Auswahl wird unabhängig davon gespeichert.

Die Quellen liegen in `system/template/theme/`. Core veröffentlicht die aktiven
statischen Dateien unter `data_c/public/theme/` und liefert sie über
`/file/theme/<anbieter-id>/<theme>/…?v=<version>` aus. Jeder Theme-Ordner
enthält nur den aktuellen Stand; Änderungen ersetzen ihn nach erfolgreichem Build.
Bibliotheken sind mit ihren Versionen unter `vendor/`
abgelegt, die Ladereihenfolge steht in `theme.json`. Unter
**Einstellungen → Template → Frontend → Performance** lassen sich CSS-Minimierung,
JavaScript-Minimierung und CSS-Bündelung getrennt einschalten. Diese
Schalter gelten nur für den jeweiligen Theme-Anbieter und sind
standardmäßig aus. Frontend und Core speichern ihre Auswahl in der eigenen
Moduldatenbank; Frontend-Optimierung verändert die Admin-Assets nicht.
Eine Variantenwahl durch Besucher folgt in einer späteren Etappe.

## Installationsanleitung
Bei lokal weiterentwickelten Modulen koennen Composers installierte Paketmetadaten
noch den alten Stand ohne PSR-4-Zuordnung enthalten. Der Loader deklariert deshalb
`fremeo\frontend\` zusaetzlich in seiner `composer.json`. Nach einer Aenderung daran
`composer dump-autoload --no-scripts --no-plugins` im Projekt ausfuehren.
Die Tests verwenden denselben Composer-Autoloader wie die Anwendung, ohne die
Frontend-Klasse separat einzubinden.

Für die Installation des phpApp‑Systems wird der phpApp Loader benötigt. Dieser muss als erstes auf dem System eingerichtet werden. Beim Ausführen installiert der Loader automatisch das phpApp‑Framework, legt alle notwendigen Standardwerte an und erzeugt einen initialen Administrator‑Zugang.

Die eigentliche Installation erfolgt über die Datei setup.php, die im Root‑Verzeichnis abgelegt und anschließend über den Browser aufgerufen wird. Der Installationsprozess läuft vollständig automatisiert ab.

Nach erfolgreicher Einrichtung meldet man sich im Backend an und ändert das Standardpasswort des Administrator‑Kontos, um die Sicherheit des Systems zu gewährleisten.

Die Grundinstallation entspricht einer Art „Stammzelle“. Erst durch die individuelle Installation der benötigten Module entwickelt sich das System zu der Lösung, die für das jeweilige Projekt vorgesehen ist.
Die Modulinstallation erfolgt bequem über das Backend. Es stehen verschiedene Module zur Verfügung, die – sofern nicht anders definiert – frei miteinander kombiniert werden können. So lassen sich beispielsweise die Module Blog, Page und Shop gemeinsam nutzen, um eine funktionsreiche Website aufzubauen.