### SNIPI ekrani - WP plugin instructions for ChatGPT Codex

Everything in terms of code logic, structure, functionalities and styling should be memorized for further code editing. I will write instructions and context in English, however the user interface of the plugin (admin and frontend) must always be in Slovenian. If there's a typo, please ask to be sure what I wanted o say if you can't get it from the context of the description.

## 1 Brief description of the project

This is my Wordpress plugin that pulls the data from a bespoke CRM called SNIPI via API and displays it on frontend in a html table via shortcode. It is primarily used to display a live timetable of school activities on a 16:9 TV in the hallway so the attendees can find classes, classrooms etc., but it should be flexible and responsive for other devices as well (desktop, tablet, mobile). By default, the plugin must always display events fetched via API for the current day, for today, starting from the earliest to the latest. The list of events of the frontend must be visible regardless of when the event begins, so it should display events from 00:00 hours to 23:59:59 and hide from view only the events that are past/over. For exampe, if an event is scheduled fom 9:00 11:00, and current time is 8:36, it should be displayed in the table nontheless ad disappear from the display at 11:01. The exception is the table page when there are no events scheduled like holidays, weeekends etc.; in that case only a message is displayed in the center of the screen, but I will further define specfics later in the document.

## 2 Key tech considerations for Codex

- The plugin should never under no circumstances use any form of caching as the purpose of the frontend table is to display live events
- I do not ever want to use Jquery - in any form. Remove any trae of it if it exists. Until now I used Vanilla JS but as it's very spartan and limited, I shall prefer you to use a modern UI building framework for admin panel such as React JS
- Never replace anything that is working without asking me first and my approval. You may always suggest QoL improvements of code, UX functionality, but if I ask for something in particular, focus only on that
- The plugin will be publicly abvailable and distributed to other schools so it should be as "agnostic" as possible for exotic technologies. It should work out of the box in Wordpress
- The last development version on Github (the one Codex scanned) is also the version I use on my dev server as a WP install for testing
- For any code you generate, please use tabs for indentation and not spaces. If you need to enqueue any scripts, ask me questions to help me figure out whether we should use conditional loading or if we should enqueue globally. In addition, when generating CSS, please follow BEM methodology. In terms of performance, make sure the plugin loads JS and CSS files only on the pages where the table is actually displayed (shortcode is used) and nowhere else globally. Always apply wordpress best coding practices. Never guess or speculate, ask me for clarification.

- My stack and workflow: my webserver is nginx with maria db. Live wp installation on a subdomain for development at https://upi.wptweaker.com. I use Blocksy PRO theme and Kadence blocks pro (Gutenberg), use Super Page Cache WP plugin by themeisle with cloudflare integration and redis for caching. Tools: Filezilla for ftp, VS Code with github integration, git and Github for windows desktop GUI (avoid giving terminal prompts when possible and give instructions for desktop version). Chrome and Chrome dev tools

I Will describe the logic and the features as I WANT THEM TO be, not as they currently are

### 3 Plugin structure and functionalities description

## BACKEND

3.1 Creating and editing Ekran (a CPT created by plugin)
- When a user/admin clicks Dodaj ekran, he sets the API ključ in the metabox and saves/publishes Ekran. After that, he should automatiaclly be redirected to the editing screen of the created Ekran, where he sets the parameters and designs the table.

For Vsi ekrani admin screen see image Vsi-ekrani.png I want you to add an icon after shortcode to copy it with a tooltip on mousehover saying "Kopiraj" and a confirmation message.

3.2 Editing screen / Admin settings
The elements of the editing screen are as follows, from top to bottom:
- The horizontal tabs Nastavitve (current screen), Oblikovanje and Navodila should be under the Ekran name, with nastavitve highlighted as an active tab

