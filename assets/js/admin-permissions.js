(function(){
    function init(){
        if(!window.fbmPerms){return;}
        document.querySelectorAll('.fbm-perm-toggle').forEach(function(cb){
            cb.addEventListener('change', function(){
                var role = this.dataset.role;
                var cap = this.dataset.cap;
                var grant = this.checked ? '1' : '0';
                var params = new URLSearchParams();
                params.append('action','fbm_perms_role_toggle');
                params.append('role', role);
                params.append('cap', cap);
                params.append('grant', grant);
                params.append('_wpnonce', fbmPerms.nonce);
                fetch(fbmPerms.url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: params.toString()
                }).then(function(res){
                    if(!res.ok){throw new Error('network');}
                    return res.json();
                }).then(function(data){
                    if(!data || !data.success){throw new Error('fail');}
                }).catch(function(){
                    cb.checked = !cb.checked;
                });
            });
        });
    }
    document.addEventListener('DOMContentLoaded', init);
})();
