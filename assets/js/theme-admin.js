(function(){
  // Vertical tabs → swap visible panel
  const tablist=document.querySelector('.fbm-vtabs');
  if(tablist){
    const tabs=Array.from(tablist.querySelectorAll('.fbm-vtab[role="tab"]'));
    const panels=tabs.map(t=>document.getElementById(t.getAttribute('aria-controls'))).filter(Boolean);

    function select(idx){
      tabs.forEach((t,i)=>{
        const sel=i===idx;
        t.setAttribute('aria-selected',sel?'true':'false');
        t.tabIndex=sel?0:-1;
      });
      panels.forEach((p,i)=>{if(p)p.hidden=i!==idx;});
    }

    tablist.addEventListener('click',e=>{
      const i=tabs.indexOf(e.target.closest('.fbm-vtab'));
      if(i>=0)select(i);
    });
    tablist.addEventListener('keydown',e=>{
      const current=tabs.findIndex(t=>t.getAttribute('aria-selected')==='true');
      if(e.key==='ArrowUp'||e.key==='ArrowLeft'){e.preventDefault();select((current-1+tabs.length)%tabs.length);tabs[(current-1+tabs.length)%tabs.length].focus();}
      if(e.key==='ArrowDown'||e.key==='ArrowRight'){e.preventDefault();select((current+1)%tabs.length);tabs[(current+1)%tabs.length].focus();}
      if(e.key==='Home'){e.preventDefault();select(0);tabs[0].focus();}
      if(e.key==='End'){e.preventDefault();select(tabs.length-1);tabs[tabs.length-1].focus();}
    });

    select(Math.max(0,tabs.findIndex(t=>t.getAttribute('aria-selected')==='true')));
  }
})();

(()=>{
  const form=document.getElementById('fbm-theme-form');
  const preview=document.getElementById('fbm-preview-vars');
  if(!form||!preview||typeof fbmTheme==='undefined'){return;}

  function serialize(){
    const out={};
    const fd=new FormData(form);
    for(const [k,v] of fd.entries()){
      const m=k.match(/^fbm_theme\[(.+)\]$/);
      if(m){ out[m[1]]=v; }
    }
    return out;
  }

  function render(){
    const payload=serialize();
    fetch(fbmTheme.ajaxUrl,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body:new URLSearchParams({action:'fbm_css_preview',_ajax_nonce:fbmTheme.nonce,payload:JSON.stringify(payload)})
    }).then(r=>r.text()).then(css=>{preview.textContent=css;});
  }

  form.addEventListener('input',render);
  form.addEventListener('change',render);

  const exportBtn=document.getElementById('fbm-export-btn');
  exportBtn?.addEventListener('click',()=>{
    const data=Object.assign({version:1},serialize());
    const blob=new Blob([JSON.stringify(data)],{type:'application/json'});
    const url=URL.createObjectURL(blob);
    const a=document.createElement('a');
    a.href=url;
    a.download='fbm-theme.json';
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(()=>URL.revokeObjectURL(url),1000);
  });

  const fileInput=document.getElementById('fbm-import-file');
  document.getElementById('fbm-import-btn')?.addEventListener('click',()=>{fileInput?.click();});
  fileInput?.addEventListener('change',()=>{
    const file=fileInput.files[0];
    if(!file){return;}
    const reader=new FileReader();
    reader.onload=()=>{
      try{
        const json=JSON.parse(reader.result);
        Object.keys(json).forEach(k=>{
          if('version'===k){return;}
          form.querySelectorAll('[name="fbm_theme['+k+']"]').forEach(el=>{
            if(el.type==='radio'){
              el.checked=(el.value==json[k]);
            }else{
              el.value=json[k];
            }
          });
        });
        render();
      }catch(e){}
    };
    reader.readAsText(file);
    fileInput.value='';
  });

  document.getElementById('fbm-defaults-btn')?.addEventListener('click',()=>{
    fetch(fbmTheme.ajaxUrl,{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body:new URLSearchParams({action:'fbm_theme_defaults'})
    }).then(r=>r.json()).then(json=>{
      Object.keys(json).forEach(k=>{
        form.querySelectorAll('[name="fbm_theme['+k+']"]').forEach(el=>{
          if(el.type==='radio'){
            el.checked=(el.value==json[k]);
          }else{
            el.value=json[k];
          }
        });
      });
      render();
    });
  });

  render();
})();
