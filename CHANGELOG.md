# SNIPI Ekrani - Changelog

## [2.3.1] – 2026-03-06

### Admin – Nastavitve tab
- **Skaliranje pisave**: radio gumbi prerazporejeni v dve koloni (50:50), vsak z opisom pod seboj
- **Spodnja vrstica – višina**: radio gumbi prerazporejeni v dve koloni (50:50)
- Dodan naslov **Urejevalnik vsebine spodnje vrstice** nad WYSIWYG editorjem
- **TV optimizacija**:
  - Opis sekcije dobil konsistenten spodnji odmik pred kontrolami
  - Polje za detekcijo obdano v siv blok (`.snipi-tv-option-block`) za vizualno ločitev
  - **Način prikaza** in **Potrditveno okno** postavljeni v eno vrstico (50:50)
  - Način prikaza: zamenjali dropdown s tremi horizontalnimi radio pill gumbi (Avtomatsko / Vedno TV / Namizni)

### Admin – Oblikovanje tab
- **Spodnja vrstica – 1. vrstica**: Poravnava besedila premaknjena za barvo besedila → razporeditev 33:33:33 (Ozadje | Besedilo | Poravnava)
- **Spodnja vrstica – 2. vrstica**: Padding zgoraj premaknjen na desno → razporeditev 33:33:33 (Velikost pisave | Padding L/D | Padding zgoraj)
- **Predogled**: odstranjen vgrajen preview box, nadomestil ga gumb **Odpri predogled v novem oknu** — odpre odzivno okno (1280×720, nastavljiva velikost), kjer uporabnik testira skaliranje pisave

### CSS / JS
- `admin.css`: dodani stili za `.snipi-radio-pill`, `.snipi-radio-inline`, `.snipi-tv-option-block`, `.snipi-radio-label--block`
- `admin-styling.css`: poenostavljen, odstranjena pravila za preview box
- `admin-styling.js`: dodan handler za radio pill aktivni razred; predogled zdaj odpre `window.open()` popup namesto injiciranja CSS v vgrajen box


## [2.3.0] – 2025-03-06

### Admin – Nastavitve tab
- **Ime ekrana + API ključ** prikazana v eni vrstici (50:50)
- **Dodatne možnosti** premaknjene takoj za kratko kodo
- Polja Vrstic / Interval / Prihodnji dnevi v razporeditvi 33:33:33 z opisom pod vsakim poljem
- **Vikend način** in **Prikaži stolpec PROGRAM** v eni vrstici (50:50)
- **Informacije o ekranu** (info box) premaknjene na vrh desnega stolpca nad navodila
- Dodano nastavitev **Skaliranje pisave** (Samodejno fill / Prosto)
- **Spodnja vrstica**: dodan radio switch Samodejno / Fiksna višina z px sliderjem (40–200px)

### Admin – Oblikovanje tab (novo)
- Zamenjali smo golo CSS polje s **4-sekcijskim GUI**: Cel zaslon, Glava, Tabela, Spodnja vrstica
- **Color picker** (wp-color-picker / Iris) za vse barvne lastnosti
- **Range sliderji** z prikazano vrednostjo za pisave (70–150%), padding (px)
- **Dropdown** za izbiro pisave (8 možnosti) in poravnavo besedila
- **Toggle** za live indikator (prikaži/skrij)
- **Custom CSS** ohranjen kot accordion sekcija za napredne uporabnike
- Živi predogled CSS se injicira brez shranjevanja forme

### Frontend – font scaling
- Nov način **fill**: pisava vrstic se skalira (CSS `--snipi-row-scale`) da `rowsPerPage` vrstic vedno zapolni razpoložljivi zaslon
- Nov način **free**: ohrani obstoječe vedenje (privzeta pisava, prikaži kolikor vrstic se ujame)

### Frontend – footer
- **ResizeObserver** na spodnji vrstici: pri vsaki spremembi višine se tabela samodejno preračuna
- Podpora za **fiksno višino footerja**: `footerHeightMode=fixed` rezervira točno toliko px prostora
- Popravek: `totalPages` se izračuna z enim korakom – odpravljena možnost zamika paginacije pri prvem renderu

