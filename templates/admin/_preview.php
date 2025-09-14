<?php // phpcs:ignoreFile
// Sample markup for Theme preview.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="fbm-scope">
<nav class="fbm-menu" style="margin-bottom:1rem;">
    <a class="fbm-menu__item is-active" href="#">Menu A</a>
    <a class="fbm-menu__item" href="#">Menu B</a>
    <a class="fbm-menu__item" href="#">Menu C</a>
</nav>

<h1>Heading 1</h1>
<h2>Heading 2</h2>
<h3>Heading 3</h3>
<h4>Heading 4</h4>
<h5>Heading 5</h5>
<h6>Heading 6</h6>
<p>Paragraph with <a href="#">link</a>. <small>Small text</small></p>

<p>
    <button class="fbm-button">Primary</button>
    <button class="fbm-button--glass">Ghost</button>
</p>

<form class="fbm-form" style="margin-top:1rem;">
    <p><label>Label <input class="fbm-input" placeholder="Placeholder" /></label></p>
    <p><select class="fbm-select"><option>Option</option></select></p>
    <p><label><input type="checkbox" /> Checkbox</label></p>
    <p><label><input type="radio" name="r" /> Radio</label></p>
</form>

<div class="fbm-grid" style="margin-top:1rem;">
    <div class="fbm-card"><div class="fbm-card__title">Tile 1</div></div>
    <div class="fbm-card"><div class="fbm-card__title">Tile 2</div></div>
    <div class="fbm-card"><div class="fbm-card__title">Tile 3</div></div>
</div>

<table class="fbm-table" style="margin-top:1rem;">
    <thead><tr><th>Head</th><th>Head</th></tr></thead>
    <tbody>
        <tr><td>Row 1</td><td>Row 1</td></tr>
        <tr><td>Row 2</td><td>Row 2</td></tr>
    </tbody>
</table>

<div class="fbm-notice fbm-notice--info" style="margin-top:1rem;">Info notice</div>
<div class="fbm-notice fbm-notice--success">Success notice</div>
<div class="fbm-notice fbm-notice--warn">Warning notice</div>
<div class="fbm-notice fbm-notice--error">Error notice</div>

<div class="fbm-tabs" style="margin-top:1rem;">
    <div class="fbm-tablist" role="tablist">
        <button role="tab" id="t1" aria-controls="p1" aria-selected="true">Tab 1</button>
        <button role="tab" id="t2" aria-controls="p2" aria-selected="false" tabindex="-1">Tab 2</button>
        <button role="tab" id="t3" aria-controls="p3" aria-selected="false" tabindex="-1">Tab 3</button>
    </div>
    <div id="p1" role="tabpanel" aria-labelledby="t1">Panel 1</div>
    <div id="p2" role="tabpanel" aria-labelledby="t2" hidden>Panel 2</div>
    <div id="p3" role="tabpanel" aria-labelledby="t3" hidden>Panel 3</div>
</div>

<script>
document.querySelectorAll('.fbm-tabs [role=tab]').forEach((tab) => {
    tab.addEventListener('click', () => {
        const tabs = tab.parentElement.querySelectorAll('[role=tab]');
        tabs.forEach((t) => {
            const selected = t === tab;
            t.setAttribute('aria-selected', selected ? 'true' : 'false');
            if (selected) {
                t.removeAttribute('tabindex');
            } else {
                t.setAttribute('tabindex', '-1');
            }
            const panel = document.getElementById(t.getAttribute('aria-controls'));
            if (panel) {
                panel.hidden = ! selected;
            }
        });
    });
});
</script>
</div>
