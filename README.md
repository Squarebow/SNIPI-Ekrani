# SNIPI Ekrani

**Version:** 2.3.5
**Author:** Aleš Lednik (SquareBow)  
**Requires WordPress:** 6.7  
**Tested up to:** 6.7  
**Requires PHP:** 8.1
**License:** GPLv2 or later

SNIPI Ekrani je WordPress vtičnik za prikaz urnikov in aktivnosti v živo na velikih zaslonih iz programa **Snipi**.  
Samodejno upravlja z **paginacijo**, **predvajanjem strani**, **podpira 16:9 responsive layout**, in **interval osveževanja podatkov**.

Vtičnik administratorjem omogoča dodajanje več ekranov, konfiguriranje obdobja prikaza podatkov (privzeto za tekoči dan) ter oblikovanje tabele za prikaz.

---

## 📌 Features

### Frontend
- ✅ Fetches and displays schedule data from the Snipi API  
- ✅ **NEW v2.2.0:** Avtomatska TV detekcija (Samsung, LG, Sony, itd.)
- ✅ **NEW v2.2.0:** Zero-scroll TV optimizacija (dinamično skaliranje)
- ✅ **NEW v2.2.0:** Support za HD Ready, Full HD, 4K TV ekrane
- ✅ Autoplay carousel with configurable intervals 
- ✅ Automatic page refresh every 60 seconds
- ✅ Responsive 16:9 layout  
- ✅ Automatic pagination
- ✅ Live indicator for ongoing events
- ✅ Day labels for future events
- ✅ Weekend mode (shows next week events)
- ✅ Optional PROGRAM column
- ✅ Customizable logo and bottom row
- ✅ **NO jQuery** - Pure vanilla JavaScript
- ✅ **NO caching** - Always live data
- ✅ Shortcodes for embedding schedules on any page

### Admin Area
- ✅ Custom Post Type: **SNIPI ekrani**
- ✅ **NEW v2.2.0:** TV Optimizacija meta box (auto-detection settings)
- ✅ **NEW v2.1.0:** Refactored modular architecture
- ✅ **NEW v2.1.0:** 60:40 layout (settings + inline help)
- ✅ **NEW v2.1.0:** Tab system (Nastavitve | Oblikovanje)
- ✅ **NEW v2.1.0:** Slovenian code comments (30% of codebase)
- ✅ **NEW v2.1.0:** No inline CSS - all in files
- ✅ API configuration with validation
- ✅ Display configuration (autoplay, refresh interval, etc.)  
- ✅ Custom CSS editor with live preview
- ✅ Logo upload with height control
- ✅ WYSIWYG editor for bottom row
- ✅ WordPress native styling
- ✅ Custom CSS/JS enqueues located in `assets/`

---

## 🏗️ Architecture (v1.2.0)

### Modular Structure

```
snipi-ekrani/
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin panel (60:40 layout, tabs)
│   │   ├── admin-styling.css      # Styling page specific
│   │   └── front.css              # Frontend table styling
│   ├── js/
│   │   ├── admin.js               # Admin functionality (vanilla JS)
│   │   ├── admin-styling.js       # Styling page JS
│   │   └── front.js               # Frontend logic (vanilla JS)
│   ├── Copy_icon_256px.svg
│   └── Live.svg
├── includes/
│   ├── Admin/
│   │   ├── class-admin-core.php         # CPT, menu, assets
│   │   ├── class-admin-meta.php         # Meta save/get logic
│   │   ├── class-admin-columns.php      # Custom columns in list
│   │   ├── class-admin-edit-screen.php  # Main edit screen
│   │   ├── class-admin-settings-tab.php # Settings tab content
│   │   └── class-admin-styling-tab.php  # Styling tab content
│   ├── Api/
│   │   ├── class-data-service.php       # API communication
│   │   └── class-rest-controller.php    # REST endpoints
│   └── Front/
│       ├── class-renderer.php           # HTML rendering
│       └── class-shortcode.php          # Shortcode handler
├── snipi-ekrani.php                     # Main plugin file
├── AGENT.md                             # Development guidelines
├── REFACTORING_CHANGELOG.md             # Refactoring details
└── README.md                            # This file
```

