# Change Log

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