# Nastavitve
- below it a section with API ključ printed on the left and shortcode with copy button printed on the right of a 50/50 row
- below it a row in the same 50/50 format with pagination Vrstic na stran on the left and Autoplay interval on the right (there is some code "" style="width:100%;" />" printed in the current version outside metabox - remove it)
-Below should be four checkbox options for the user to pick, all unchecked by default. Also, if there are further options available when checking the checkbox, it should auto-expand on click:
1) Prikaži današnje končane dogodke (so, if a user chooses so, he can display past events from earlier of the current day in the table as well. Default display is set for events that are going on or are scheduled for later. 2) Pokaži dogodke za prihodnje dni (default display is for event of today, but if the user chooses so I want to give them an option to display, fore example up to 7 days of events ahead - in the future. When he clicks the checkbox, he can select how many days of events he can display). There should be a placeholder for description under every checkbox. 3) Prikaži spodnjo vrstico (this is a text box for bottom row with basic editing capabilites. If uncchecked, there shuld only be a checkbox visible, if checked it should expand into full view.
- the last Nastavitve row is for the logo. By default there should be a button Naloži logotip to upload logo from the media library with brief description that only PNG and SVG format are allowed. Concerning the heigth of the logo, I'm not sure, but it should be adaptable and definately not to small or to big for big screens. Now there's a slider for the heigth that is not functional I think, at least the logo does not visibly resize.
- entirely remove existing obdobje - od obdobje - do meta fields as I won't need or use them

There should probably be a Shrani nastavitve separate button on the bottomto save all the settings apart from updating/posodobi the CPT Ekran.

# Oblikovanje

On the second tab the user should have a neat simple GUI for stylig the rendered table without writing CSS. All the styling should be done on this admin tab and not in the wp block editor once the shortcode is pasted there. This admin editor should have controls to style every single element on the front end separately, from ala visible top row (date, Urni izobraževanj text, pagination, logonad current time)to events cotent cells and bottom fixed row. Depending on the content, there should be fields to paste hex coor code and color pickers for row backgrounds and fonts, an option to alternate background color of every second row (similar to existing wp tables plugins), selectable size units for responsive display of fonts (vw, rem, px), font weigth) and settings for rows padding with separate top right bottom and left fields (with clear labels) with auto column width that adapts itself based on the content of the cell.

Does it make sense to include a checkbox option to inherit setting from the theme?

# Navodila

From top to bottom, just a set of brief txt instructions #namestitev vtičnika (steps as a bulleted list), #Uporaba kratke kode (some text) #Oblikovanje tabele (some text explanation or list) #CSS classes (a list of all CSS classes for the table with brief location descriptions to manually add CSS).

## FRONTEND

For development and testing purposes, here's a URL with working shortcode/table you can use to analyze the code when necessary: https://upi.wptweaker.com/ekran


All "Ekrani" created from the admin menu should have the following structure:

1) Top of the page, from left to right, row should be divided in 20/60/20 Left: Uploaded logo some space and date displayed as ponedeljek, 17. november 2025. Middle/center section: displayed exactly as Urnik izobraževanj followed by small space and pagination as stran 2/3. Right corner: live time in 24h format as is - 10:45:32
Top row should be always visible, even if there are no events to be displayed

2) Content rows with data from API: the header row should include and display the following values from left to right: ČAS (API reference "start" - "end"), IZOBRAŽEVANJE "name", PREDAVATELJ "teacher", UČILNICA "room" and NADSTROPJE "floor"

3) The bottom row of the viewport should always stay fixed (similar to header) to bottom, no columns, just one big cell, and allow for any txt to be displayed. So, if there are no content rows or only 3 for example, I dont't want entire table to shrink and pull bottom row up but to always stay in place. Bottom row, as top row, must also always be visible and displayed, when it's turned on.

4) When there are no events displayed in content rows, I want you to always hide the table top row (Čas IZOBRAŽEVANJE, PREDAVATELJ etc.), but always display top and bottom row. In the middle of the screen there should be text displayed as is now. 

No matter how many rows a user selects in the Nastavitve tab to be displayed, the viewport should always be edge to edge and should never display vertical and horizontal scroll bars, it should always be filled with content rows, so does it makes sense to limit them to say 14?

All your instructions should be beginner friendly as I am not a programmer, so be very precise. Always provide a clear path where code should be pasted and provide step by step instructions for complex tasks. Never provide small code patches and bits for me to put inside some file, always output entire file of code to be replaced.

## 4. API notes

These are API developers notes and I suggest you memorize them for future reference unless I upload new data:

[begin api notes]
Zadeva deluje tako, da v Snipiju pripraviš nov "Ekran", tam tudi lahko filtriraš vse potrebno in potem pokličeš API preko GET metode. Metoda sprejme 3 parametre in sicer:
key - to je zadnji del URL-ja za ekran, npr. https://urnik.snipi.si/BdhBcrRm8 - za tega bi bil key: BdhBcrRm8
dateFrom
dateTo - oba datuma v ISO 8601 formatu (yyyy-MM-dd)

Primer klica za vaš ekran pri refereratu: https://upi.snipi.si/api/Scheduler/GetTimeSlots?dateFrom=2025-11-11&dateTo=2025-11-30&key=BdhBcrRm8

