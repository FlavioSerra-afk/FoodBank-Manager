<?php
// phpcs:ignoreFile
/**
 * FBM Theme admin template.
 *
 * @package FoodBankManager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$opts   = get_option( 'fbm_theme', [] );
$groups = fbm_theme_groups();
?>
<div class="wrap fbm-theme">
  <h1><?php esc_html_e( 'FBM Theme', 'foodbank-manager' ); ?></h1>

  <form method="post" action="options.php" id="fbm-theme-form">
    <?php settings_fields( 'fbm_theme' ); ?>
    <div class="fbm-grid fbm-grid--vtabs">
      <!-- LEFT: vertical tab buttons -->
      <aside class="fbm-controls" role="tablist" aria-label="<?php esc_attr_e( 'Theme Sections', 'foodbank-manager' ); ?>">
        <?php fbm_render_theme_vertical_nav( $groups ); ?>
        <?php submit_button( __( 'Save Theme', 'foodbank-manager' ), 'primary' ); ?>
        <div class="fbm-utils">
          <button type="button" class="button" id="fbm-import-btn"><?php esc_html_e( 'Import', 'foodbank-manager' ); ?></button>
          <a class="button" id="fbm-export-btn" download="fbm-theme.json"><?php esc_html_e( 'Export', 'foodbank-manager' ); ?></a>
          <button type="button" class="button" id="fbm-defaults-btn"><?php esc_html_e( 'Defaults', 'foodbank-manager' ); ?></button>
          <input type="file" id="fbm-import-file" accept="application/json" hidden>
        </div>
      </aside>

      <!-- RIGHT: selected group fields + preview -->
      <main class="fbm-right">
        <div class="fbm-right-fields">
          <?php fbm_render_all_group_panels( $opts ); ?>
        </div>

        <div class="fbm-preview">
          <div class="fbm-preview__bar">
            <h2><?php esc_html_e( 'Preview Catalog', 'foodbank-manager' ); ?></h2>
            <span style="color:#6b7280;font-size:12px;"><?php esc_html_e( 'Live preview uses your current settings', 'foodbank-manager' ); ?></span>
          </div>
          <style id="fbm-preview-vars"><?php echo fbm_css_variables_preview( $opts ); ?></style>
          <div class="fbm-catalog">
            <?php include __DIR__ . '/_preview.php'; ?>
          </div>
        </div>
      </main>
    </div>
  </form>
</div>
