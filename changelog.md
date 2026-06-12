# Changelog

## 2.0 (2026-06-12)

Update for PHP 7.4 – 8.4 and current e107 v2. Based on Mentions 1.6.4 by Arun S. Sekher.

### Security
- Debug logging no longer writes plaintext files (mentioned usernames, full e-mail content) into the web-accessible plugin directory; logging is now disabled by default and, when enabled via the `MENTIONS_DEBUG` constant, writes to the protected e107 system log folder
- Auto-complete endpoint (`index.php?mq=`) hardened: input length capped by the *max characters* pref, SQL value escaped with `toDB()`, `LIKE` wildcards (`%`, `_`) neutralized, result count limited in SQL by the *suggestion limit* pref, banned users excluded from suggestions
- Auto-complete query is URL-encoded in JavaScript before being sent

### Fixed
- Fatal error when opening the plugin admin page on PHP 8: `MENTIONS_ADMIN_*` constants were defined in `init()` but used as class property defaults, which are evaluated earlier; constants are now defined at file level
- Notification e-mails: the dispatch loop indexed entries that no longer existed after duplicate/self filtering — on PHP 8 it raised "undefined array key" warnings and could skip mentioned users; rewritten as a bounded foreach
- Comment notifications never registered: the *contexts* preference (stored as string) was compared strictly against an integer — condition was always false
- Event callbacks (`chatbox`, `comment`, `forum`) were instance methods registered as static callbacks — unreliable/fatal on PHP 8; converted to static entry points
- Mentioning a non-existent user no longer raises "array offset on bool" warnings (PHP 7.4/8) — missing DB rows are handled
- The *highlight first suggestion* preference was never passed to the JavaScript settings — popup always used the library default
- PHP 8.1+ deprecations fixed: `trim(null)` in e-mail subject, `ctype_digit()` with int argument, `ltrim(null)`
- Undefined index warnings fixed throughout (`e_url_list` prefs, forum event data, missing plugin prefs — defaults provided)
- Invalid e-mail header `X-e107-Plugin : Mentions-Plugin-v` (space before colon) corrected
- `plugin.xml`: `LAN_CONGIGURE` typo corrected to `LAN_CONFIGURE`

### Changed
- `plugin.xml`: version 2.0
- README rewritten

### Removed
- External donation/widget scripts (ko-fi, bitcoin, GitHub buttons) from the admin *Project Info* panel
