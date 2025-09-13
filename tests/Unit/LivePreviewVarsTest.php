<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class LivePreviewVarsTest extends \PHPUnit\Framework\TestCase {
    public function test_style_updates_on_input(): void {
        $js = FBM_PATH . 'assets/js/theme-preview.js';
        $cmd = "node -e \"const fs=require('fs'),vm=require('vm');const code=fs.readFileSync('$js','utf8');let text='';const inputs=[{_val:'22',token:'--fbm-h3',unit:'px'},{_val:'44',token:'--fbm-tabs-h',unit:'px'}];const preview={appendChild:(el)=>{preview.el=el;}};function jQuery(sel){if(typeof sel==='function'){sel();return;}if(typeof sel==='object'){return sel;}if(sel==='.fbm-theme-controls'){return {on:(ev,sel,fn)=>{jQuery.handler=fn;}};}if(sel && sel.includes('[data-token]')){return {each:(cb)=>{inputs.forEach(i=>cb.call(i));}};}if(sel==='[data-fbm-preview]'){return {find:()=>({length:0}),appendChild:(el)=>{preview.appendChild(el);}};}if(sel && sel.startsWith('<')){const el={textContent:'',appendTo:(t)=>{t.appendChild(el);return {text:(v)=>{el.textContent=v;text=v;}}}};return el;}return {on:()=>{},find:()=>({each:()=>{}}),val:()=>'',data:()=>''};}inputs.forEach(i=>{i.val=function(){return this._val;};i.data=function(k){if(k==='token')return this.token; if(k==='unit')return this.unit;};});const sandbox={window:{},document:{},jQuery,setTimeout:(fn)=>fn(),clearTimeout:()=>{}};vm.runInNewContext(code,sandbox);jQuery.handler.call(inputs[0]);console.log(text);\"";
        $out = shell_exec($cmd);
        $this->assertStringContainsString('--fbm-h3:22px', (string)$out);
        $this->assertStringContainsString('--fbm-tabs-h:44px', (string)$out);
    }
}
