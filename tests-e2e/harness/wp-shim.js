(function (window) {
  if (!window) {
    return;
  }

  if (!window.wp) {
    window.wp = {};
  }

  if (!window.wp.i18n) {
    window.wp.i18n = {};
  }

  window.wp.i18n.__ = window.wp.i18n.__ || ((s) => s);
  window.wp.i18n._x = window.wp.i18n._x || ((s) => s);
  window.wp.i18n._n = window.wp.i18n._n || ((a, b, n) => (n === 1 ? a : b));
  window.wp.i18n.sprintf = window.wp.i18n.sprintf || ((...args) => String.raw(...args));

  window.FBM = window.FBM || {};
  window.FBM.ajaxUrl = window.FBM.ajaxUrl || 'https://example.test/wp-admin/admin-ajax.php';
  window.FBM.restUrl = window.FBM.restUrl || 'https://example.test/wp-json/fbm/v1/checkin';
  window.FBM.exportsBase = window.FBM.exportsBase || 'https://example.test/wp-admin/exports';
  window.FBM.nonce = window.FBM.nonce || 'e2e-nonce';
})(typeof window !== 'undefined' ? window : undefined);
