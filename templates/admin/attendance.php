<?php
// phpcs:ignoreFile

use FoodBankManager\Security\Helpers;
use FoodBankManager\Admin\AttendancePage;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<div class="fbm-admin"><div class="wrap">
<h1><?php esc_html_e( 'Attendance', 'foodbank-manager' ); ?></h1>
<?php if ( isset( $_GET['fbm_override'] ) ) : ?>
<div class="notice notice-success"><p><?php esc_html_e( 'Override check-in recorded.', 'foodbank-manager' ); ?></p></div>
<?php endif; ?>
<form method="get" class="fbm-filters">
        <input type="hidden" name="page" value="fbm_attendance" />
	<div class="fbm-filter-row">
	<label><?php esc_html_e( 'Preset', 'foodbank-manager' ); ?>
		<select name="preset">
		<option value="7d" <?php selected( $preset, '7d' ); ?>><?php esc_html_e( 'Last 7 days', 'foodbank-manager' ); ?></option>
		<option value="30d" <?php selected( $preset, '30d' ); ?>><?php esc_html_e( 'Last 30 days', 'foodbank-manager' ); ?></option>
		<option value="6m" <?php selected( $preset, '6m' ); ?>><?php esc_html_e( 'Last 6 months', 'foodbank-manager' ); ?></option>
		<option value="12m" <?php selected( $preset, '12m' ); ?>><?php esc_html_e( 'Last 12 months', 'foodbank-manager' ); ?></option>
		<option value="custom" <?php selected( $preset, 'custom' ); ?>><?php esc_html_e( 'Custom', 'foodbank-manager' ); ?></option>
		</select>
	</label>
	<label><?php esc_html_e( 'From', 'foodbank-manager' ); ?> <input type="date" name="range_from" value="<?php echo esc_attr( substr( $range_from, 0, 10 ) ); ?>" /></label>
	<label><?php esc_html_e( 'To', 'foodbank-manager' ); ?> <input type="date" name="range_to" value="<?php echo esc_attr( substr( $range_to, 0, 10 ) ); ?>" /></label>
	<label><?php esc_html_e( 'Event', 'foodbank-manager' ); ?> <input type="number" name="event_id" value="<?php echo isset( $filters['event_id'] ) ? esc_attr( (string) $filters['event_id'] ) : ''; ?>" /></label>
	<label><?php esc_html_e( 'Status', 'foodbank-manager' ); ?>
		<select name="status[]" multiple>
		<option value="present" 
		<?php
		if ( isset( $filters['status'] ) && in_array( 'present', $filters['status'], true ) ) {
			echo 'selected';}
		?>
		><?php esc_html_e( 'Present', 'foodbank-manager' ); ?></option>
		<option value="no_show" 
		<?php
		if ( isset( $filters['status'] ) && in_array( 'no_show', $filters['status'], true ) ) {
			echo 'selected';}
		?>
		><?php esc_html_e( 'No-show', 'foodbank-manager' ); ?></option>
		</select>
	</label>
	<label><?php esc_html_e( 'Type', 'foodbank-manager' ); ?>
		<select name="type[]" multiple>
		<option value="in_person" 
		<?php
		if ( isset( $filters['type'] ) && in_array( 'in_person', $filters['type'], true ) ) {
			echo 'selected';}
		?>
		><?php esc_html_e( 'In person', 'foodbank-manager' ); ?></option>
		<option value="delivery" 
		<?php
		if ( isset( $filters['type'] ) && in_array( 'delivery', $filters['type'], true ) ) {
			echo 'selected';}
		?>
		><?php esc_html_e( 'Delivery', 'foodbank-manager' ); ?></option>
		</select>
	</label>
	<label><?php esc_html_e( 'Manager ID', 'foodbank-manager' ); ?> <input type="number" name="manager_id" value="<?php echo isset( $filters['manager_id'] ) ? esc_attr( (string) $filters['manager_id'] ) : ''; ?>" /></label>
	<label><input type="checkbox" name="policy_only" value="1" <?php checked( ! empty( $filters['policy_only'] ) ); ?> /> <?php esc_html_e( 'Policy only', 'foodbank-manager' ); ?></label>
	<label><input type="checkbox" name="include_voided" value="1" <?php checked( $include_voided ); ?> /> <?php esc_html_e( 'Include voided', 'foodbank-manager' ); ?></label>
	<button class="button"><?php esc_html_e( 'Filter', 'foodbank-manager' ); ?></button>
	</div>
