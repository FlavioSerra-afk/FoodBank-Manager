(function(){
    var input = document.getElementById('schema_json');
    if (!input) { return; }
    var schema = [];
    try { schema = JSON.parse(input.value || '[]'); } catch(e){ schema = []; }
    function sync(){ input.value = JSON.stringify(schema); }
    window.fbmFormBuilder = {
        addField: function(type,label){ schema.push({type:type,label:label}); sync(); },
        getSchema: function(){ return schema; }
    };
})();