Vrne seznam objektov, primer:

{
        "selected": true,
        "objectId": 10739,
        "type": 1,
        "uuid": "Lecture|10739",
        "project": "Osnovna šola",
        "name": "Angleščina OŠO",
        "location": "UPI - ljudska univerza Žalec",
        "room": "P-8",
        "floor": "1. nadstropje",
        "start": "2025-11-13T16:00:00",
        "end": "2025-11-13T19:00:00",
        "organizer": "UPI Žalec",
        "teacher": "Kolar Kristian",
        "subjects": [
            {
                "studyName": "Osnovna šola za odrasle",
                "studyCode": "OŠO",
                "subjectName": "angleščina",
                "year": 7,
                "studyType": 5
            },
            {
                "studyName": "Osnovna šola za odrasle",
                "studyCode": "OŠO",
                "subjectName": "angleščina",
                "year": 8,
                "studyType": 5
            },
            {
                "studyName": "Osnovna šola za odrasle",
                "studyCode": "OŠO",
                "subjectName": "angleščina",
                "year": 9,
                "studyType": 5
            }
        ],
        "subjectText": "angleščina 7.r (OŠO), angleščina 8.r (OŠO), angleščina 9.r (OŠO)",
        "displayNameWithIcon": "📖 Angleščina OŠO",
        "timeDisplay": "13. 11. 2025 16:00-19:00",
        "timeDisplayTimeOnly": "16:00 - 19:00"
    }

Opis:
"selected": vedno true, za interno uporabo
"objectId": interni ID dogodka
"type": tip dogodka in sicer: 0 - delavnica (navadna skupina), 1 - predavanje (šolski programi), 2 - konzultacija (šolski programi), 3 - izpit, 4 - ostale rezervacije
"uuid": globalni enolični ID dogodka
"project": Ime projekta
"name": Ime za izpis
"location": Ime lokacije
"room": Ime prostora
"floor": Ime nadstropja
"start": začetek (ISO 8601) yyyy-MM-ddThh:mm:ss,
"end": zaključek (ISO 8601)
"organizer": Ime organizatorja
"teacher": Imena učiteljev
"subjects": seznam predmetov (če obstajajo)
"subjectText": našteti vsi predmeti v eni vrstici
"displayNameWithIcon": ime za izpis + ikona - Predavanja, Konzultacije, Izpiti dobijo pred imenom še ikono, da se takoj ločijo od delavnic
"timeDisplay": besedilo s formatiranim trajanjem dogodka
"timeDisplayTimeOnly": besedilo samo ura dogodka brez datuma

Opis "subjects"
"studyName": Ime šolskega programa
"studyCode": kratica šolskega programa
"subjectName": ime predmeta
"year": letnik/razred
"studyType": tip šolskega programa: 0 - ostalo, 1 - PTI, 2 - SSI, 3 - SPI, 4 - PT, 5 - OŠ, 6 - VŠ, 7 - GIM, 8 - NPI, 9 - VSŠ, 10 - MT

[end api notes]
---

## 5. ChatGPT Codex initial analysis summary made on november 18th 2025

Finally, now that you know the context nd have instructions, I want you to include your initial analisys summary in the code fixes, in particular allyo dscribe undr Gaps & likely broken/unfinished pieces
- Version mismatch – The plugin header says Version: 2.5, but the constant used for cache-busting/enqueues is 2.3.2, so browsers may not get updated assets when you release new builds. -I suggest you remove the version constant. Can you do that? Do I need cache busting?

- Date range UI is unused – Admins can still pick “Obdobje – od/do”, but the REST controller ignores those values and always fetches “today” by overwriting date_from/date_to. This can confuse users who expect the UI to matter. - provide a fix, this is crucial

- Custom CSS never loads – _snipi_custom_css is saved but never enqueued or injected on the frontend or preview, so the “Oblikovanje” tab has no effect beyond storing text. (The string doesn’t appear in any other file.) - provide a fix

- REST payload not fully used on the client – The GET route returns bottom_row and display_bottom, but front.js only reads data.items and data.logo_url. That means toggling the bottom row or editing its content won’t update dynamically after the first server render; visitors need a hard refresh to see admin changes. - provide a fix

- Admin preview hook missing – admin.js never calls the preview REST endpoint that SNIPI_Admin::admin_assets() localizes (nonce + URL), so admins can’t actually see a live preview of styling/entries from the metabox without manually refreshing a frontend page. - there was ment to be a live preview screen of the Oblikovanje tab but never got it to work. - rework and fix it