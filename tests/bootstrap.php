<?php
/**
 * WP_Mock bootstrap — no real WordPress/database needed. Tests target pure
 * logic (sanitize/validate callbacks in inc/content-fields.php,
 * inc/post-types.php, inc/uploads.php) with every WP core function mocked
 * per-test via WP_Mock::userFunction(). See CONTENT_MODEL.md for the field
 * rules each test in tests/ is asserting.
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

WP_Mock::bootstrap();

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

// inc/content-fields.php has no side-effecting top-level code (no
// add_action()/register_post_type() calls) — safe to require once here for
// every test, unlike inc/post-types.php or inc/home-fields.php, which
// register real WordPress hooks at include time and would need those
// functions stubbed before the file itself can even load.
require_once dirname( __DIR__ ) . '/inc/content-fields.php';
