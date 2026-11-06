# Localization

The **MJ Whitebox** library includes localization support for Persian (fa_IR) users. This ensures that the plugin is accessible to Persian-speaking audiences.

## Files
- `languages/mj-whitebox-fa_IR.po`: Translation file for Persian.
- `languages/mj-whitebox-fa_IR.mo`: Compiled binary translation file.
- `languages/mj-whitebox.pot`: Template file for translations.

## Adding New Translations
1. Use the `.pot` file as a template.
2. Create a new `.po` file for your desired language.
3. Compile the `.po` file into a `.mo` file.
4. Place the new files in the `languages` directory.

## Loading Translations
To load translations when using Composer, add the following to your plugin or theme:

```php
load_textdomain( 'mj-whitebox', 'vendor/mjkhajeh/whitebox/languages/mj-whitebox-' . get_locale() . '.mo' );
```

The plugin automatically loads translations based on the WordPress site language setting.
