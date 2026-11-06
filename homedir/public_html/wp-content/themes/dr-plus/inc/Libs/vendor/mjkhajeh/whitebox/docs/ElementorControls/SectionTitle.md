# SectionTitle Control

## Overview
The `SectionTitle` class in MJ Whitebox ElementorControls provides a flexible way to add section title controls to Elementor widgets, including tag, icon, title, and link, as well as advanced style options.

## Methods

### settings($object, array $args = [])
Initializes the section title settings and adds controls to the Elementor widget.
- **$object**: Elementor widget instance
- **$args**: Array of section, excludes, controls, etc.

### controls($object, $args = [])
Adds section title controls (tag, icon, title, link) to the widget.
- **$object**: Elementor widget instance
- **$args**: Array of control overrides and options

### default_controls()
Returns the raw array of default section title control definitions without adding them to a widget.

### row_style($object, $args = [])
Adds style controls for the section title row.
- **$object**: Elementor widget instance
- **$args**: Style options

### icon_style($object, $args = [])
Adds style controls for the section title icon.
- **$object**: Elementor widget instance
- **$args**: Style options

### title_style($object, $args = [])
Adds style controls for the section title text.
- **$object**: Elementor widget instance
- **$args**: Style options

### arrows_style($object, $args = [])
Adds style controls for slider arrows in the section title.
- **$object**: Elementor widget instance
- **$args**: Style options

### styles($object, $icon = true, $arrows = false, $args = [])
Adds all style controls (row, icon, title, arrows) as needed.
- **$object**: Elementor widget instance
- **$icon**: Whether to add icon style controls
- **$arrows**: Whether to add arrows style controls
- **$args**: Array of style options

## Controls Provided
- **Tag**: HTML tag for the title (h1, h2, etc.)
- **Icon**: Icon for the title
- **Title**: Section title text
- **Link**: URL for the title

## Usage Example
```php
SectionTitle::settings($this, [
    'section' => [
        'name' => 'my_section_title',
        'label' => 'Section Title',
    ],
]);
SectionTitle::styles($this);
```

## Integration Tips
- Use the `settings` and `styles` methods in your Elementor widget for full control over section titles.
- Customize controls and styles via the `$args` array for advanced use cases.
