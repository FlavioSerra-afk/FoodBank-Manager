<?php // phpcs:ignoreFile
// Sample markup for Theme preview.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<nav class="fbm-menu">
    <div class="fbm-menu__item is-active"><span class="fbm-menu__icon">🏠</span>Menu A</div>
    <div class="fbm-menu__item"><span class="fbm-menu__icon">📄</span>Menu B</div>
    <div class="fbm-menu__divider"></div>
    <div class="fbm-menu__item"><span class="fbm-menu__icon">⚙️</span>Menu C</div>
</nav>
<div class="fbm-card" style="margin-top:1rem;padding:1rem;">
    <h3 class="fbm-card__title">Card title</h3>
    <p class="fbm-text">Card content goes here.</p>
</div>
<form class="fbm-form" style="margin-top:1rem;">
    <p><label>Label <input class="fbm-input" placeholder="Placeholder" /></label></p>
    <p><select class="fbm-select"><option>Option</option></select></p>
    <p><label><input type="checkbox" /> Checkbox</label></p>
    <p><label><input type="radio" name="r" /> Radio</label></p>
</form>
<table class="fbm-table" style="margin-top:1rem;">
    <thead><tr><th>Head</th><th>Head</th></tr></thead>
    <tbody><tr><td>Row</td><td>Row</td></tr></tbody>
</table>
<div class="fbm-notice fbm-notice--info" style="margin-top:1rem;">Info notice</div>
<div class="fbm-notice fbm-notice--success">Success notice</div>
<div class="fbm-notice fbm-notice--warn">Warning notice</div>
<div class="fbm-notice fbm-notice--error">Error notice</div>
