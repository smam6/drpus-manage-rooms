# Slider Control

## Overview
The `Slider` class in MJ Whitebox ElementorControls provides advanced slider controls for Elementor widgets, including responsive slide settings, autoplay, loop, and navigation arrow styles.

## Methods

### settings_controls($object, $args = [])
Adds slider settings controls to the Elementor widget, including slide count/type/space for desktop, tablet, and mobile, as well as autoplay, arrows, and loop options.
- **$object**: Elementor widget instance
- **$args**: Array of section, excludes, controls, etc.

### options_controls($object, $args = [], $add_display_conditions_to_section = false)
Adds slider options controls, excluding slide count/type/space controls. Can add display conditions to the section.
- **$object**: Elementor widget instance
- **$args**: Array of control overrides and options
- **$add_display_conditions_to_section**: Whether to add display conditions

### autoplay_controls($object, $args = [])
Adds autoplay controls (enable/disable, interval) to the widget.
- **$object**: Elementor widget instance
- **$args**: Array of control overrides and options

### arrows_style_controls($object, $arrows_btn_selector, $args = [])
Adds style controls for slider navigation arrows.
- **$object**: Elementor widget instance
- **$arrows_btn_selector**: CSS selector for arrow buttons
- **$args**: Style options

### dots_style_controls($object, $dot_selector, $args = [])
Adds style controls for slider pagination dots.
- **$object**: Elementor widget instance
- **$dot_selector**: CSS selector for dots
- **$args**: Style options

## Controls Provided
- **Slides Type**: Count or auto for desktop/tablet/mobile
- **Visible Slides**: Number of visible slides
- **Slides Space**: Space between slides
- **Autoplay**: Enable/disable and interval
- **Show Arrows**: Toggle navigation arrows
- **Show Dots**: Toggle pagination dots
- **Next Arrow Icon**: Icon for the next arrow
- **Previous Arrow Icon**: Icon for the previous arrow
- **Loop**: Enable/disable looping

## Usage Example
```php
Slider::settings_controls($this);
Slider::arrows_style_controls($this, '.my-slider-nav-btn');
```

## Integration Tips
- Use the `settings_controls` method for full slider configuration.
- Use `arrows_style_controls` to style navigation arrows as needed.
- Customize controls via the `$args` array for advanced use cases.
