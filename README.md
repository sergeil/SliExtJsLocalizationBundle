Bundle provides a command ( sli:update-extjs-translation ) which can be used to generate translation files from your extjs classes. In order
the task to detect your extjs class it must comply with the following rules:
- Before the very first translation token this comment must be placed - // l10n
- Translation tokens must be suffixed with "Text", for example - firstnameText
- One blank line must follow after translation tokens and other class members ( other properties, methods etc )
- Do not add any blank lines between translation tokens ( you might be tempted to do this to group your tokens semantically )

See for Tests/*/resources for examples of valid extjs classes.