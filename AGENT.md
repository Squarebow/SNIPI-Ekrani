### SNIPI ekrani - WP plugin instructions for ChatGPT Codex

Everything in terms of code logic, structure, functionalities and styling should be memorized for further code editing. I will write instructions and context in English, however the user interface of the plugin (admin and frontend) must always be in Slovenian. If there's a typo, please ask to be sure what I wanted o say if you can't get it from the context of the description.

## 1 Brief description of the project

This is my Wordpress plugin that pulls the data from a bespoke CRM called SNIPI via API and displays it on frontend in a html table via shortcode. It is primarily used to display a live timetable of school activities on a 16:9 TV in the hallway so the attendees can find classes, classrooms etc., but it should be flexible and responsive for other devices as well (desktop, tablet, mobile). By default, the plugin must always display events fetched via API for the current day, for today, starting from the earliest to the latest. The list of events of the frontend must be visible regardless of when the event begins, so it should display events from 00:00 hours to 23:59:59 and hide from view only the events that are past/over. For exampe, if an event is scheduled fom 9:00 11:00, and current time is 8:36, it should be displayed in the table nontheless ad disappear from the display at 11:01. The exception is the table page when there are no events scheduled like holidays, weeekends etc.; in that case only a message is displayed in the center of the screen, but I will further define specfics later in the document.

## 2 Key tech considerations for Codex

- You are modifying the WordPress plugin SNIPI ekrani. You must strictly follow the structural and styling rules below. Do NOT invent new structures, new classes, or new markup. Only use what is defined here or what is native WordPress admin UI.
- The plugin should never under no circumstances use any form of caching as the purpose of the frontend table is to display live events
- I do not ever want to use Jquery - in any form. Remove any trae of it if it exists. Until now I used Vanilla JS but as it's very spartan and limited, I shall prefer you to use a modern UI building framework for admin panel such as React JS
- Never replace anything that is working without asking me first for my approval. You may always suggest QoL improvements of code, UX functionality, but if I ask for something in particular, focus only on that
- The plugin will be publicly abvailable and distributed to other schools so it should be as "agnostic" as possible for exotic technologies. It should work out of the box in Wordpress
- The development version on Github is the stable version I use on my dev server as a WP install for testing
- For any code you generate, please use tabs for indentation and not spaces. If you need to enqueue any scripts, ask me questions to help me figure out whether we should use conditional loading or if we should enqueue globally. In addition, when generating CSS, please follow BEM methodology. In terms of performance, make sure the plugin loads JS and CSS files only on the pages where the table is actually displayed (shortcode is used) and nowhere else globally. Always apply wordpress best coding practices. Never guess or speculate, ask me for clarification.
- From now on, number the plugin versions after every fix by one minor step, e.g. from 1.0 currently to 1.0.1 When a major upgrade is due, I will tell you. Change the version in plugin file and in README.md

- My stack and workflow: my webserver is nginx with maria db. Live wp installation on a subdomain for development at https://upi.wptweaker.com. I use Blocksy PRO theme and Kadence blocks pro (Gutenberg), use Super Page Cache WP plugin by themeisle with cloudflare integration and redis for caching. Tools: Filezilla for ftp, VS Code with github integration, git and Github for windows desktop GUI (avoid giving terminal prompts when possible and give instructions for desktop version). Chrome and Chrome dev tools

I Will describe the logic and the features as I WANT THEM TO be, not as they currently are

### 3 Plugin structure and functionalities description

## BACKEND

3.1 
3.2 

# Nastavitve Features
Work in progress

Any changes, additions, updates and upgrades will be listed below this line and given as a descriptive prompt with instructions.

# Oblikovanje page (TODO)

On the second tab the user should have a neat simple GUI for stylig the rendered table without writing CSS. All the styling should be done on this admin tab and not in the wp block editor once the shortcode is pasted there. This admin editor should have controls to style every single element on the front end separately, from ala visible top row (date, Urni izobraževanj text, pagination, logo and current time)to events cotent cells and bottom fixed row. Depending on the content, there should be fields to paste hex coor code and color pickers for row backgrounds and fonts, an option to alternate background color of every second row (similar to existing wp tables plugins), selectable size units for responsive display of fonts (vw, rem, px), font weigth) and settings for rows padding with separate top right bottom and left fields (with clear labels) with auto column width that adapts itself based on the content of the cell.

# Navodila (TODO)

From top to bottom, just a set of brief txt instructions #namestitev vtičnika (steps as a bulleted list), #Uporaba kratke kode (some text) #Oblikovanje tabele (some text explanation or list) #CSS classes (a list of all CSS classes for the table with brief location descriptions to manually add CSS).

## FRONTEND

For development and testing purposes, here's a URL with working shortcode/table you can use to analyze the code when necessary: https://upi.wptweaker.com/ekran

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
