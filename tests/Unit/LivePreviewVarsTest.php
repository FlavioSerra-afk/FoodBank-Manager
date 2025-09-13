<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class LivePreviewVarsTest extends \PHPUnit\Framework\TestCase {
    public function test_style_injected(): void {
        $js = FBM_PATH . 'assets/js/theme-preview.js';
        $cmd = "node -e \"const fs=require('fs'),vm=require('vm');const code=fs.readFileSync('$js','utf8');let text='';const preview={appendChild:(el)=>{preview.el=el;}};function jQuery(sel){if(typeof sel==='function'){return;}if(sel==='[data-fbm-preview]'){return {find:()=>({length:0}),appendChild:(el)=>{preview.appendChild(el);}};}if(sel.startsWith('<')){const el={textContent:'',appendTo:(t)=>{t.appendChild(el);return {text:(v)=>{el.textContent=v;text=v;}}}};return el;}return {on:()=>{},find:()=>({each:()=>{}}),val:()=>'',data:()=>''};}const sandbox={window:{},document:{},jQuery,setTimeout:(fn)=>fn(),clearTimeout:()=>{}};vm.runInNewContext(code,sandbox);sandbox.window.FBMPreview.render({'--fbm-bg':'#fff'});console.log(text);\"";
        $out = shell_exec($cmd);
        $this->assertStringContainsString('.fbm-preview.fbm-scope', (string)$out);
        $this->assertStringContainsString('--fbm-bg:#fff', (string)$out);
    }
}
