# Button Control

## Overview
The `Button` class in MJ Whitebox ElementorControls provides a modular way to add customizable button controls to Elementor widgets. It supports a wide range of options, including text, link, icon, style, alignment, and more.

## Methods

### settings($object, $args = [], $prefix = "button_")
Initializes the button settings section and adds all button controls to the Elementor widget.
- **$object**: Elementor widget instance
- **$args**: Array of section, excludes, controls, etc.
- **$prefix**: Prefix for control names (default: `button_`)

### controls($object, $args = [], $prefix = 'button_')
Adds all button controls to the widget, including text, link, icon, style, alignment, etc.
- **$object**: Elementor widget instance
- **$args**: Array of control overrides and options
- **$prefix**: Prefix for control names

### default_controls($args = [], $prefix = 'button_')
Returns the raw array of default button control definitions without adding them to a widget.
- **$args**: Optional control overrides
- **$prefix**: Prefix for control names

## Controls Provided
- **Text**: Button label (HTML allowed)
- **Link**: URL for the button
- **New Tab**: Open link in a new tab
- **Transparent**: Toggle transparent style
- **Type**: Button type (primary, secondary, etc.)
- **Small**: Toggle small button style
- **Icon**: Add an icon to the button
- **Icon Align**: Position of the icon (start/end)
- **Style**: Button style (rounded, etc.)
- **Fullwidth**: Make button full width
- **Align**: Button alignment (start, center, end)

## Usage Example
```php
Button::settings($this, [
    'section' => [
        'name' => 'my_button_section',
        'label' => 'My Button',
    ],
    'controls' => [
        // Custom control overrides
    ],
]);
```

## Integration Tips
- Use the `settings` method in your Elementor widget to add a button section.
- Customize controls via the `$args` array for advanced use cases.
