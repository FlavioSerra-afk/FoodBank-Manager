<?php // phpcs:ignoreFile
if (!defined('ABSPATH')) { exit; }
$jobs = $jobs ?? array();
?>
<?php echo '<div id="fbm-ui" class="fbm-scope fbm-app">'; ?>
<div class="wrap fbm-admin"><div class="fbm-section">
<h2><?php esc_html_e('Export Jobs', 'foodbank-manager'); ?></h2>
<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:1em;">
    <input type="hidden" name="action" value="fbm_export_job_run" />
    <?php wp_nonce_field('fbm_export_job_run'); ?>
    <button type="submit" class="button fbm-button--glass"><?php esc_html_e('Run now', 'foodbank-manager'); ?></button>
</form>
<table class="widefat">
    <thead>
        <tr>
            <th><?php esc_html_e('ID', 'foodbank-manager'); ?></th>
            <th><?php esc_html_e('Type', 'foodbank-manager'); ?></th>
            <th><?php esc_html_e('Format', 'foodbank-manager'); ?></th>
            <th><?php esc_html_e('Status', 'foodbank-manager'); ?></th>
            <th><?php esc_html_e('Actions', 'foodbank-manager'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($jobs as $j) : ?>
        <tr>
            <td><?php echo (int) $j['id']; ?></td>
            <td><?php echo esc_html($j['type']); ?></td>
            <td><?php echo esc_html($j['format']); ?></td>
            <td><?php echo esc_html($j['status']); ?></td>
            <td>
                <?php if ('done' === $j['status']) : ?>
                    <a class="button fbm-button--glass" href="<?php echo esc_url( wp_nonce_url( admin_url('admin-post.php?action=fbm_export_download&id=' . (int)$j['id']), 'fbm_export_download_' . (int)$j['id'] ) ); ?>"><?php esc_html_e('Download', 'foodbank-manager'); ?></a>
                <?php elseif ('failed' === $j['status']) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                        <input type="hidden" name="action" value="fbm_export_job_retry" />
                        <input type="hidden" name="id" value="<?php echo (int) $j['id']; ?>" />
                        <?php wp_nonce_field('fbm_export_job_retry_' . (int)$j['id']); ?>
                        <button type="submit" class="button fbm-button--glass">Retry</button>
                    </form>
                <?php else : ?>
                    &mdash;
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
 </table>
</div></div>
<?php echo '</div>'; ?>
