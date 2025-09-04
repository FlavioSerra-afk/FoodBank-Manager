<?php
/**
 * Shortcodes admin template.
 *
 * @package FoodBankManager
 */

declare(strict_types=1);
?>
<div class="wrap">
<h1><?php esc_html_e( 'Shortcodes', 'foodbank-manager' ); ?></h1>
<form method="post" id="fbm-shortcodes-form">
	<?php wp_nonce_field( 'fbm_shortcodes_preview', '_wpnonce' ); ?>
	<input type="hidden" name="fbm_action" value="shortcode_preview" />
	<p>
		<label for="fbm-tag"><?php esc_html_e( 'Shortcode', 'foodbank-manager' ); ?></label>
		<select name="tag" id="fbm-tag">
			<option value=""><?php esc_html_e( 'Select', 'foodbank-manager' ); ?></option>
			<?php foreach ( $shortcodes as $sc ) : ?>
				<option value="<?php echo esc_attr( $sc['tag'] ); ?>" <?php selected( $current_tag, $sc['tag'] ); ?>>
						<?php echo esc_html( $sc['tag'] ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</p>
	<div id="fbm-attrs"></div>
	<p>
		<button type="button" id="fbm-generate" class="button"><?php esc_html_e( 'Generate', 'foodbank-manager' ); ?></button>
		<button type="submit" id="fbm-preview" class="button button-primary"><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></button>
	</p>
</form>
<div id="fbm-output" style="display:none;">
	<input type="text" id="fbm-shortcode-string" readonly />
	<button type="button" class="fbm-copy button"><?php esc_html_e( 'Copy', 'foodbank-manager' ); ?></button>
</div>
<?php if ( '' !== $preview_html ) : ?>
		<h2><?php esc_html_e( 'Preview', 'foodbank-manager' ); ?></h2>
		<div class="fbm-preview"><?php echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- safe HTML ?></div>
<?php endif; ?>
</div>
<script>
const FBM_SHORTCODES = <?php echo wp_json_encode( $shortcodes ); ?>;
const FBM_CURRENT = {
	tag: <?php echo $current_tag ? '"' . esc_js( $current_tag ) . '"' : 'null'; ?>,
	atts: <?php echo wp_json_encode( $current_atts ); ?>
};
const form = document.getElementById('fbm-shortcodes-form');
const select = document.getElementById('fbm-tag');
const attrsWrap = document.getElementById('fbm-attrs');
const outputWrap = document.getElementById('fbm-output');
const outField = document.getElementById('fbm-shortcode-string');
function buildAttrs(tag){
	attrsWrap.innerHTML='';
	const sc = FBM_SHORTCODES.find(s=>s.tag===tag);
	if(!sc){return;}
	Object.entries(sc.atts).forEach(([name,info])=>{
		const p=document.createElement('p');
		const label=document.createElement('label');
		label.textContent=name+' ';
		let field;
		if(info.type==='bool'){
			field=document.createElement('select');
			['true','false'].forEach(v=>{const o=document.createElement('option');o.value=v;o.text=v;field.appendChild(o);});
		}else if(info.type==='enum'){
			field=document.createElement('select');
			info.options.forEach(v=>{const o=document.createElement('option');o.value=v;o.text=v;field.appendChild(o);});
		}else{
			field=document.createElement('input');
			field.type='text';
		}
		field.name='atts['+name+']';
		const cur=FBM_CURRENT.tag===tag? (FBM_CURRENT.atts[name]||'') : info.default;
		field.value=cur;
		label.appendChild(field);
		p.appendChild(label);
		attrsWrap.appendChild(p);
	});
}
select.addEventListener('change',function(){buildAttrs(this.value);});
document.getElementById('fbm-generate').addEventListener('click',function(){
	const tag=select.value;
	if(!tag){return;}
	const parts=[];
	attrsWrap.querySelectorAll('[name^="atts["]').forEach(el=>{
		const m=el.name.match(/atts\[(.*)\]/);
		if(!m){return;}
		const name=m[1];
		const val=el.value.trim();
		if(val!==''){
			parts.push(name+'="'+val.replace(/"/g,'&quot;')+'"');
		}
	});
	parts.push('mask_sensitive="true"');
	const sc='['+tag+(parts.length?' '+parts.join(' '):'')+']';
	outField.value=sc;
	outputWrap.style.display='block';
});
form.addEventListener('submit',function(){
	const hidden=document.createElement('input');
	hidden.type='hidden';
	hidden.name='atts[mask_sensitive]';
	hidden.value='true';
	form.appendChild(hidden);
});
document.addEventListener('click',function(e){
	if(e.target.classList.contains('fbm-copy')){
		navigator.clipboard.writeText(outField.value);
	}
});
if(FBM_CURRENT.tag){
	select.value=FBM_CURRENT.tag;
	buildAttrs(FBM_CURRENT.tag);
}
</script>
