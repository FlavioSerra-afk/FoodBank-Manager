<?php
use FoodBankManager\Security\Helpers;

if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap">
<h1><?php esc_html_e('Attendance','foodbank-manager'); ?></h1>
<form method="get" class="fbm-filters">
  <input type="hidden" name="page" value="fbm-attendance" />
  <div class="fbm-filter-row">
    <label><?php esc_html_e('Preset','foodbank-manager'); ?>
      <select name="preset">
        <option value="7d" <?php selected($preset,'7d'); ?>><?php esc_html_e('Last 7 days','foodbank-manager'); ?></option>
        <option value="30d" <?php selected($preset,'30d'); ?>><?php esc_html_e('Last 30 days','foodbank-manager'); ?></option>
        <option value="6m" <?php selected($preset,'6m'); ?>><?php esc_html_e('Last 6 months','foodbank-manager'); ?></option>
        <option value="12m" <?php selected($preset,'12m'); ?>><?php esc_html_e('Last 12 months','foodbank-manager'); ?></option>
        <option value="custom" <?php selected($preset,'custom'); ?>><?php esc_html_e('Custom','foodbank-manager'); ?></option>
      </select>
    </label>
    <label><?php esc_html_e('From','foodbank-manager'); ?> <input type="date" name="range_from" value="<?php echo esc_attr(substr($range_from,0,10)); ?>" /></label>
    <label><?php esc_html_e('To','foodbank-manager'); ?> <input type="date" name="range_to" value="<?php echo esc_attr(substr($range_to,0,10)); ?>" /></label>
    <label><?php esc_html_e('Event','foodbank-manager'); ?> <input type="number" name="event_id" value="<?php echo isset($filters['event_id']) ? esc_attr((string)$filters['event_id']) : ''; ?>" /></label>
    <label><?php esc_html_e('Status','foodbank-manager'); ?>
      <select name="status[]" multiple>
        <option value="present" <?php if (isset($filters['status']) && in_array('present',$filters['status'],true)) echo 'selected'; ?>><?php esc_html_e('Present','foodbank-manager'); ?></option>
        <option value="no_show" <?php if (isset($filters['status']) && in_array('no_show',$filters['status'],true)) echo 'selected'; ?>><?php esc_html_e('No-show','foodbank-manager'); ?></option>
      </select>
    </label>
    <label><?php esc_html_e('Type','foodbank-manager'); ?>
      <select name="type[]" multiple>
        <option value="in_person" <?php if (isset($filters['type']) && in_array('in_person',$filters['type'],true)) echo 'selected'; ?>><?php esc_html_e('In person','foodbank-manager'); ?></option>
        <option value="delivery" <?php if (isset($filters['type']) && in_array('delivery',$filters['type'],true)) echo 'selected'; ?>><?php esc_html_e('Delivery','foodbank-manager'); ?></option>
      </select>
    </label>
    <label><?php esc_html_e('Manager ID','foodbank-manager'); ?> <input type="number" name="manager_id" value="<?php echo isset($filters['manager_id']) ? esc_attr((string)$filters['manager_id']) : ''; ?>" /></label>
    <label><input type="checkbox" name="policy_only" value="1" <?php checked(!empty($filters['policy_only'])); ?> /> <?php esc_html_e('Policy only','foodbank-manager'); ?></label>
    <button class="button"><?php esc_html_e('Filter','foodbank-manager'); ?></button>
  </div>
</form>
<?php $base_url = remove_query_arg('paged'); ?>
<table class="wp-list-table widefat fixed striped">
  <thead><tr>
    <?php
      $cols = array(
        'name'=>__('Name','foodbank-manager'),
        'email'=>__('Email','foodbank-manager'),
        'postcode'=>__('Postcode','foodbank-manager'),
        'last_attended'=>__('Last Attended','foodbank-manager'),
        'visits_range'=>__('Visits (Range)','foodbank-manager'),
        'noshows_range'=>__('No-shows (Range)','foodbank-manager'),
        'visits_12m'=>__('Visits (12m)','foodbank-manager'),
        'policy_badge'=>__('Policy','foodbank-manager')
      );
      foreach ($cols as $key=>$label) {
        $order = ($filters['orderby']===$key && $filters['order']==='ASC') ? 'DESC':'ASC';
        $url = esc_url(add_query_arg(array('orderby'=>$key,'order'=>$order),$base_url));
        echo '<th><a href="'.$url.'">'.esc_html($label).'</a></th>';
      }
    ?>
  </tr></thead>
  <tbody>
  <?php if (empty($rows)) : ?>
    <tr><td colspan="8"><?php esc_html_e('No attendance records.','foodbank-manager'); ?></td></tr>
  <?php else : foreach ($rows as $r) : ?>
    <tr>
      <td><?php echo esc_html($r['name']); ?></td>
      <td><?php echo esc_html($r['email']); ?></td>
      <td><?php echo esc_html($r['postcode']); ?></td>
      <td><?php echo esc_html($r['last_attended']); ?></td>
      <td><?php echo esc_html((string)$r['visits_range']); ?></td>
      <td><?php echo esc_html((string)$r['noshows_range']); ?></td>
      <td><?php echo esc_html((string)$r['visits_12m']); ?></td>
      <td><?php echo $r['policy_badge']==='warning' ? '<span class="badge badge-warning">'.esc_html__('Warning','foodbank-manager').'</span>' : ''; ?></td>
    </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table>
<?php
  $total_pages = max(1, ceil($total / $per_page));
?>
<div class="tablenav">
  <div class="tablenav-pages">
    <?php if ($page > 1): ?>
      <a class="prev-page" href="<?php echo esc_url(add_query_arg('paged',$page-1,$base_url)); ?>">&laquo;</a>
    <?php endif; ?>
    <span class="paging-input"><?php echo esc_html($page) . ' / ' . esc_html($total_pages); ?></span>
    <?php if ($page < $total_pages): ?>
      <a class="next-page" href="<?php echo esc_url(add_query_arg('paged',$page+1,$base_url)); ?>">&raquo;</a>
    <?php endif; ?>
  </div>
  <div class="alignleft actions">
    <form method="get" id="fbm-perpage-form">
      <input type="hidden" name="page" value="fbm-attendance" />
      <input type="hidden" name="preset" value="<?php echo esc_attr($preset); ?>" />
      <input type="hidden" name="range_from" value="<?php echo esc_attr($range_from); ?>" />
      <input type="hidden" name="range_to" value="<?php echo esc_attr($range_to); ?>" />
      <select name="per_page" onchange="document.getElementById('fbm-perpage-form').submit();">
        <option value="25" <?php selected($per_page,25); ?>>25</option>
        <option value="50" <?php selected($per_page,50); ?>>50</option>
        <option value="100" <?php selected($per_page,100); ?>>100</option>
      </select>
    </form>
  </div>
</div>
<?php if (current_user_can('attendance_export')): ?>
<form method="post" class="fbm-export">
  <input type="hidden" name="action" value="fbm_att_export" />
  <?php if ($can_sensitive): ?>
    <label><input type="checkbox" name="unmask" value="1" /> <?php esc_html_e('Unmask','foodbank-manager'); ?></label>
  <?php endif; ?>
  <?php wp_nonce_field('fbm_att_export'); ?>
  <button type="submit" class="button"><?php esc_html_e('Export CSV','foodbank-manager'); ?></button>
</form>
<?php endif; ?>
</div>
