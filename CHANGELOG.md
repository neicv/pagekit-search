## 0.1.7.5 (April 24, 2018)

### Fixed

- Fixed Highlight bug (unicode pos failure)
- Fixed "Pages", "Driven/Listings" plugin SQLite unicode compatible compare bug 
    (Case-insensitive LIKE in SQLite)

### Added
- added plugin support "Driven / Listing ver 1.0.6 " MySQL Database  
- added :
General Change:
(betta release)
improve compatible with Case-insensitive LIKE in SQLite 
Any other character matches itself or it's lower/upper case equivalent (i.e. case-insensitive matching). (A bug: SQLite only understands upper/lower case for ASCII characters. The LIKE operator is case sensitive for unicode characters that are beyond the ASCII range. For example, the expression 'a' LIKE 'A' is TRUE but 'æ' LIKE 'Æ' is FALSE.)."


## 0.1.7.4 (April 18, 2018)

### Fixed

- Fixed Highlight bug (unicode pos failure)

### Added
- added "#sahrp links item " in plugin "Driven / Listing ver 1.0.6 "
- added Info Tab 

## 0.1.7.3 (April 13, 2018)

### Fixed
issue #7 Extension incompatible with sql_mode=only_full_group_by
- Fixed MySQL <= 5.7.5 support (ONLY_FULL_GROUP_BY)

### Added
- added plugin support "Driven / Listing ver 1.0.6 " SQLite Only