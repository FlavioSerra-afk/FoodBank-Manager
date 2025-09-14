(function($){
    const styleSelector = 'style[data-fbm-preview]';
    function render(tokens){
        let css = '';
        Object.keys(tokens).forEach(key=>{
            css += key + ':' + tokens[key] + ';';
        });
        const cssText = '@layer fbm {.fbm-scope{' + css + '}}';
        let $style = $(styleSelector);
        if(!$style.length){
            $style = $('<style>',{'data-fbm-preview':''}).appendTo(document.head);
        }
        $style.text(cssText);
    }
    function gather(){
        const tokens = {};
        $('.fbm-theme-controls [data-token]').each(function(){
            const $el = $(this);
            const unit = $el.data('unit') || '';
            tokens[$el.data('token')] = $el.val() + unit;
        });
        render(tokens);
    }
    let timer;
    $('.fbm-theme-controls').on('input change','[data-token]',function(){
        clearTimeout(timer);
        timer = setTimeout(gather,125);
    });
    $('.fbm-reset-all').on('click',function(){
        $('.fbm-theme-controls [data-token]').each(function(){
            const $el = $(this);
            const def = $el.data('default');
            if (def !== undefined) {
                $el.val(def);
            }
        });
        gather();
    });
    $(gather);
    window.FBMPreview = {render};
})(jQuery);