### Interno
- `class-admin-meta.php`: nova meta polja `_snipi_row_scale_mode`, `_snipi_footer_height_mode`, `_snipi_footer_fixed_height`, `_snipi_styling_data` (JSON)
- `class-renderer.php`: nova metoda `generate_styling_css()` – generira scoped CSS iz GUI podatkov
- `class-shortcode.php`: injicira styling CSS + posreduje nova JS konfig polja
- `class-admin-core.php`: doda `wp-color-picker` v enqueue

## v2.2.0 (15. februar 2026) - TV DETECTION & OPTIMIZATION

### ✨ Nove funkcionalnosti

**Avtomatska TV detekcija:**
- ✅ Samodejno zazna Smart TV ekrane (Samsung Tizen, LG webOS, Sony Android TV, itd.)
- ✅ User Agent detection + resolucija matching
- ✅ Confidence scoring (high/medium/low)

**TV optimizacija:**
- ✅ Zero-scroll garantija - vsebina se vedno prilagodi ekranu
- ✅ Dinamično skaliranje pisave (14px/16px/24px)
- ✅ Avtomatski grid columns (3/4/6 glede na resolucijo)
- ✅ Optimizacija za HD Ready (1366×768), Full HD (1920×1080), 4K (3840×2160)

**Admin nastavitve:**
- ✅ Nov meta box "TV Optimizacija"
- ✅ Avtomatska detekcija TV (privzeto vključeno)
- ✅ Način prikaza override (Auto/Vedno TV/Vedno Desktop)
- ✅ Potrditveno okno pri prvi uporabi

**Potrditveno okno:**
- ✅ "Zaznan TV ekran. Optimiziram prikaz za TV?"
- ✅ localStorage shranjevanje izbire
- ✅ Auto-hide po 15 sekundah

### 🔧 Tehnične spremembe

**Novi moduli:**
- ✅ `class-admin-tv-tab.php` - TV settings meta box
- ✅ `assets/css/tv.css` - TV-optimized styling
- ✅ `assets/js/tv.js` - TV detection & optimization logic

**CSS optimizacije:**
- ✅ Fixed heights namesto flex (boljša TV browser podpora)
- ✅ CSS variables za dinamično skaliranje
- ✅ Media queries za različne TV resolucije

### 🐛 Popravljeno
- Footer breaking na več vrstic na TV ekranih
- Scrollbar quirks na Smart TV brskalnikih
- Font scaling na različnih resolucijah

---

## v2.1.0 (14. februar 2026) - FAZA 3: WP NATIVE INTEGRATION + FONTAWESOME

### ✨ Nove funkcionalnosti

**FontAwesome ikone:**
- ✅ **FontAwesome 6.4.0** naložen preko CDN
- ✅ **Vsi emojiji zamenjani** z FontAwesome ikonami:
  - 📋 → <i class="fas fa-clipboard-list"></i> (Navodila)
  - 🔑 → <i class="fas fa-key"></i> (API ključ)
  - 📄 → <i class="fas fa-file-code"></i> (Kratka koda)
  - 📊 → <i class="fas fa-chart-bar"></i> (Prikaz dogodkov)
  - ⚙️ → <i class="fas fa-cog"></i> (Dodatne možnosti)
  - 🖼️ → <i class="fas fa-image"></i> (Logotip)
  - 📌 → <i class="fas fa-thumbtack"></i> (Spodnja vrstica)
  - 🎨 → <i class="fas fa-palette"></i> (Oblikovanje)
  - In več...

**WordPress Settings API:**
- ✅ Nov modul `class-admin-settings.php`
- ✅ Registracija nastavitev preko `register_setting()`
- ✅ Avtomatična sanitizacija input polj
- ✅ Type hints za vsako nastavitev
- ✅ Default vrednosti definirane v Settings API

### 🎨 UI Izboljšave

