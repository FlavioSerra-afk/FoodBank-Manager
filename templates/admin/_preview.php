<?php // phpcs:ignoreFile
// Sample markup for Theme preview.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="fbm-scope">
<h1>Heading 1</h1>
<h2>Heading 2</h2>
<h3>Heading 3</h3>
<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
<p>Paragraph with <a href="#">link</a> and <span class="fbm-text--muted">muted text</span>. <small>Small text</small></p>
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
<div class="fbm-tabs" style="margin-top:1rem;">
    <div class="fbm-tablist" role="tablist">
        <button role="tab" id="t1" aria-controls="p1" aria-selected="true">Tab 1</button>
        <button role="tab" id="t2" aria-controls="p2" tabindex="-1">Tab 2</button>
        <button role="tab" id="t3" aria-controls="p3" tabindex="-1">Tab 3</button>
    </div>
    <div id="p1" role="tabpanel" aria-labelledby="t1">Panel 1</div>
    <div id="p2" role="tabpanel" aria-labelledby="t2" hidden>Panel 2</div>
    <div id="p3" role="tabpanel" aria-labelledby="t3" hidden>Panel 3</div>
</div>
</div>
