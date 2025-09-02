<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="fbm-attendance-manager">
  <div class="fbm-tabs">
    <button type="button" data-tab="manual" class="active"><?php echo esc_html__( 'Manual', 'foodbank-manager' ); ?></button>
    <button type="button" data-tab="scan"><?php echo esc_html__( 'Scan', 'foodbank-manager' ); ?></button>
  </div>
  <div id="fbm-tab-manual" class="fbm-tab">
    <p><label><?php esc_html_e( 'Token', 'foodbank-manager' ); ?> <input type="text" id="fbm-token" /></label></p>
    <p><label><?php esc_html_e( 'Application ID', 'foodbank-manager' ); ?> <input type="number" id="fbm-app" /></label></p>
    <p><label><?php esc_html_e( 'Event ID', 'foodbank-manager' ); ?> <input type="number" id="fbm-event" /></label></p>
    <p><label><?php esc_html_e( 'Type', 'foodbank-manager' ); ?>
      <select id="fbm-type">
        <?php foreach ( $type_opts as $t ) : ?>
          <option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $t ) ) ); ?></option>
        <?php endforeach; ?>
      </select>
    </label></p>
    <p>
      <button type="button" id="fbm-checkin"><?php esc_html_e( 'Check-in', 'foodbank-manager' ); ?></button>
      <button type="button" id="fbm-noshow"><?php esc_html_e( 'Mark no-show', 'foodbank-manager' ); ?></button>
    </p>
    <div id="fbm-message"></div>
  </div>
  <div id="fbm-tab-scan" class="fbm-tab" style="display:none;">
    <p><button type="button" id="fbm-start-scan"><?php esc_html_e( 'Start camera scan', 'foodbank-manager' ); ?></button></p>
    <div id="fbm-scanner" style="width:240px;height:240px;display:none;"></div>
    <div id="fbm-scan-error" style="display:none;" class="fbm-error"><?php esc_html_e( 'Camera not available.', 'foodbank-manager' ); ?></div>
  </div>
</div>
<script>
(function(){
  const nonce = '<?php echo esc_js( $nonce_var ); ?>';
  const checkinUrl = '<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/checkin' ) ); ?>';
  const noshowUrl = '<?php echo esc_url_raw( rest_url( 'pcc-fb/v1/attendance/noshow' ) ); ?>';
  const tabs = document.querySelectorAll('.fbm-tabs button');
  tabs.forEach(btn=>btn.addEventListener('click',()=>{
    tabs.forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.fbm-tab').forEach(el=>el.style.display='none');
    document.getElementById('fbm-tab-'+btn.dataset.tab).style.display='block';
  }));
  async function postJson(url,data){
    return fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},body:JSON.stringify(data)});
  }
  async function handleCheckin(data){
    let res = await postJson(checkinUrl,data);
    if(res.status===409){
      const j = await res.json();
      const msg = document.getElementById('fbm-message');
      msg.innerHTML = '';
      const w = document.createElement('div');
      w.textContent = 'Policy: last attended '+j.policy_warning.last_attended_at+' (<'+j.policy_warning.rule_days+'d).';
      const ov = document.createElement('div');
      ov.innerHTML = '<label><input type="checkbox" id="fbm-ov"> <?php echo esc_js( __( 'Override', 'foodbank-manager' ) ); ?></label> <input type="text" id="fbm-ov-note" placeholder="<?php echo esc_js( __( 'Note', 'foodbank-manager' ) ); ?>" /> <button id="fbm-ov-submit"><?php echo esc_js( __( 'Confirm', 'foodbank-manager' ) ); ?></button>';
      msg.appendChild(w); msg.appendChild(ov);
      document.getElementById('fbm-ov-submit').addEventListener('click',()=>{
        if(!document.getElementById('fbm-ov').checked || document.getElementById('fbm-ov-note').value===''){return;}
        data.override={allowed:true,note:document.getElementById('fbm-ov-note').value};
        handleCheckin(data);
      });
      return;
    }
    const j = await res.json();
    document.getElementById('fbm-message').textContent = j.status ? '<?php echo esc_js( __( 'Success', 'foodbank-manager' ) ); ?>' : '<?php echo esc_js( __( 'Error', 'foodbank-manager' ) ); ?>';
  }
  document.getElementById('fbm-checkin').addEventListener('click',()=>{
    const data={token:document.getElementById('fbm-token').value,application_id:parseInt(document.getElementById('fbm-app').value,10)||undefined,event_id:parseInt(document.getElementById('fbm-event').value,10)||undefined,type:document.getElementById('fbm-type').value,method:'manual'};
    handleCheckin(data);
  });
  document.getElementById('fbm-noshow').addEventListener('click',async()=>{
    const data={application_id:parseInt(document.getElementById('fbm-app').value,10)||0,event_id:parseInt(document.getElementById('fbm-event').value,10)||undefined,type:document.getElementById('fbm-type').value};
    const res = await postJson(noshowUrl,data);
    const j = await res.json();
    document.getElementById('fbm-message').textContent = j.status==='no_show' ? '<?php echo esc_js( __( 'No-show recorded', 'foodbank-manager' ) ); ?>' : '<?php echo esc_js( __( 'Error', 'foodbank-manager' ) ); ?>';
  });
  document.getElementById('fbm-start-scan').addEventListener('click',async()=>{
    try{
      const mod = await import('https://cdn.jsdelivr.net/npm/@zxing/browser@latest');
      const codeReader = new mod.BrowserQRCodeReader();
      const preview = document.getElementById('fbm-scanner');
      preview.style.display='block';
      const result = await codeReader.decodeOnceFromVideoDevice(undefined, preview);
      document.getElementById('fbm-token').value = result.text;
      codeReader.reset();
    }catch(e){
      document.getElementById('fbm-scanner').style.display='none';
      document.getElementById('fbm-scan-error').style.display='block';
    }
  });
})();
</script>