</form>
<?php $base_url = remove_query_arg( 'paged' ); ?>
<table class="wp-list-table widefat fixed striped">
	<thead><tr>
	<?php
		$cols = array(
			'name'          => __( 'Name', 'foodbank-manager' ),
			'email'         => __( 'Email', 'foodbank-manager' ),
			'postcode'      => __( 'Postcode', 'foodbank-manager' ),
			'last_attended' => __( 'Last Attended', 'foodbank-manager' ),
			'visits_range'  => __( 'Visits (Range)', 'foodbank-manager' ),
			'noshows_range' => __( 'No-shows (Range)', 'foodbank-manager' ),
			'visits_12m'    => __( 'Visits (12m)', 'foodbank-manager' ),
                        'policy_badge'  => __( 'Policy', 'foodbank-manager' ),
                        'actions'       => __( 'Actions', 'foodbank-manager' ),
                );
		foreach ( $cols as $key => $label ) {
			$order = ( $filters['orderby'] === $key && $filters['order'] === 'ASC' ) ? 'DESC' : 'ASC';
			$url   = esc_url(
				add_query_arg(
					array(
						'orderby' => $key,
						'order'   => $order,
					),
					$base_url
				)
			);
			echo '<th><a href="' . $url . '">' . esc_html( $label ) . '</a></th>';
		}
		?>
	</tr></thead>
	<tbody>
	<?php if ( empty( $rows ) ) : ?>
        <tr><td colspan="9"><?php esc_html_e( 'No attendance records.', 'foodbank-manager' ); ?></td></tr>
		<?php
	else :
		foreach ( $rows as $r ) :
			?>
	<tr>
		<td><?php echo esc_html( $r['name'] ); ?></td>
		<td><?php echo esc_html( $r['email'] ); ?></td>
		<td><?php echo esc_html( $r['postcode'] ); ?></td>
		<td><?php echo esc_html( $r['last_attended'] ); ?></td>
		<td><?php echo esc_html( (string) $r['visits_range'] ); ?></td>
		<td><?php echo esc_html( (string) $r['noshows_range'] ); ?></td>
		<td><?php echo esc_html( (string) $r['visits_12m'] ); ?></td>
		<td><?php echo $r['policy_badge'] === 'warning' ? '<span class="badge badge-warning">' . esc_html__( 'Warning', 'foodbank-manager' ) . '</span>' : ''; ?></td>
                <td>
                        <button type="button" class="button fbm-timeline-btn" data-app="<?php echo esc_attr( (string) $r['application_id'] ); ?>"><?php esc_html_e( 'Timeline', 'foodbank-manager' ); ?></button>
                        <?php if ( current_user_can( 'fb_manage_attendance' ) ) : ?>
                        <button type="button" class="button fbm-showqr-btn" data-url="<?php echo esc_url( AttendancePage::build_checkin_url( (int) $r['application_id'] ) ); ?>"><?php esc_html_e( 'Show QR', 'foodbank-manager' ); ?></button>
                        <button type="button" class="button fbm-override-btn" data-app="<?php echo esc_attr( (string) $r['application_id'] ); ?>"><?php esc_html_e( 'Override & Check-in', 'foodbank-manager' ); ?></button>
                        <?php endif; ?>
                </td>
	</tr>
			<?php
	endforeach;
endif;
	?>
	</tbody>
</table>
<?php
	$total_pages = max( 1, ceil( $total / $per_page ) );
