<?php
if (!defined('FBM_PATH')) {
    define('FBM_PATH', dirname(__DIR__, 2) . '/');
}

final class LivePreviewVarsTest extends \PHPUnit\Framework\TestCase {
    public function test_style_updates_on_render(): void {
        $js = FBM_PATH . 'assets/js/theme-preview.js';
        $node = <<<'NODE'
const fs=require('fs');
const code=fs.readFileSync(process.argv[1],'utf8');
global.document={
    head:{node:null,appendChild(n){this.node=n;}},
    querySelector(sel){return sel==='style[data-fbm-preview]'?this.head.node:null;},
    createElement(tag){return {tagName:tag,dataset:{},textContent:''};},
    querySelectorAll(){return[];}
};
global.window={};
eval(code);
window.FBMPreview.render({'--fbm-h3':'22px','--fbm-tabs-h':'44px'});
const first=document.head.node;
window.FBMPreview.render({'--fbm-h3':'22px','--fbm-tabs-h':'44px'});
const second=document.head.node;
console.log(JSON.stringify({single:first===second,css:second.textContent}));
NODE;
        $cmd = 'node -e ' . escapeshellarg($node) . ' ' . escapeshellarg($js);
        $out = shell_exec($cmd);
        $data = json_decode((string)$out, true);
        $this->assertTrue($data['single']);
        $this->assertStringContainsString('--fbm-h3:22px', $data['css']);
        $this->assertStringContainsString('--fbm-tabs-h:44px', $data['css']);
        $this->assertStringContainsString('@layer fbm', $data['css']);
    }
}

