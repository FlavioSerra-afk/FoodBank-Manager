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

  const exportBtn=document.querySelector('.fbm-export');
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

  const fileInput=document.querySelector('.fbm-utils-file');
  document.querySelector('.fbm-import')?.addEventListener('click',()=>{fileInput?.click();});
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

  document.querySelector('.fbm-defaults')?.addEventListener('click',()=>{
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