### Tech Stack
- **Backend:** PHP 8.3, WordPress 6.7, OOP with static classes
- **Frontend:** Vanilla JavaScript (ES5), Fetch API, Intl API
- **CSS:** Grid + Flexbox, BEM methodology
- **WP Integration:** Custom Post Type, REST API, Shortcodes
- **NO jQuery** - Pure vanilla JS
- **NO caching** - Always live data

---

## 🔧 Installation

1. Upload the plugin folder to `/wp-content/plugins/`  
2. Activate the plugin in **Plugins > Installed Plugins**  
3. Go to **SNIPI ekrani** in the WordPress admin menu  
4. Click **Dodaj ekran** to create a new screen
5. Enter your API key (from Snipi system)
6. Copy the shortcode
7. Paste the shortcode into any page or post

---

## 🎨 Admin Interface (v1.2.0)

### New UI Features:
- **60:40 Layout:** Settings (60%) + Inline help (40%)
- **Tab System:** Nastavitve | Oblikovanje
- **Sticky Sidebar:** Help stays visible while scrolling
- **WordPress Native Tabs:** Uses WP native tab styling
- **No Inline CSS:** All styling in CSS files

### Settings Tab:
- Screen name
- API key (required)
- Shortcode (with copy button)
- Rows per page / Autoplay interval / Future days
- Info box (events count today)
- Weekend mode checkbox
- Show PROGRAM column checkbox
- Logo upload with height slider
- Bottom row toggle + WYSIWYG editor

### Styling Tab:
- Custom CSS editor (dark theme)
- Preview button
- Live preview box
- CSS class reference table
- 5 practical CSS examples

---

## 💻 Usage

### Basic Shortcode:
```
[snipi_ekran id="123"]
```

### API Key Format:
The API key is the last part of your Snipi screen URL.  
Example: `https://urnik.snipi.si/BdhBcrRm8` → Key: `BdhBcrRm8`

### CSS Customization:
Add custom CSS in **Oblikovanje** tab:

```css
/* Example: Change header color */
.snipi__header {
    background: #2271b1;
    color: white;
}

/* Example: Larger title */
.snipi__title {
    font-size: 3rem;
}

/* Example: Alternating rows */
.snipi__row--alt {
    background: #f6f7f7;
}
```

---

## 🔌 API Integration

### Endpoint:
```
https://upi.snipi.si/api/Scheduler/GetTimeSlots
```

### Parameters:
- `key` - Your API key
- `dateFrom` - Start date (YYYY-MM-DD)
- `dateTo` - End date (YYYY-MM-DD)

### Example Call:
```
https://upi.snipi.si/api/Scheduler/GetTimeSlots?dateFrom=2025-11-11&dateTo=2025-11-30&key=BdhBcrRm8
```

### Response Format:
Returns array of events with fields:
- `objectId` - Event ID
- `type` - Event type (0-4)
- `name` - Event name
- `location` - Location name
- `room` - Room name
- `floor` - Floor name
- `start` - Start time (ISO 8601)
- `end` - End time (ISO 8601)
- `teacher` - Teacher name(s)
- `subjects` - Array of subjects
- `displayNameWithIcon` - Name with icon

---

## 🎯 CSS Classes Reference

### Main Elements:
- `.snipi` - Main wrapper
- `.snipi__header` - Header (logo, date, clock)
- `.snipi__title` - Main title
- `.snipi__table` - Table element
- `.snipi__table thead` - Table header
- `.snipi__row` - Event row
- `.snipi__row--alt` - Alternating rows

