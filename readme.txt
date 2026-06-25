=== Altersverifikation – Age Gate für WooCommerce & WordPress ===
Contributors: kipphard
Tags: age verification, altersverifikation, age gate, jugendschutz, woocommerce
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

DSGVO-konformer Altersverifikations-Overlay (Age Gate) für WordPress und WooCommerce. Das Geburtsdatum wird ausschließlich client-seitig ausgewertet – keine personenbezogenen Daten verlassen den Browser.

== Description ==

**Altersverifikation – Age Gate für WooCommerce & WordPress** schützt deine Website oder bestimmte Seiten vor dem Zugriff minderjähriger Besucher. Ein Vollbild-Overlay erscheint, bevor Inhalte sichtbar werden. Der Besucher bestätigt das Mindestalter durch einfachen Klick oder durch Eingabe des Geburtsdatums.

**DSGVO-Besonderheit:** Die Altersberechnung erfolgt vollständig im Browser. Kein Geburtsdatum und keine personenbezogenen Daten werden an den Server übertragen. Das Plugin setzt nach Bestätigung nur ein funktionales Cookie (kein Tracking, keine Profilbildung).

**Fail-Safe-Ansatz:** Der Overlay ist per CSS standardmäßig sichtbar. JavaScript *entfernt* ihn, wenn ein gültiges Bestätigungs-Cookie vorliegt oder der Besucher bestätigt hat. Schlägt JavaScript fehl, bleiben die Inhalte geschützt.

**Was das Plugin kostenlos bietet:**

* Vollbild-Overlay mit anpassbarem Heading, Nachrichtentext und Button-Beschriftungen
* Zwei Verifikationsmodi: einfache Bestätigung (Ja/Nein) oder Geburtsdatum-Eingabe
* Geltungsbereich: gesamte Website oder nur ausgewählte Seiten (per ID)
* Anpassbare Overlay- und Akzentfarben sowie optionales Logo
* Funktionales Cookie zur Wiedererkennung (1–3650 Tage)
* Ablehnen-Aktion: Meldung anzeigen oder zu einer URL weiterleiten
* Keine externen Abhängigkeiten, kein Tracking, kein CDN

**Altersverifikation Pro:**

* WooCommerce-Integration: Gate nur für Produkte in bestimmten Kategorien aktivieren
* Geo-Targeting: Overlay nur für Besucher aus bestimmten Ländern (ISO-Codes)
* White-Label: Kipphard-Branding im Overlay ausblenden
* Eigenes CSS für vollständige Design-Kontrolle

*This plugin shows a full-screen age gate overlay before visitors can access content. Date-of-birth evaluation is done entirely client-side — no personal data is sent to the server. GDPR-compliant. WooCommerce per-category gating available in Pro.*

== Installation ==

1. Lade das Plugin-Verzeichnis `altersverifikation` in das `/wp-content/plugins/`-Verzeichnis hoch.
2. Aktiviere das Plugin unter "Plugins" in der WordPress-Administration.
3. Navigiere zu **Altersverifikation** im Admin-Menü und konfiguriere die Einstellungen.
4. Speichere die Einstellungen – der Overlay ist sofort aktiv.

== Frequently Asked Questions ==

= Ist das eine rechtssichere Altersprüfung? =

Nein. Dieser Overlay ist eine Bestätigungsschranke, die den Zugang für minderjährige Besucher erschwert und einen ehrlichen Hinweis gibt. Er ist jedoch keine harte Identitätsprüfung. Eine echte Altersverifikation erfordert ID-basierte Methoden (z. B. Personalausweis-Scan, PostIdent) oder Schätzungsverfahren. Solche Integrationen befinden sich auf der Pro-Roadmap. Für rechtlich kritische Inhalte (z. B. nach JuSchG regulierte Waren) empfehlen wir eine fachkundige Rechtsberatung.

= Werden personenbezogene Daten gespeichert oder übertragen? =

Nein. Das Geburtsdatum (Modus "Geburtsdatum eingeben") wird ausschließlich im Browser des Besuchers berechnet und nie an den Server gesendet. Nach Bestätigung wird lediglich ein anonymes funktionales Cookie (`avf_ok=1`) gesetzt.

= Was passiert, wenn JavaScript deaktiviert ist? =

Der Overlay bleibt sichtbar (Fail-Safe). Der Inhalt ist ohne JS nicht zugänglich.

= Funktioniert das Plugin mit WooCommerce? =

Die kostenlose Version gated die gesamte Website oder einzelne Seiten. Die Pro-Version ermöglicht das Gating auf Ebene einzelner WooCommerce-Produktkategorien.

= Kann ich das Design anpassen? =

Ja. Du kannst Overlay-Hintergrundfarbe und Akzentfarbe direkt in den Einstellungen wählen. Mit der Pro-Version steht zusätzlich ein Custom-CSS-Feld zur Verfügung.

= Wo wird das Cookie gesetzt? =

Das Cookie `avf_ok=1` wird nach Bestätigung durch JavaScript gesetzt (kein Server-Request). Es gilt für den Pfad `/`, hat eine konfigurierbare Laufzeit und ist als `SameSite=Lax` markiert.

== Changelog ==

= 0.1.0 =
* Erstveröffentlichung
* Vollbild-Overlay (Fail-Safe via CSS, JS entfernt ihn)
* Modi: Bestätigung und Geburtsdatum
* Geltungsbereich: gesamte Website oder bestimmte Seiten
* Anpassbare Farben, Logo, Texte
* Funktionales Cookie (kein PII, kein Tracking)
* Admin-Einstellungsseite mit Upgrade-Teaser
* Pro-Hooks: WooCommerce-Kategorien, Geo-Targeting, White-Label, Custom CSS
* Keine externen Abhängigkeiten
