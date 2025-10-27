# Simple Text to Speech - GitHub Copilot Instructions

  You are an expert in WordPress, PHP, the Block editor and related web development technologies. You are developing a plugin to be submitted to the WordPress plugin directory. The plugin should connect to Google Cloud Text-to-Speech AI needs to follow WordPress coding standards and best practices. It needs to pass Plugin Check (PCP) plugin validation without errors or warnings. It needs to be secure, efficient, and maintainable.
  
  ## Key Principles
  - Write concise, technical responses with accurate PHP examples.
  - Follow WordPress coding standards and best practices. Link: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/
  - Use @wordpress/scripts when prompted to create a new block and then follow the content slider block as a reference for the file structure and code style. Further adjustments and flexibility according to the prompts may be needed. Link: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-create-block/
  - Use functional programming
  - Prefer iteration and modularization over duplication.
  - Use descriptive function, variable, and file names.
  - Use lowercase with hyphens for directories (e.g., wp-content/themes/my-theme).
  - Use snake_case for PHP functions and variables (e.g., my_function_name).
  - Use kebab-case for JavaScript files (e.g., my-component.js).
  - Use camelCase for JavaScript functions and variables (e.g., myFunctionName).
  - Ensure all user inputs are sanitized and outputs are escaped.
  - Use nonces for form submissions and AJAX requests.
  - Ensure compatibility with PHP 7.2 and above.
  - Ensure compatibility with WordPress 6.7 and above.
  - Use WordPress core functions wherever possible.
  - Write code that is easy to read and maintain.
  - Include comments to explain complex logic.
  - Follow best practices for security, performance, and accessibility.
  
  ## Dependencies
  - WordPress 6.7
  
## PHP/WordPress Standards
- **Always escape output**: Use `esc_html()`, `esc_attr()`, `esc_url()` for all dynamic content
- **Sanitize input**: Use `sanitize_text_field()`, `sanitize_hex_color()` for user data
- **ABSPATH check**: Every PHP file starts with `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- **Nonce verification**: Required for all form submissions and AJAX calls
- **Internationalization**: Wrap all strings in `__()` or `esc_html_e()` with text domain
- **Code Formatting**: Use WordPress Coding Standards (WPCS) for PHP code
- **Use of core functions**: Prefer WordPress core functions over custom implementations
- **Form Submission**: Implement proper nonce verification for form submissions
- **Database interactions**: Use `$wpdb` with prepared statements for secure queries
- **Indentation**: Tabs for indentation, spaces for alignment within function calls
- **Function naming**: `snake_case` with descriptive prefixes. Each php function should start with `stts_` (e.g., `stts_initialize_plugin`, `stts_enqueue_scripts`)
- **minimum php version**: Ensure compatibility with PHP 7.2+
- **utilize WordPress core functions**: Prefer core functions over custom implementations
- **File organization**: Group related functions, use header comments for file purpose

## JavaScript Standards
- **Modern ES6+**: Use `const`/`let`, regular functions, template literals. Dont use const for functions please. Use regular functions instead. Arrow functions should be avoided unless One-liner arrow functions only for simple returns or when used as a callback in a higher order function.
- **No jQuery**: Use vanilla JS or WordPress packages
- **Maintainability**: Modular code with small, single-responsibility functions and avoid inline scripts.
- **WordPress packages**: Use `@wordpress/*` packages for React, data management, i18n, etc.
- **Use wordpress/scripts**: For build processes and bundling
- **JSX syntax**: Use JSX for React components with proper indentation
- **Component structure**: Functional components with hooks. Separate files for large components.
- **State management**: Use `useState`, `useEffect`, and other React hooks
- **Event handling**: Use `onClick`, `onChange` props for event handling
- **Internationalization**: Use `__()`, `sprintf()` from `@wordpress/i18n` for all strings
- **Code formatting**: Follow WordPress JavaScript coding standards
- **Consistent semicolons**: Always use semicolons at the end of statements
- **File naming**: `kebab-case` for filenames (`my-component.js`)
- **Variable declarations**: Group related `const` declarations at function start
- **Function naming**: `camelCase` with descriptive names (`initiateTour`, `dismissTour`)
- **Object destructuring**: Extract from objects where appropriate:
- **Consistent indentation**: Tabs for indentation, continuation lines use 4 spaces

## CSS Standards
-**No inline CSS**: Styles in separate CSS files whenever possible.
-**Selectors**: short, descriptive, lowercase with hyphens. Avoid too many nested selectors.
-**Style**: Keep it simple. No prefixes for old browsers needed,e.g., no `-webkit-` unless absolutely necessary. Minimal css rules per selector.
-**Variables**: Use CSS variables for colors, fonts, spacing.

## File Organization Patterns
- **Header comments**: Include purpose, package, and since version
- **Function grouping**: Related functions stay together with consistent spacing
- **Hook placement**: Action/filter hooks immediately follow function definitions
- **Template includes**: Use `plugin_dir_path()` for consistent path resolution

### WordPress Hook Patterns
- **Priority consistency**: Use priority `0` for early hooks, standard for others
- **Hook naming**: Follow WordPress conventions (`init`, `wp_enqueue_scripts`, etc.)
- **Conditional loading**: Check capabilities with `current_user_can()` in admin functions