### Data Columns:
- `[data-snipi-col="time"]` - Time column
- `[data-snipi-col="name"]` - Event name column
- `[data-snipi-col="program"]` - Program column
- `[data-snipi-col="teacher"]` - Teacher column
- `[data-snipi-col="room"]` - Room column
- `[data-snipi-col="floor"]` - Floor column

### Special Elements:
- `.snipi__live-indicator` - Live event indicator
- `.snipi__day-label` - Future day label
- `.snipi__bottom-row` - Bottom fixed row
- `.snipi__logo` - Logo element

---

## 📋 Changelog

### v1.2.0 (February 14, 2026)
**🎉 MAJOR REFACTORING - Faza 2 Complete**

**Architecture:**
- ✅ Modularized `class-admin.php` (754 lines) into 6 clean modules
- ✅ Created `class-admin-core.php` - CPT, menu, assets (230 lines)
- ✅ Created `class-admin-meta.php` - Meta logic (150 lines)
- ✅ Created `class-admin-columns.php` - List columns (120 lines)
- ✅ Created `class-admin-edit-screen.php` - Edit UI (250 lines)
- ✅ Created `class-admin-settings-tab.php` - Settings content (200 lines)
- ✅ Created `class-admin-styling-tab.php` - Styling content (180 lines)

**UI Improvements:**
- ✅ New 60:40 layout (main content + inline help sidebar)
- ✅ Tab system instead of separate pages
- ✅ WordPress native tab styling
- ✅ Sticky help sidebar
- ✅ Responsive design (stacks on mobile)

**Code Quality:**
- ✅ **Added 350+ lines of Slovenian comments** (30% of codebase)
- ✅ **Removed ALL inline CSS** - moved to admin.css
- ✅ Centralized meta save/get logic
- ✅ Consistent validation across all fields
- ✅ Better code organization and readability

**Documentation:**
- ✅ Created REFACTORING_CHANGELOG.md (detailed changes)
- ✅ Updated README.md with v1.2.0 features
- ✅ Documented all modules and functions

### v1.1 (Previous stable)
- WordPress native success notice after saving
- Fixed autoplay to show all pages including future days
- Fixed save error and preview link
- Weekend mode uses Today + 3 days range
- Added PROGRAM column option
- WYSIWYG editor for bottom row
- Logo upload with height control
- CSS editor with preview

### v1.0.4
- Added WordPress native success notice
- Fixed autoplay pagination

### v1.0.3
- Fixed save error
- Updated preview to actual page with shortcode
- Weekend mode improvements

---

## 🛠️ Development

### Requirements:
- WordPress 6.7+
- PHP 8.3+
- Modern browser with ES6 support

### Coding Standards:
- **Tabs for indentation** (not spaces)
- **BEM methodology** for CSS
- **WordPress coding standards** for PHP
- **Slovenian comments** for all important code
- **NO jQuery** - vanilla JavaScript only
- **NO caching** - always live data

### Asset Versioning:
Assets use `filemtime()` for cache-busting:
```php
snipi_ekrani_asset_version( 'assets/css/admin.css' )
```

### Enqueue Strategy:
Assets load only where needed - not globally:
```php
if ( $is_snipi_page || $is_ekran_cpt ) {
    wp_enqueue_style( 'snipi-admin-css', ... );
}
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Follow WordPress coding standards
4. Add Slovenian comments to all important code
5. Commit changes (`git commit -m 'Add AmazingFeature'`)
6. Push to branch (`git push origin feature/AmazingFeature`)
7. Open Pull Request

---

## 📄 License

This plugin is licensed under the GPLv2 or later.

---

## 🙋 Support

For bug reports and feature requests, please use [GitHub Issues](https://github.com/Squarebow/snipi-ekrani/issues).

For custom development or support: [https://squarebow.com](https://squarebow.com)

---

## 🎓 Credits

- **Developer:** Aleš Lednik
- **Company:** SquareBow
- **API Integration:** Snipi CRM System

---

**Made with ❤️ in Slovenia**
