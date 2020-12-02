# Change Log

## 2.1.4
## Fixed
- Missing `<?php` tag

## 2.1.3
## Fixed
- HumHub 1.6 compatibility (#17, #19)

## 2.1.2
## Fixed
- Fixed an issue that prevented plugin from removing (#14)


## 2.1.1
## Changed
- Improved migration failures (#12)

## Fixed
- Fixed module id (#12)


## 2.1.0
## Added
- Build script to split readme to according sections (#6)

## Changed
- Logo and screenshots for marketplace
- Updated empty files (v15)

## Fixed
- Fixed marketplace id
- Fixed HumHub minimum version (#5)


## 2.0.0
## Added
- JWT support
- Option to convert `.doc .odt .xls. ods .ppt .odp .txt .csv` files to Office Open XML
- `de`, `fi`, `fr`, `hr`, `hu`, `it`, `ja`, `nl`, `pl`, `pt_br`, `vi` translations

## Changed
- Editors will now use portal language if user language is null
- `author` and `created` fields are now passed correctly
- `firstname` and `lastname` fields are deprecated, using `name` instead
- Allow editing only for Office Open XML formats
- Issue with `/` at the end of Document Service url
- Better way to pass editors config
- Files properly marked as updated now
