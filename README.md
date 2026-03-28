# SNIPI Ekrani

**Prikaz urnikov iz sistema Snipi na velikih TV zaslonih.**

![WordPress](https://img.shields.io/badge/WordPress-6.7%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![Licenca](https://img.shields.io/badge/Licenca-GPLv2-green)
![Verzija](https://img.shields.io/badge/Verzija-2.3.5-blue)
![Status](https://img.shields.io/badge/Status-Stabilna-brightgreen)

---

## Kazalo

- [O vtičniku](#o-vtičniku)
- [Kaj vtičnik zmore](#kaj-vtičnik-zmore)
- [Namestitev](#namestitev)
- [Začetek uporabe](#začetek-uporabe)
- [Nastavitve](#nastavitve)
- [TV ekrani](#tv-ekrani)
- [Oblikovanje](#oblikovanje)
- [Pogosta vprašanja](#pogosta-vprašanja)
- [Roadmap](#roadmap)
- [Podpora in kontakt](#podpora-in-kontakt)
- [Licenca](#licenca)

---

## O vtičniku

**SNIPI Ekrani** je WordPress vtičnik, ki prikazuje urnike iz CRM sistema **SNIPI** na velikih LCD zaslonih in pametnih televizorjih — brez ročnega posodabljanja.

Namenjen je šolam, fakultetam in drugim izobraževalnim ustanovam, ki že uporabljajo SNIPI za upravljanje urnikov. Podatki se samodejno osvežujejo v živo, zaslon pa se prilagodi vsaki ločljivosti — od navadnega monitorja do 4K TV zaslona.

---

## Kaj vtičnik zmore

### Prikaz urnika
- Prikazuje urnik za tekoči dan iz sistema SNIPI v pregledni tabeli
- Samodejno osvežuje podatke vsakih 60 sekund — vedno aktualno
- Označuje aktivne (trenutno potekajoče) dogodke
- Lahko prikazuje urnik za prihodnje dni (do 30 dni vnaprej)
- Podpira vikend način (v petek po zadnjem dogodku preskoči soboto in nedeljo ter na ekranu prikazuje dogodke od prihodnjega ponedeljka dalje)
- Po želji prikazuje stolpec PROGRAM (vsebina je odvisna od vaše konfiguracije v SNIPIju)

### TV optimizacija
- Samodejno zazna pametne TV ekrane (Samsung, LG, Sony itd.)
- Prilagodi velikost pisave in razporeditev glede na ločljivost zaslona
- Podpora za HD Ready (1366×768), Full HD (1920×1080) in 4K (3840×2160)
- Brez drsenja — vsebina se vedno prilagodi ekranu

### Paginacija strani
- Samodejno prehaja med stranmi, če je število vnosov za več kot eno stran
- Število prikazanih dogodkov (vrstic) in interval prehajanja sta nastavljiva

### Oblikovanje
- Lasten logotip organizacije z nastavljivo višino
- Spodnja vrstica (noga) z lastno vsebino, na primer legendo kratic ipd. (WYSIWYG urejevalnik)
- Popoln nadzor nad izgledom z lastnim CSS

### Skrbniški vmesnik
- Dodajanje več ekranov z ločenimi nastavitvami
- Kratka koda za vgraditev na katero koli WordPress stran ali objavo
- Zavihka Nastavitve in Oblikovanje s preglednim vmesnikom

---

## Namestitev
1. [Prenesite najnovejšo ZIP datoteko vtičnika](https://github.com/Squarebow/SNIPI-Ekrani/releases/download/v2.3.5/SNIPI-Ekrani-v2.3.5.zip)
2. V WordPress skrbniškem vmesniku pojdite na **Vtičniki → Dodaj nov vtičnik**
3. Kliknite **Naloži vtičnik** in izberite preneseno ZIP datoteko
4. Kliknite **Namesti zdaj**, nato **Aktiviraj**

> **FTP metoda:** ZIP datoteko razpakirajte in mapo `snipi-ekrani` prekopirajte v `/wp-content/plugins/` prek FTP, nato vtičnik aktivirajte v WordPress vmesniku.

---

## Uporaba

### 1. Najprej ustvarite ekran v SNIPIju

Registrirani uporabniki SNIPIja se po prijavi pomaknite v razdelek **Rezervacija prostorov → Izpisi na ekranih**. Ustvarite nov ekran ali uredite obstoječega.

Izberite, katere podatke želite prikazati na ekranu. Možnosti, ki so na voljo za prikaz, vključujejo: lokacije, prostore, projekte, šolske programe, izvajalce ipd.

> **Pomembno:** Po potrebi lahko ustvarite več ekranov z različnimi konfiguracijami za prikaz na različnih straneh, objavah ali TV zaslonih!

Ko ekran v SNIPIju shranite, dobi URL povezavo. **API ključ** je zadnji del URL naslova vašega zaslona v SNIPIju.

**Primer:**
```
https://ustanova.snipi.si/BdhBcrRm8
                        ↑
                    API ključ = BdhBcrRm8
```

Kopirajte API ključ za naslednji korak!


### 2. Ustvarite nov ekran v Wordpressu

Po namestitvi in aktivaciji tega vtičnika v skrbniškem meniju na levi kliknite **SNIPI ekrani → Dodaj ekran**.


### 3. Vnesite API ključ

Kopirani ključ prilepite ali vpišite v polje **API ključ** na strani za urejanje ekrana ga shranite.


### 4. Kopirajte kratko kodo

Po shranjevanju se prikaže vaša **kratka koda** za vgradnjo, na primer:

```
[snipi_ekran id="123"]
```

### 5. Vgradnja na Wordpress stran

V WordPressu **ustvarite novo stran** in po poimenujte npr. ekran. Kratko kodo prilepite na stran z uporabo bloka "HTML po meri" ali "Kratka koda". Shranite stran in jo osvežite v  brskalniku (Ctrl + F5) - urnik se bo prikazal.

> **Priporočilo:** Wordpress stran, kjer želite prikazati urnik, naj bo **popolnoma prazna**. To pomeni, da je na njej priporočljivo odstraniti oziroma skriti privzeto glavo in nogo (header & footer).

Odvisno od vaše Wordpress teme, gradnikov (blocks, Elementor ipd.) ter vtičnikov, je to mogoče narediti na več načinov. Nekatere sodobne teme omogočajo izključitev prikaza glave in noge na posameznih stranehv prilagoditvah teme (customizer).

Če te možnosti nimate, je potrebno te elemente "skriti" s CSS kodo, da se ne prikazujejo na ekranu.

Če uporabljate vtičnik za medpomnjenje (caching), je url strani, npr. `moja-ustanova.si/ekran` priporočeno dodati **med izključitve (do not cache)**.


### 6. Vgradnja urnika med vsebino vaše strani (iframe)

Ko ste urnik izdelali in shranili, ga lahko kot HTML element vgradite tudi **na katero koli obstoječo spletno stran (page) ali objavo (post)** bodisi z uporabo enake kratke kode kot v prejšnjem koraku ali pa z elementom `<iframe>`.<br> Spodnji primer prikazuje urnik pomanjšan v razmerju 2:3, tako da se prilagodi manjšemu prostoru na strani, a ohrani izvorno obliko. Ključno je, da **uporabite url strani, na kateri imate kratko kodo urnika**.<br> Na primer, če ste Wordpress stran z urnikom poimenovali urnik-pritlicje, boste v iframe vstavili url `https://moja-ustanova/urnik-pritlicje`.<br> Kopirajte in prilepite spodnjo kodo v HTML blok na strani ali objavi, kjer želite prikazati urnik, in **spremenite URL** ter po potrebi velikost (max-width).

---

#### Osnovna koda
```html
<div style="
    position: relative;
    width: 100%;
    max-width: 900px;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    border-radius: 6px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
">
    <iframe
        style="
            position: absolute;
            top: 0;
            left: 0;
            width: 150%;
            height: 150%;
            border: none;
            transform: scale(0.667);
            transform-origin: top left;
            pointer-events: none;
        "
        src="https://moja-domena.si/urnik"
        scrolling="no">
    </iframe>

    
        href="https://mopja-domena/urnik"
        target="_blank"
        title="Odpri celotno stran"
        style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            cursor: pointer;
            display: block;
        ">
    </a>
</div>
```

---

#### Kako to deluje

Iframe je nastavljen na **150 % širine in višine** vsebnika, nato pa pomanjšan nazaj na **66,7 %** z `transform: scale(0.667)`. Rezultat je urnik v polni ločljivosti, ki vizualno zasede manjši prostor — brez drsnih vrstic in brez deformacije besedila ali elementov.

Ob kliku se odpre urnik v novi zavihku.

---

#### Parametri, ki jih lahko prilagodite

| Parameter | Kje v kodi | Opis |
|---|---|---|
| `max-width: 900px` | zunanja `div` | Največja širina vsebnika; zmanjšajte za ožji prikaz ali povečajte za širši prikaz |
| `aspect-ratio: 16 / 10` | zunanja `div` | Razmerje stranic vsebnika; `16 / 9` za TV format |
| `border-radius: 6px` | zunanja `div` | Zaobljenost vogalov; `0` za pravokotni okvir |
| `box-shadow: ...` | zunanja `div` | Senca okoli vsebnika; odstranite vrstico, če je ne želite |
| `width: 150%` + `height: 150%` | `iframe` | Določata, kako velik je iframe pred pomanjšanjem — skupaj s `scale` |
| `transform: scale(0.667)` | `iframe` | Faktor pomanjšanja (0.667 = 2/3); za manj pomanjšan prikaz povečajte vrednost (npr. `0.75`) |
| `src="https://..."` | `iframe` in `<a>` | URL *vaše strani z urnikom*; nujno ga zamenjajte z pravim naslovom |

**Razmerje med `width`/`height` in `scale` mora biti vedno usklajeno:**  
Če nastavite `width: 133%` in `height: 133%`, mora biti `scale(0.75)` — vrednosti sta med seboj obratno sorazmerni.

---

#### Opomba v primeru (ne)prikazovanja) urnika na strani pri vgradnji z iframe metodo

- Če je stran z urnikom na **isti domeni** kot stran, na katero jo vgrajujete, teh omejitev ni in iframe bo deloval brez posebnih nastavitev.
- Če je stran z urnikom na **drugi domeni**, mora strežnik dovoliti vgradnjo — glava `X-Frame-Options` ne sme biti nastavljena na `SAMEORIGIN` ali `DENY`.

Kar vpliva na vgradnjo z iframom je glava `X-Frame-Options` (ali `Content-Security-Policy: frame-ancestors`) na vaši ciljni strani (kamor vgrajujete iframe)!

> Na strežnikih nginx in pri uporabi Cloudflare CDN je pogosta težava, da je glava `X-Frame-Options: SAMEORIGIN` nastavljena privzeto — bodisi v nginx konfiguraciji, WordPress kodi ali prek Cloudflare upravljanih preoblikovanj. Preverite vse tri vire, če iframe prikazuje prazno vsebino ali napako.


---

## Nastavitve

Na zaslonu za urejanje ekrana so na voljo naslednje nastavitve:

| Nastavitev | Opis | Privzeto |
|---|---|---|
| **Ime ekrana** | Naziv za prepoznavanje v skrbniškem vmesniku | — |
| **API ključ** | Ključ iz sistema Snipi (obvezno) | — |
| **Vrstic na stran** | Koliko vnosov oziroma vrstic se prikaže na eni strani | 10 |
| **Interval prehajanja** | Čas med stranmi v sekundah | 10 s |
| **Prihodnji dnevi** | Koliko dni vnaprej se prikazuje urnik | 1 |
| **Vikend način** | V petek preskoči soboto in nedeljo ter prikazuje naslednji teden | Izklopljeno |
| **Stolpec PROGRAM** | Prikazuje stolpec z imenom programa | Izklopljeno |
| **Logotip** | Slika za glavo ekrana | — |
| **Višina logotipa** | Višina logotipa v pikslih | 60 px |
| **Spodnja vrstica** | Fiksna vsebina na dnu (HTML) | Izklopljeno |

---

## TV ekrani

SNIPI Ekrani vsebuje poseben način optimizacije za pametne televizorje.

### Samodejna zaznava

Vtičnik samodejno prepozna, ali je zaslon priklopljen na pametni TV (Samsung Tizen, LG webOS, Sony Android TV in drugi). Ko zazna TV, preklopi v TV način, ki zagotavlja:

- Prilagajanje vsebine celotnemu zaslonu brez drsenja
- Pisava je dovolj velika za branje z razdalje
- Razporeditev je optimizirana za daljinski upravljalnik

> **Opozorilo:** Brskalniki, ki so vgrajeni v pametne TV naprave, niso zelo zmogljivi in delujejo drugače kot brskalniki na računalniki. Če na zaslonu opazite nenavaden prikaz, preizkusite na TV naložiti več različnih brskalnikov in jih preizkusite, kateri vam najbolj ustreza.

### Nastavitve TV načina

| Možnost | Opis |
|---|---|
| **Samodejno** | Vtičnik sam zazna TV in preklopi način |
| **Vedno TV** | TV način vedno vklopljen (priporočeno za namenske zaslone) |
| **Vedno Desktop** | TV način vedno izklopljen |

> **Nasvet:** Če zaslon služi izključno kot informacijski ekran, priporočamo možnost **Vedno TV** — tako ni odvisen od zaznave brskalnika.

### Podprte ločljivosti

| Resolucija | Tip zaslona |
|---|---|
| 1366 × 768 | HD Ready TV |
| 1920 × 1080 | Full HD TV |
| 3840 × 2160 | 4K TV |

---

## Oblikovanje

### Zgradba zaslona

Zaslon urnika je sestavljen iz štirih področij. Vsako področje lahko oblikujete neodvisno.

---

**Glava strani** `.snipi__header`

Prikazuje se na vrhu zaslona in ostane vidna ves čas. Vsebuje logotip vaše ustanove (če ga naložite), ime ekrana kot naslov, tekoči datum ter uro, ki se samodejno posodablja vsako sekundo. Glava je fiksni element — ne izgine pri menjavi strani ob paginaciji.

Kaj lahko spremenite: barvo ozadja in pisave, velikost in poravnavo logotipa, vidnost posameznih elementov (datum, ura, naslov).

---

**Glava tabele** `.snipi__table thead`

Vrstica z imeni stolpcev, ki se prikaže tik pod glavo strani. Vsebuje naslove stolpcev: Čas, Izobraževanje, Program (če je vključen prikaz), Predavatelj, Učilnica in Nadstropje. Glava tabele je vedno fiksna, medtem ko se vrstice z dogodki menjajo.

Kaj lahko spremenite: barvo ozadja in pisave, debelino in barvo spodnje obrobe, velikost in težo pisave.

---

**Telo tabele** `.snipi__row`

Osrednji del zaslona — seznam dogodkov za izbrani dan ali obdobje. Vsaka vrstica predstavlja en dogodek. Vrstice se izmenjujejo v dveh odtenkih ozadja (`.snipi__row--alt`) za boljšo berljivost. Aktiven (trenutno potekajoč) dogodek je dodatno označen z utripajočim indikatorjem v živo (`.snipi__live-indicator`). Če urnik obsega več strani, je prehajanje med njimi samodejno. Prihodnji dnevi so ločeni z imenom dneva in datumom (`.snipi__day-label`).

Kar lahko spremenite: barvo in višino vrstic, barvo izmenitvenih vrstic, poudaritev aktivnega dogodka, slog oznake za prihodnji dan, velikost in barvo pisave v posameznih stolpcih.

---

**Noga strani** `.snipi__bottom-row`

Prikazuje se na dnu zaslona in ostane vidna ves čas, neodvisno od vsebine tabele. Je opcijska — vklopite jo v nastavitvah in vanjo z WYSIWYG urejevalnikom vnesete poljubno HTML vsebino: obvestilo, logotip partnerja, kontaktne podatke ali kateri koli vsebinski element. Noga je priporočljiva za zaslone v hodnikih, kjer želite prikazati stalne informacije poleg urnika.

Kar lahko spremenite: barvo ozadja in pisave, višino, poravnavo vsebine, robove in obrobe.

###  Uporabljeni CSS razredi (napredni uporabniki)

Na zavihku **Oblikovanje** lahko spremenite videz urnika z lastnim CSS. Primeri in reference CSS razredov (class).


| Class | Element |
|---|---|
| `.snipi` | Celoten blok urnika |
| `.snipi__header` | Glava (logotip, datum, ura) |
| `.snipi__title` | Naslov |
| `.snipi__table` | Tabela urnika |
| `.snipi__row` | Vrstica dogodka |
| `.snipi__row--alt` | Izmenična vrstica (drugačno ozadje) |
| `.snipi__live-indicator` | Oznaka za aktiven dogodek |
| `.snipi__day-label` | Oznaka za prihodnji dan |
| `.snipi__bottom-row` | Spodnja fiksna vrstica |
| `.snipi__logo` | Logotip |

### Primeri

```css
/* Sprememba barve glave */
.snipi__header {
    background: #1a3a5c;
    color: white;
}

/* Večja pisava naslova */
.snipi__title {
    font-size: 3rem;
}

/* Izmenične vrstice */
.snipi__row--alt {
    background: #f0f4f8;
}

/* Barva aktivnega dogodka */
.snipi__live-indicator {
    color: #e53e3e;
}
```

---

## Pogosta vprašanja

**Kje dobim API ključ?**  
API ključ je zadnji del URL naslova vašega zaslona v sistemu SNIPI (primer: `https://ustanova.snipi.si/BdhBcrRm8` → API ključ je `BdhBcrRm8`). Registrirani uporabniki SNIPIja seznam ekranov in API ključ najdete v razdelku **Rezervacija prostorov → Izpisi na ekranih**.<br> Kateri podatki se bodo prikazali na ekranu izberete pri ustvarjanju novega ekrana v SNIPIju oziroma s klikom na Uredi. Možnosti, ki so na voljo za prikaz podatkov, so: lokacije, prostori, projekti, šolski programi, izvajalci ipd.

**Koliko ekranov lahko ustvarim?**  
Ni omejitve — ustvarite lahko toliko ekranov, kolikor jih potrebujete. Za prikaze različnih podatkov najprej ekran ustvarite v SNIPIju in izberite parametre, nato pa še v Wordpress vtičniku. Vsak ima ločene nastavitve in svojo kratko kodo.

**Ali vtičnik deluje na starejših TV ekranih?**  
Da. Prikaz je optimiziran za starejše brskalnike Smart TV (vključno s Samsung Tizen pred letom 2018). Vtičnik ne uporablja jQuery ali modernih ogrodij — samo čisti JavaScript.

**Ali se podatki shranjujejo lokalno?**  
Ne. Vtičnik vsakič pridobi sveže podatke neposredno iz Snipi API — brez predpomnilnika.

**Ali moram imeti WordPress za uporabo vtičnika?**  
DA — vtičnik zahteva WordPress. Načrtujemo pa različico brez WordPressa; glejte razdelek [Roadmap](#roadmap).

**Ali vtičnik podpira večjezičnost?**  
Skrbniški vmesnik je v slovenščini. Podatki v urniku se prikazujejo točno tako, kot so vneseni v sistemu Snipi.

---

## Roadmap

> 🚀 **SNIPI Ekrani kot samostojna aplikacija**

Ker vse ustanove nimajo spletnih strani, zgrajenih na platformi WordPress, pripravljamo **samostojno različico SNIPI Ekrani**, ki ne bo zahtevala nobene vsebinske platforme.

### Načrtovane možnosti

**SaaS storitev (gostovana rešitev)**  
Preprosta spletna aplikacija, dostopna prek brskalnika. Ustvarite brezplačen uporabniški račun, dodate ekrane in pridobite URL za vsak zaslon — brez nameščanja česarkoli.

**Docker (samogostovanje)**  
Za ustanove z lastno IT infrastrukturo (spletnim strežnikom in gostovanjem) bo na voljo Docker kontejner. Zaženete jo na svojem strežniku in tako v celoti nadzorujete podatke in zasebnost.

### Kaj ostaja enako

Prikaz urnika na TV zaslonu bo v obeh različicah enak kot je v Wordpress vtičniku — enak videz, enaka hitrost. Samo skrbniški oziroma administratorsdki del bo dostopen neposredno prek spleta - brez WordPressa.

### Kdaj?

Predvidoma do konca leta 2026. Sledite repozitoriju na GitHubu za novosti.

---

## Podpora in kontakt

Za prijavo napak in predloge novih funkcionalnosti uporabite [GitHub Issues](https://github.com/Squarebow/snipi-ekrani/issues).

Tehnična pomoč pri namestitvi ali naročilo prilagoditev: [Pišite nam](mailto:info@squarebow.com?subject=SNIPI%20Ekrani%20podpora)

SNIPI podpora (Dejan Dular): [SNIPI podpora](mailto:dejan@snipi.si)

---

## Dnevnik sprememb

### v2.3.5 — trenutna stabilna verzija
- Najnovejše popravke in izboljšave najdete v datoteki `CHANGELOG.md`

### v2.2.0
- Avtomatska zaznava Smart TV ekranov
- TV optimizacija: zero-scroll, dinamično skaliranje pisave
- Podpora za HD Ready, Full HD in 4K resolucije
- Nov meta box TV Optimizacija v skrbniškem vmesniku

### v2.1.0
- FontAwesome ikone v skrbniškem vmesniku
- WordPress Settings API integracija z avtomatično sanitizacijo

### v2.0.0
- Popolna prenova arhitekture v 6 ločenih modulov
- Nov 60:40 layout (nastavitve + vgrajeni priročnik)
- Sistem jezičkov (Nastavitve | Oblikovanje)
- Vsi komentarji v kodi v slovenščini

---

## Licenca

Vtičnik je licenciran pod [GPLv2 ali novejšo](https://www.gnu.org/licenses/gpl-2.0.html) licenco.

---

**Avtor:** Aleš Lednik · [SquareBow](https://squarebow.com)  
**Narejeno s ❤️ v Sloveniji**
