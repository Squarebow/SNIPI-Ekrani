# SNIPI Ekrani

**Version:** 1.0.4
**Author:** SquareBow  
**Requires WordPress:** 5.4+  
**Tested up to:** 6.7  
**Requires PHP:** 7.4+  
**License:** GPLv2 or later

SNIPI Ekrani je WordPress vtičnik za prikaz urnikov in aktivnosti v živo na velikih zaslonih iz programa **Snipi**  
Samodejno upravlja z **paginacijo**, **predvajanjem strani**, **podpira 16:9 responsive layout**, in **interval osveževanja podatkov**.

Vtičnik administratorjem omogoča dodajanje več ekranov, konfiguriranje obdobja prikaza podatkov (privzeto za tekoči dan) ter oblikovanje tabeleza prikaz. 

---

## 📌 Features

### Frontend
- Fetches and displays schedule data from the Snipi API  
- Autoplay carousel  
- Automatic page refresh  
- Responsive 16:9 layout  
- Pagination 
- Shortcodes for embedding schedules on any page  

### Admin Area
- Custom menu: **SNIPI ekrani**  
- GUI settings from `includes/class-admin-settings.php`  
- API configuration  
- Display configuration (autoplay, refresh interval, etc.)  
- Toggle different behaviour options  
- Easily extendable settings page  
- Custom CSS/JS enqueues located in `assets/`  

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`  
2. Activate the plugin in **Plugins > Installed Plugins**  
3. Open **SNIPI ekrani** in the left WordPress admin menu  
4. Enter your API data  
5. Insert the shortcode into any page or post

## Changelog

v2.7
- Popravljene povezave za nastavitvene strani SNIPI ekranov, da se ob urejanju obstoječega ekrana stran Nastavitve pravilno naloži brez napake "Napaka pri nalaganju snipi-nastavitve".

v2.6
- Dodane samostojne skrbniške strani (Nastavitve, Oblikovanje, Navodila) z gumbi za preklop, premaknjenim logotipom in izboljšanim prikazom informacij.
- Opcijski stolpec PROGRAM med IZOBRAŽEVANJE in UČITELJ (novi checkbox), WYSIWYG uvodna oznaka ter posodobljene ikone za kopiranje kratke kode v seznamu in urejanju ekrana.

v2.5
- Nazaj na prejšnjo verzijo + popravki datuma
- Obdobje prikaza je vedno vezano na današnji dan (00:00–23:59:59), z opcijo prikaza do 3 prihodnjih dni prek novega polja "Prikaz dogodkov za prihodnje dni".
- Odstranjena polja "Obdobje - od/do"; Vrstic na stran, Autoplay interval in novi izbor prihodnjih dni so prikazani v eni vrstici.

v2.3.4
- Strukturna konsolidacija: vsi potrebni class-* datoteki v canonical strukturi.
- REST: date_from prisilno nastavljen na danes (Europe/Ljubljana). date_to se uporablja le, če je omogočen "Prikaži dogodke v prihodnjih dneh".
- Admin: odstranjen "Od" polje; dodan checkbox "Prikaži dogodke v prihodnjih dneh" (privzeto OFF) + datepicker "Do".
- Filtriranje: server odstrani samo dogodke z end < now; JS ponovno preveri ob vsakem fetchu (hybrid).
- Časovni žig: robustno parsanje ISO in fallback formati; izpisi kot dd.mm.yyyy in 24h H:i.
- Timezone fix

v2.3
- Admin ekran: vse meta polja razporejena v 50:50 vrstice (API ključ / Kratka koda; Obdobje od/do; Vrstic na stran / Autoplay).
- Dodan opis pod Autoplay (pomoč uporabniku).
- Prikaži spodnjo vrstico 100% in WYSIWYG editor za vsebino.
- Logo: Spremeni / Odstrani gumbi delujejo, preview se osveži, dodan slider za višino logotipa (px).
- Oblikovanje: vračam CSS editor (wp.codeEditor / CodeMirror), preview preko REST, popravljena "HTTP napaka pri predogledu".
- Popravljena varnostna varovalka pri nalaganju admin assetov.
- Implementirana hibridna logika filtriranja (server + JS).
- Server normalizira datume v Europe/Ljubljana in vrača start_iso/end_iso z offsetom.
- Filtrira samo dogodke, ki so že končani (end < now).
- JS ponovno preveri in odstrani končane dogodke ob vsakem fetchu (vsako minuto).
- Robustno parsanje ISO in več fallback formatov (če API spremeni format).
- Izpis datuma: dd.mm.yyyy; čas: 24h H:i.
- Ni cachinga; vse deluje z Vanilla JS.



v2.2
- Preprečeno izvajanje admin assetov med WP upload-plugin procesom (odpravljena napaka "Izberi datoteko").
- Odstranjen EventON meta box (#evoia_mb) z urejevalnika ekranov.
- Seznam 'Vsi ekrani': "Shortcode" preimenovan v "Kratka koda", brez okvirja, dodan gumb za kopiranje s potrditvijo.
- Dodan stolpec "API ključ" (prikaz ključa brez treh pik).
- Metabox urejanja: premaknjeno "Prikaži spodnjo vrstico" nad logo in dodan WYSIWYG editor za vsebino spodnje vrstice.
- Dodan 'Odstrani logo' gumb in slider za višino logotipa.
- Vrstic na stran in Autoplay sta v isti vrstici; dodan placeholder pod Obdobje.
- Popravljeni admin JS (vanilla) in preview klic preko REST.


v2.1
- CPT: pravilno registriran kot 'ekran' (singular) in 'ekrani' (menu label)
- Frontend:
	- Enqueue scripts only when shortcode is present.
	- Fetch preko REST (deluje za prijavljene in neprijavljene uporabnike).
	- Logo vrnjen s REST odgovorom in vstavi za neprijavljene uporabnike.
	- Server-side filtriranje končanih dogodkov + client-side zaščita.
	- Sortiranje po času (start ascending).
	- Osveževanje vsako minuto.
	- Če ni dogodkov, prikaže velik centriran napis (Option A); header ostane.
	- Sticky bottom row renderan takoj (če vklopljeno).
- Admin:
	- Popravljena metabox UI (shortcode polje + kopiraj).
	- Admin preview z REST endpointom (deluje brez admin-ajax nonce problemov).
	- Popravljeni admin tabs in JS (vanilla).
- Odpravljen problem izgube menija in napačnega CPT sluga.
v2.0
- Popolna refaktorizacija na REST API arhitekturo (wp-json/snipi/v1).
	- GET /snipi/v1/timeslots?post_id=ID  (public)
	- POST /snipi/v1/preview              (admin-only)
- SNIPI_Data_Service: centraliziran klic zunanjemu Snipi API-ju, brez cache-a, server-side filtriranje preteklih dogodkov in sortiranje (start ascending).
- Frontend:
	- Prenosen na REST klic (brez nonce), deluje za prijavljene in neprijavljene uporabnike.
	- Logotip se vrne z REST odgovorom in se prikaže tudi za neprijavljene uporabnike.
	- Če ni dogodkov, se prikaže velik centriran napis (Option A); header ostaja.
	- Avtomatsko osveževanje vsako minuto.
- Admin:
	- CPT preimenovan v "SNIPI ekrani", gumb "Dodaj ekran".
	- Shortcode polje v metaboxu ter stolpec Shortcode v seznamu CPT.
	- Live preview prepisan na REST (POST /snipi/v1/preview), odpravljena napaka z admin AJAX/nonce.
	- Admin styling assets (vanilla JS), preview rutinsko delujoč.
- Vsa JS koda brez jQuery (vanilla).
- Brez uporabe transients, brez cache zapiskov vtičnika.


v1.2
- CPT: preimenovano v "SNIPI ekrani", gumb "Dodaj ekran" namesto "Dodaj prispevek".
- V metabox (Nastavitve) dodan readonly shortcode polje in gumb "Kopiraj".
- Na seznamu CPT dodani stolpci: Shortcode, Avtor, Datum.
- Popravljena front.js:
	- AJAX action popravljena (sync z backendom).
	- Filtrira zaključene dogodke, sortira po začetku (asc).
	- Osveževanje vsako minuto.
	- Če ni dogodkov, prikaže veliko centrirano sporočilo (Option A), header ostane.
- Logo se sedaj pravilno prikazuje (CSS + HTML popravki).
- Bottom row (legend) se zdaj vedno izpiše takoj (ne odvisno od AJAX).
- Popravljena admin-styling preview logika (post_id in nonce lokalizacija), odpravljena "HTTP napaka pri predogledu".

v1.1 
Dodan nov zavihek "Oblikovanje" v metaboxu CPT 'ekran'
- Uporabnik lahko sedaj nastavi:
	- Barve (header, header tekst, thead ozadje/tekst, vrstica/alt vrstica, bottom vrstica in tekst).
	- Pisavo: sistemska ali Google Fonts (izbira nekaj primerov).
	- Velikost pisave z enotami (px, rem, vw) in font-weight.
	- Padding za vsako stran ločeno (Top/Right/Bottom/Left) z enotami (px, rem, vh).
	- Vklop/izklop alternacije vrstic.
	- Velikost predogleda: 100% ali 1280px.
	- Prikaz logotipa v predogledu.
- Vse spremembe se shranijo v post_meta '_snipi_style_settings' kot JSON.
- Dodan AJAX endpoint 'snipi_render_preview', ki generira server-side HTML preview brez cache-a.
- Dodan admin JS (vanilla) z debounced instant preview (brez jQuery).
- Dodan admin CSS za urejanje oblikovanja.
- Shrani styling kot inline CSS na frontend, vključno z nalaganjem Google fonta, če je izbran.
- Vsi popravki so brez uporabe cache-a in brez jQuery.