?>
<div class="tablenav">
	<div class="tablenav-pages">
	<?php if ( $page > 1 ) : ?>
		<a class="prev-page" href="<?php echo esc_url( add_query_arg( 'paged', $page - 1, $base_url ) ); ?>">&laquo;</a>
	<?php endif; ?>
	<span class="paging-input"><?php echo esc_html( $page ) . ' / ' . esc_html( $total_pages ); ?></span>
	<?php if ( $page < $total_pages ) : ?>
		<a class="next-page" href="<?php echo esc_url( add_query_arg( 'paged', $page + 1, $base_url ) ); ?>">&raquo;</a>
	<?php endif; ?>
	</div>
	<div class="alignleft actions">
	<form method="get" id="fbm-perpage-form">
                <input type="hidden" name="page" value="fbm_attendance" />
		<input type="hidden" name="preset" value="<?php echo esc_attr( $preset ); ?>" />
		<input type="hidden" name="range_from" value="<?php echo esc_attr( $range_from ); ?>" />
		<input type="hidden" name="range_to" value="<?php echo esc_attr( $range_to ); ?>" />
		<?php
		if ( $include_voided ) :
			?>
			<input type="hidden" name="include_voided" value="1" /><?php endif; ?>
		<select name="per_page" onchange="document.getElementById('fbm-perpage-form').submit();">
		<option value="25" <?php selected( $per_page, 25 ); ?>>25</option>
		<option value="50" <?php selected( $per_page, 50 ); ?>>50</option>
		<option value="100" <?php selected( $per_page, 100 ); ?>>100</option>
		</select>
	</form>
	</div>
</div>
<?php if ( current_user_can( 'fb_manage_attendance' ) ) : ?>
<form method="post" class="fbm-export">
	<input type="hidden" name="action" value="fbm_att_export" />
	<?php
	if ( $include_voided ) :
		?>
		<input type="hidden" name="include_voided" value="1" /><?php endif; ?>
	<?php if ( $can_sensitive ) : ?>
	<label><input type="checkbox" name="unmask" value="1" /> <?php esc_html_e( 'Unmask', 'foodbank-manager' ); ?></label>
	<?php endif; ?>
        <?php wp_nonce_field( 'fbm_att_export', '_wpnonce' ); ?>
	<button type="submit" class="button"><?php esc_html_e( 'Export CSV', 'foodbank-manager' ); ?></button>
</form>
<?php endif; ?>
<div id="fbm-timeline-modal" style="display:none;position:fixed;top:10%;left:10%;right:10%;background:#fff;border:1px solid #ccc;padding:10px;max-height:70%;overflow:auto;z-index:1000;">
        <button type="button" id="fbm-timeline-close" style="float:right;">&times;</button>
        <ul id="fbm-timeline-list"></ul>
</div>
<div id="fbm-qr-modal" style="display:none;position:fixed;top:10%;left:10%;right:10%;background:#fff;border:1px solid #ccc;padding:10px;z-index:1000;">
        <button type="button" id="fbm-qr-close" style="float:right;">&times;</button>
        <div id="fbm-qr-canvas"></div>
        <p><code id="fbm-qr-url"></code> <button type="button" id="fbm-qr-copy" class="button"><?php esc_html_e( 'Copy', 'foodbank-manager' ); ?></button></p>
        <p><?php esc_html_e( 'Admin-only — requires login', 'foodbank-manager' ); ?></p>
        <p><?php esc_html_e( 'Expires with session/nonce', 'foodbank-manager' ); ?></p>
</div>
<div id="fbm-override-modal" style="display:none;position:fixed;top:20%;left:30%;right:30%;background:#fff;border:1px solid #ccc;padding:10px;z-index:1000;">
        <button type="button" id="fbm-override-close" style="float:right;">&times;</button>
        <form id="fbm-override-form">
                <label><?php esc_html_e( 'Reason', 'foodbank-manager' ); ?><br />
                <textarea id="fbm-override-reason" required minlength="5" maxlength="500" style="width:100%;"></textarea></label>
                <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Override & Check-in', 'foodbank-manager' ); ?></button></p>
        </form>
