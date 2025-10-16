# HideItDude

## Description

HideItDude is a WordPress plugin designed to hide various elements of the WordPress backend from users with subscriber roles. It provides granular control over what subscribers can see and access in the WordPress admin area, creating a cleaner and more focused user experience.

## Features

### Core Functionality
- **Admin Menu Control**: Hide specific admin menu items from subscribers
- **Admin Bar Management**: Hide specific admin bar items
- **Dashboard Widget Control**: Remove unwanted dashboard widgets
- **Notification Suppression**: Hide WordPress core and plugin notifications
- **Interface Cleanup**: Hide Screen Options and Help tabs
- **Account Menu Replacement**: Replace complex account menu with simple logout button
- **Profile Access Control**: Hide profile menu access for subscribers
- **Login Redirection**: Redirect subscribers to homepage instead of admin area
- **Complete Admin Bar Removal**: Option to completely disable admin bar for subscribers
- **Cleanup Options**: Configurable data removal on plugin uninstall

### Advanced Features
- **Custom Database Tables**: Uses dedicated tables instead of bloating wp_options
- **Role-Specific Targeting**: Only affects users with subscriber role
- **Tabbed Interface**: Organized settings with easy navigation
- **Keyboard Shortcuts**: Ctrl+S to save settings
- **Select All/None**: Quick selection tools for checkbox groups
- **Responsive Design**: Mobile-friendly admin interface
- **Lazy Loading**: Settings loaded only when needed for optimal performance
- **Defensive Database Operations**: All queries include table existence checks
- **Caching**: Database results cached to minimize queries

## File Structure

```
hideitdude/
├── hideitdude.php           # Main plugin file
├── README.md                # This documentation file
├── LICENSE                  # GPL v2 license (add manually)
├── uninstall.php            # Cleanup on deletion
├── index.php                # Security stub
├── assets/
│   ├── admin.css            # Admin interface styles
│   ├── admin.js             # Admin interface JavaScript
│   └── index.php            # Security stub
└── includes/
    ├── class-database.php   # All database operations
    ├── class-admin.php      # Admin interface
    ├── class-core.php       # Core functionality
    └── index.php            # Security stub
```

## Installation

1. Upload the `hideitdude` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Tools → HideItDude to configure settings
4. Add LICENSE file manually (GPL v2 text)

## Database Structure

The plugin creates a custom table `wp_hideitdude_settings` with the following structure:

```sql
CREATE TABLE wp_hideitdude_settings (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    setting_key varchar(191) NOT NULL,
    setting_value longtext,
    PRIMARY KEY (id),
    UNIQUE KEY setting_key (setting_key)
)
```

### Why Custom Tables?

- Prevents wp_options table bloat
- Improved query performance
- Better data organization
- Cleaner uninstall process

## Configuration Options

### Admin Menus Tab
Control which admin menu items are hidden from subscribers:
- Dashboard
- Posts
- Media
- Pages
- Comments
- Appearance
- Plugins
- Users
- Tools
- Settings

### Admin Bar Tab
Control which admin bar items are hidden from subscribers:
- WordPress Logo
- Site Name
- Updates
- Comments
- New Content
- Edit
- My Account

### Dashboard Tab
Control which dashboard widgets are hidden from subscribers:
- At a Glance
- Activity
- Quick Draft
- WordPress Events and News

### General Tab
Additional hiding options:
- **Hide Notifications**: Suppresses all WordPress and plugin notices
- **Hide Screen Options**: Removes Screen Options tab
- **Hide Help Tab**: Removes Help tab
- **Replace Account Menu**: Replaces account dropdown with logout button
- **Hide Profile Menu**: Removes profile menu access

### Redirect & Bar Tab
Advanced control options:
- **Redirect Subscribers**: Automatically redirect subscribers to homepage
- **Hide Admin Bar**: Completely remove admin bar for subscribers

