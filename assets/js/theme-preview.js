(function($){
    const styleId = 'fbm-preview-vars';
    const $preview = $('[data-fbm-preview]');
    function render(tokens){
        let css = '';
        Object.keys(tokens).forEach(key=>{
            css += key + ':' + tokens[key] + ';';
        });
        const cssText = '@layer fbm {.fbm-preview.fbm-scope{' + css + '}}';
        let $style = $preview.find('#'+styleId);
        if(!$style.length){
            $style = $('<style/>',{id:styleId}).appendTo($preview);
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
        timer = setTimeout(gather,175);
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