</div>
<script>
(function(){
        const nonce='<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>';
        const endpoint='<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/timeline' ) ); ?>';
        const includeVoided=<?php echo $include_voided ? 'true' : 'false'; ?>;
    const canAdmin=<?php echo current_user_can( 'fb_manage_attendance' ) ? 'true' : 'false'; ?>;
        const voidUrl='<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/void' ) ); ?>';
        const unvoidUrl='<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/unvoid' ) ); ?>';
        const noteUrl='<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/note' ) ); ?>';
        const checkinUrl='<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/checkin' ) ); ?>';
	document.querySelectorAll('.fbm-timeline-btn').forEach(btn=>{
	btn.addEventListener('click',async()=>{
		const app=btn.dataset.app;
		const res=await fetch(endpoint+'?application_id='+app+(includeVoided?'&include_voided=1':''),{headers:{'X-WP-Nonce':nonce}});
		if(!res.ok)return;
		const data=await res.json();
		const list=document.getElementById('fbm-timeline-list');
		list.innerHTML='';
		data.records.forEach(r=>{
		const li=document.createElement('li');
		li.textContent=r.attendance_at+' \u2022 '+r.status+' \u2022 '+r.type+' \u2022 '+r.recorded_by_user_id;
		if(r.is_void){li.textContent+=' \u2022 void';}
		if(canAdmin){
			const btnV=document.createElement('button');
			btnV.textContent=r.is_void?'Unvoid':'Void';
			btnV.addEventListener('click',async()=>{
			const reason=r.is_void?'':prompt('Reason');
			const url=r.is_void?unvoidUrl:voidUrl;
			const body=r.is_void?{attendance_id:r.id}:{attendance_id:r.id,reason:reason};
			await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},body:JSON.stringify(body)});
			btn.click();
			});
			li.appendChild(btnV);
			const btnN=document.createElement('button');
			btnN.textContent='Note';
			btnN.addEventListener('click',async()=>{
			const note=prompt('Note');
			if(!note)return;
			await fetch(noteUrl,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},body:JSON.stringify({attendance_id:r.id,note:note})});
			btn.click();
			});
			li.appendChild(btnN);
		}
		if(r.notes){
			const ul=document.createElement('ul');
			r.notes.forEach(n=>{
			const ni=document.createElement('li');
			ni.textContent=n.note_text+' ('+n.created_at+')';
			ul.appendChild(ni);
			});
			li.appendChild(ul);
		}
		list.appendChild(li);
		});
		document.getElementById('fbm-timeline-modal').style.display='block';
	});
	});
        document.getElementById('fbm-timeline-close').addEventListener('click',()=>{
        document.getElementById('fbm-timeline-modal').style.display='none';
        });
        document.querySelectorAll('.fbm-showqr-btn').forEach(btn=>{
        btn.addEventListener('click',()=>{
                const url=btn.dataset.url;
                const modal=document.getElementById('fbm-qr-modal');
                const canvas=document.getElementById('fbm-qr-canvas');
                canvas.innerHTML='';
                // eslint-disable-next-line no-undef
                new QRCode(canvas,url);
                document.getElementById('fbm-qr-url').textContent=url;
                modal.style.display='block';
        });
        });
        document.getElementById('fbm-qr-close').addEventListener('click',()=>{
        document.getElementById('fbm-qr-modal').style.display='none';
        });
        document.getElementById('fbm-qr-copy').addEventListener('click',()=>{
        navigator.clipboard.writeText(document.getElementById('fbm-qr-url').textContent);
        });
        let overrideApp=0;
        document.querySelectorAll('.fbm-override-btn').forEach(btn=>{
        btn.addEventListener('click',()=>{
                overrideApp=btn.dataset.app;
                document.getElementById('fbm-override-modal').style.display='block';
        });
        });
        document.getElementById('fbm-override-close').addEventListener('click',()=>{
        document.getElementById('fbm-override-modal').style.display='none';
        });
        document.getElementById('fbm-override-form').addEventListener('submit',async e=>{
        e.preventDefault();
        const reason=document.getElementById('fbm-override-reason').value;
        if(reason.length<5){return;}
        const res=await fetch(checkinUrl,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},body:JSON.stringify({application_id:overrideApp,override:{allowed:true,note:reason}})});
        if(res.ok){window.location=window.location.href+(window.location.href.includes('?')?'&':'?')+'fbm_override=1';}
        });
})();
</script>
</div></div>