**FontAwesome styling:**
- ✅ Ikone v help box naslovih (h3, h4)
- ✅ Ikone v gumbih (preview CSS)
- ✅ Ikone v field-group naslovih
- ✅ Barvanje ikon (#2271b1 - WP blue)
- ✅ Pravilni razmiki (margin-right)

**CSS posodobitve:**
- ✅ Dodani stili za `.button i.fas`
- ✅ Dodani stili za `.snipi-help-box h3 i`
- ✅ Dodani stili za `.snipi-field-group h3 i`

### 🔧 Tehnične izboljšave

**Settings API prednosti:**
- ✅ Boljša WP integracija
- ✅ Avtomatična sanitizacija
- ✅ Type safety (string, integer, boolean)
- ✅ Default vrednosti
- ✅ Validacija z callback funkcijami

**Sanitizacijske callback funkcije:**
- `sanitize_rows_per_page()` - 1-50
- `sanitize_autoplay_interval()` - 5-60 sekund
- `sanitize_future_days()` - 0-3 dni
- `sanitize_logo_height()` - 40-120px
- `sanitize_checkbox()` - '1' ali '0'

### 📦 Nove datoteke

- `includes/Admin/class-admin-settings.php` - WordPress Settings API modul

### 📝 Posodobljene datoteke

**PHP:**
- `snipi-ekrani.php` - Verzija 2.1.0, require Settings API
- `includes/Admin/class-admin-core.php` - FontAwesome CDN enqueue
- `includes/Admin/class-admin-edit-screen.php` - FA ikone v help boxih
- `includes/Admin/class-admin-styling-tab.php` - FA ikone v naslovih

**CSS:**
- `assets/css/admin.css` - FontAwesome styling

---

## v2.0.1 (14. februar 2026) - BUGFIX RELEASE

### 🐛 Popravki napak (Fixes)

**KRITIČNI POPRAVKI:**
- ✅ **Fatal error popravljen** - Dodani manjkajoči moduli iz originalne verzije
  - Kopirani `includes/Api/` moduli (class-data-service.php, class-rest-controller.php)
  - Kopirani `includes/Front/` moduli (class-renderer.php, class-shortcode.php)
  - Kopirani manjkajoči assets (JS, CSS, SVG)

- ✅ **Case sensitivity popravljen** - Linux server zahteva natančno ujemanje
  - Preimenovan direktorij: `includes/admin/` → `includes/Admin/`
  - require_once statements v snipi-ekrani.php zdaj delujejo pravilno

- ✅ **Vrstni red stolpcev popravljen** v CPT listi
  - **PREJ:** Kratka koda | API ključ | Naslov
  - **ZDAJ:** Naslov | API ključ | Kratka koda ✓

### 📦 Manjkajoče datoteke dodane:

**API moduli:**
- includes/Api/class-data-service.php
- includes/Api/class-rest-controller.php

**Frontend moduli:**
- includes/Front/class-renderer.php
- includes/Front/class-shortcode.php

**Assets:**
- assets/css/admin-styling.css
- assets/css/front.css
- assets/js/admin.js
- assets/js/admin-styling.js
- assets/js/front.js
- assets/Copy_icon_256px.svg
- assets/Live.svg

### ✅ Preverjeno:

- [x] Plugin se pravilno aktivira (brez fatal error)
- [x] Vsi moduli prisotni in na pravem mestu
- [x] Case sensitivity popravljen za Linux server
- [x] Vrstni red stolpcev: Naslov → API ključ → Kratka koda
- [x] Vsi assets (CSS, JS, SVG) prisotni

---

## v2.0.0 (14. februar 2026) - MAJOR REFACTORING

### 🎉 Glavne spremembe:

**Arhitektura:**
- ✅ Modularizacija: `class-admin.php` (754 vrstic) → 6 modulov
- ✅ Slovenski komentarji: 350+ vrstic (30% kode)
- ✅ Brez inline CSS - vse v datotekah

**UI Spremembe:**
- ✅ 60:40 layout (main content + inline help)
- ✅ Tab sistem (Nastavitve | Oblikovanje)
- ✅ WP native tab styling
- ✅ Sticky sidebar z navodili

**Code Quality:**
- ✅ Centralizirana meta logika
- ✅ Konsistentna validacija
- ✅ BEM CSS metodologija
- ✅ Responsive design

---

## Navodila za posodobitev iz v1.1 na v2.0.1:

1. **Deaktiviraj** staro verzijo vtičnika (v1.1)
2. **Izbriši** stare datoteke preko FTP:
   - includes/Admin/class-admin.php
   - includes/Admin/class-admin-styling.php
3. **Naloži** nove datoteke iz v2.0.1 ZIP arhiva
4. **Aktiviraj** vtičnik
5. **Testiraj** da vse deluje

**Pomembno:** Podatki (ekrani, nastavitve) se ohranijo - samo koda se posodobi.

---

## Known Issues:

- Ni trenutno znanih težav v v2.0.1

---

**Verzija:** 2.0.1  
**Status:** ✅ Stable  
**Priporočeno za:** Produkcijo
