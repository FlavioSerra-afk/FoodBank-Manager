<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class LivePreviewVarsTest extends \PHPUnit\Framework\TestCase {
    public function test_style_updates_on_input(): void {
        $js = FBM_PATH . 'assets/js/theme-preview.js';
        $node = <<<'NODE'
const fs=require('fs'),vm=require('vm');
const code=fs.readFileSync(process.argv[1],'utf8');
const inputs=[{_val:'22',token:'--fbm-h3',unit:'px'},{_val:'44',token:'--fbm-tabs-h',unit:'px'}];
function jQuery(sel,attrs){
    if(typeof sel==='function'){sel();return;}
    if(typeof sel==='object'){return sel;}
    if(sel==='.fbm-theme-controls'){return {on:(ev,child,fn)=>{jQuery.handler=fn;}};}
    if(typeof sel==='string' && sel.includes('[data-token]')){return {each:(cb)=>{inputs.forEach(i=>cb.call(i));}};}
    if(sel==='style[data-fbm-preview]'){
        if(jQuery.styleEl){return {length:1,text:(v)=>{if(v===undefined)return jQuery.styleEl.textContent;jQuery.styleEl.textContent=v;}};}
        return {length:0};
    }
    if(typeof sel==='string' && sel.startsWith('<style')){
        const el={attrs:attrs||{},textContent:'',appendTo:()=>({text:(v)=>{el.textContent=v;}})};
        jQuery.styleEl=el;
        return el;
    }
    return {on:()=>{},find:()=>({each:()=>{}}),val:()=>'',data:()=>''};
}
inputs.forEach(i=>{i.val=function(){return this._val;};i.data=function(k){if(k==='token')return this.token;if(k==='unit')return this.unit;};});
const sandbox={window:{},document:{},jQuery,setTimeout:(fn)=>fn(),clearTimeout:()=>{}};
vm.runInNewContext(code,sandbox);
jQuery.handler.call(inputs[0]);
console.log(JSON.stringify({css:jQuery.styleEl.textContent,hasAttr:Object.prototype.hasOwnProperty.call(jQuery.styleEl.attrs,'data-fbm-preview')}));
NODE;
        $cmd = 'node -e ' . escapeshellarg($node) . ' ' . escapeshellarg($js);
        $out = shell_exec($cmd);
        $data = json_decode((string)$out, true);
        $this->assertTrue($data['hasAttr']);
        $this->assertStringContainsString('--fbm-h3:22px', $data['css']);
        $this->assertStringContainsString('--fbm-tabs-h:44px', $data['css']);
    }
}