### Cleanup Tab
Data management options:
- **Delete Data on Uninstall**: Remove all plugin data when plugin is deleted (enabled by default)

## Security Features

### SQL Injection Prevention
- All queries use `$wpdb->prepare()` with proper placeholders
- Table names use `%i` placeholder (WordPress 6.2+)
- Defensive table existence checks before all operations
- Proper charset collate handling

### XSS Prevention
- All output escaped using appropriate functions
- HTML: `esc_html()`
- Attributes: `esc_attr()`
- URLs: `esc_url()`
- Text areas: `esc_textarea()`

### CSRF Protection
- Nonce verification on all form submissions
- Capability checks in both render and save methods
- Proper action verification

### Additional Security
- No direct file access prevention
- No debug code in production
- Proper error handling with WP_Error
- Role-based functionality restrictions

## Performance Optimization

### Caching Strategy
- Settings cached after first retrieval
- Cache cleared on data modification
- Lazy loading pattern throughout

### Database Optimization
- Single query to load all settings
- Defensive table checks with caching
- Null coalescing for array access
- Minimal database operations

### Asset Loading
- Scripts/styles loaded only on plugin pages
- Version-based cache busting
- Footer loading for JavaScript
- Conditional enqueuing

## User Roles Targeted

This plugin specifically targets users with:
- Subscriber role as their primary role
- Only subscriber capabilities
- Users who have subscriber role exclusively

**Note**: Users with multiple roles or higher privileges are not affected.

## Plugin Hooks

### Actions

#### hideitdude_before_save_settings
Fires before settings are saved.

```php
add_action( 'hideitdude_before_save_settings', 'my_custom_function' );
function my_custom_function() {
    // Your code here
}
```

#### hideitdude_after_save_settings
Fires after settings are saved.

```php
add_action( 'hideitdude_after_save_settings', 'my_custom_function' );
function my_custom_function() {
    // Your code here
}
```

### Filters

#### hideitdude_default_settings
Filter default plugin settings.

```php
add_filter( 'hideitdude_default_settings', 'my_custom_defaults' );
function my_custom_defaults( $defaults ) {
    $defaults['hide_notifications'] = '1';
    return $defaults;
}
```

#### hideitdude_admin_menus
Filter available admin menus for hiding.

```php
add_filter( 'hideitdude_admin_menus', 'my_custom_menus' );
function my_custom_menus( $menus ) {
    $menus['custom-menu.php'] = 'Custom Menu';
    return $menus;
}
```

## Troubleshooting

### Common Issues

**Settings not saving**:
- Check file permissions
- Verify database table creation
- Ensure WordPress admin privileges
- Check error logs

**JavaScript not working**:
- Check for plugin conflicts
- Verify jQuery is loaded
- Check browser console for errors
- Clear browser cache

**Styles not loading**:
- Clear WordPress cache
- Check asset file paths
- Verify CSS file permissions
- Check for theme conflicts

### Debug Mode
Enable WordPress debug mode to troubleshoot:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
```

## Compatibility

- **WordPress Version**: 6.2 and above (uses modern `$wpdb->prepare()` with `%i`)
- **PHP Version**: 7.4 and above
- **Database**: MySQL 5.6 and above
- **Browsers**: All modern browsers

## Development

### Code Standards
- WordPress PHP Coding Standards
- Tabs for indentation
- Yoda conditions
- Comprehensive escaping
- Proper documentation

### Security Practices
- Never trust user input
- Escape all output
- Verify nonces
- Check capabilities
- Use prepared statements

### Architecture
- Separation of concerns
- Single responsibility principle
- No logic in main file
- Lazy loading patterns
- Defensive programming

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Follow WordPress coding standards
2. Include proper documentation
3. Test thoroughly before submitting
4. Ensure all security checks pass
5. Update README if needed

## License

This plugin is licensed under GPL v2 or later.
- Custom database tables
- Performance optimization
- Comprehensive documentation
