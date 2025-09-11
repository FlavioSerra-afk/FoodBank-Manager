(function(){
    function buildAttrs(tag){
        var attrsWrap = document.getElementById('fbm-attrs');
        if(!attrsWrap){return;}
        attrsWrap.innerHTML='';
        var sc = (window.FBM_SHORTCODES || []).find(function(s){return s.tag===tag;});
        if(!sc){return;}
        Object.entries(sc.atts).forEach(function(entry){
            var name = entry[0];
            var info = entry[1];
            var p = document.createElement('p');
            var label = document.createElement('label');
            label.textContent = name + ' ';
            var field;
            if(info.type==='bool'){
                field = document.createElement('select');
                ['true','false'].forEach(function(v){
                    var o = document.createElement('option');
                    o.value = v;
                    o.text = v;
                    field.appendChild(o);
                });
            }else if(info.type==='enum'){
                field = document.createElement('select');
                (info.options||[]).forEach(function(v){
                    var o = document.createElement('option');
                    o.value = v;
                    o.text = v;
                    field.appendChild(o);
                });
            }else{
                field = document.createElement('input');
                field.type = 'text';
            }
            field.name = 'atts['+name+']';
            var cur = window.FBM_CURRENT && window.FBM_CURRENT.tag===tag ? (window.FBM_CURRENT.atts[name]||'') : info.default;
            field.value = cur;
            label.appendChild(field);
            p.appendChild(label);
            attrsWrap.appendChild(p);
        });
    }
    function copySnippet(text){
        if(navigator.clipboard && navigator.clipboard.writeText){
            navigator.clipboard.writeText(text);
            return;
        }
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.top = '-1000px';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        try{document.execCommand('copy');}catch(e){}
        document.body.removeChild(ta);
    }
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('fbm-shortcodes-form');
        var select = document.getElementById('fbm-tag');
        var outputWrap = document.getElementById('fbm-output');
        var outField = document.getElementById('fbm-shortcode-string');
        if(select){
            select.addEventListener('change', function(){buildAttrs(this.value);});
        }
        var gen = document.getElementById('fbm-generate');
        if(gen){
            gen.addEventListener('click', function(){
                var tag = select.value;
                if(!tag){return;}
                var parts = [];
                document.querySelectorAll('#fbm-attrs [name^="atts["]').forEach(function(el){
                    var m = el.name.match(/atts\[(.*)\]/);
                    if(!m){return;}
                    var name = m[1];
                    var val = el.value.trim();
                    if(val!==''){
                        parts.push(name+'="'+val.replace(/"/g,'&quot;')+'"');
                    }
                });
                parts.push('mask_sensitive="true"');
                var sc = '['+tag+(parts.length?' '+parts.join(' '):'')+']';
                if(outField){outField.value=sc;}
                if(outputWrap){outputWrap.style.display='block';}
            });
        }
        if(form){
            form.addEventListener('submit', function(){
                var hidden = document.createElement('input');
                hidden.type='hidden';
                hidden.name='atts[mask_sensitive]';
                hidden.value='true';
                form.appendChild(hidden);
            });
        }
        document.addEventListener('click', function(e){
            if(e.target.classList && e.target.classList.contains('fbm-copy')){
                var snippet = e.target.dataset.snippet || (outField ? outField.value : '');
                if(snippet){copySnippet(snippet);}
            }
        });
        if(window.FBM_CURRENT && window.FBM_CURRENT.tag && select){
            select.value = window.FBM_CURRENT.tag;
            buildAttrs(window.FBM_CURRENT.tag);
        }
    });
})();
