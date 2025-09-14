(()=>{
    const styleSelector = 'style[data-fbm-preview]';
    const scopeSelector = '.fbm-scope';

    function render(tokens){
        const declarations = Object.entries(tokens).map(([k,v])=>`${k}:${v};`).join('');
        const cssText = `@layer fbm { ${scopeSelector} { ${declarations} } }`;
        let style = document.querySelector(styleSelector);
        if(!style){
            style = document.createElement('style');
            style.dataset.fbmPreview = '';
            document.head.appendChild(style);
        }
        style.textContent = cssText;
    }

    function gather(){
        const tokens = {};
        document.querySelectorAll('.fbm-theme-controls [data-token]').forEach(el=>{
            const unit = el.dataset.unit || '';
            tokens[el.dataset.token] = el.value + unit;
        });
        render(tokens);
    }

    const debounce = (fn,delay)=>{
        let timer;return (...args)=>{clearTimeout(timer);timer=setTimeout(()=>fn(...args),delay);};
    };
    const onInput = debounce(gather,125);

    const controls = document.querySelector('.fbm-theme-controls');
    if(controls){
        controls.addEventListener('input',e=>{ if(e.target && e.target.dataset && e.target.dataset.token !== undefined){ onInput(); } });
        controls.addEventListener('change',e=>{ if(e.target && e.target.dataset && e.target.dataset.token !== undefined){ onInput(); } });
    }

    document.querySelectorAll('.fbm-reset-all').forEach(btn=>{
        btn.addEventListener('click',()=>{
            document.querySelectorAll('.fbm-theme-controls [data-token]').forEach(el=>{
                const def = el.dataset.default;
                if(def !== undefined){ el.value = def; }
            });
            gather();
        });
    });

    if(document.readyState !== 'loading'){ gather(); }
    else{ document.addEventListener('DOMContentLoaded', gather); }

    window.FBMPreview = { render };
})();
