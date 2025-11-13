<div id="settings-flash" class="p-3 hidden">
  <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-3 py-2 text-sm">
    {{ __('instellingen.flash.saved') }}
  </div>
</div>

<script>
(function(){
  // 1) Nieuwe taalcode zetten op <html lang="...">
  const currentLocale = @json(app()->getLocale());

  // 2) Woordenboek: layout + instellingen (server-side al in de nieuwe taal)
  const dict = {
    __lang: currentLocale,

    // --- Layout: sidebar ---
    'sidebar.overview'    : @json(__('sidebar.overview')),
    'sidebar.support'     : @json(__('sidebar.support')),
    'sidebar.leads'       : @json(__('sidebar.leads')),
    'sidebar.projects'    : @json(__('sidebar.projects')),
    'sidebar.marketing'   : @json(__('sidebar.marketing')),
    'sidebar.users'       : @json(__('sidebar.users')),
    'sidebar.add_module'  : @json(__('sidebar.add_module')),
    'sidebar.audit_log'   : @json(__('sidebar.audit_log')),

    // --- Layout: profiel dropdown ---
    'profile_dropdown.persoonlijke_gegevens': @json(__('profile_dropdown.persoonlijke_gegevens')),
    'profile_dropdown.bedrijfsinstellingen' : @json(__('profile_dropdown.bedrijfsinstellingen')),
    'profile_dropdown.abonnement_betaling'  : @json(__('profile_dropdown.abonnement_betaling')),
    'profile_dropdown.support'              : @json(__('profile_dropdown.support')),
    'profile_dropdown.uitloggen'            : @json(__('profile_dropdown.uitloggen')),

    // --- Instellingen-pagina ---
    'instellingen.title'              : @json(__('instellingen.title')),
    'instellingen.search_placeholder' : @json(__('instellingen.search_placeholder')),
    'instellingen.tabs.personal'      : @json(__('instellingen.tabs.personal')),
    'instellingen.tabs.company'       : @json(__('instellingen.tabs.company')),
    'instellingen.tabs.team'          : @json(__('instellingen.tabs.team')),
    'instellingen.tabs.billing'       : @json(__('instellingen.tabs.billing')),
    'instellingen.tabs.documents'     : @json(__('instellingen.tabs.documents')),
    'instellingen.fields.name'        : @json(__('instellingen.fields.name')),
    'instellingen.fields.email'       : @json(__('instellingen.fields.email')),
    'instellingen.fields.lang'        : @json(__('instellingen.fields.lang')),
    'instellingen.actions.save'       : @json(__('instellingen.actions.save')),
  };

  // 3) Globaal: update alle elementen met data-i18n (layout + page)
  if (window.applyI18n) {
    window.applyI18n(dict);
  }

  // 4) Extra: als je (nog) IDs gebruikt i.p.v. data-i18n, update die ook
  const map = {
    '#i18n-settings-title' : 'instellingen.title',
    '#i18n-tab-personal'   : 'instellingen.tabs.personal',
    '#i18n-tab-company'    : 'instellingen.tabs.company',
    '#i18n-tab-team'       : 'instellingen.tabs.team',
    '#i18n-tab-billing'    : 'instellingen.tabs.billing',
    '#i18n-tab-docs'       : 'instellingen.tabs.documents',
    '#i18n-label-name'     : 'instellingen.fields.name',
    '#i18n-label-email'    : 'instellingen.fields.email',
    '#i18n-label-lang'     : 'instellingen.fields.lang',
    '#i18n-save-btn'       : 'instellingen.actions.save',
    // attribuut update:
    '#users-search@placeholder': 'instellingen.search_placeholder',
  };
  Object.entries(map).forEach(([sel, key])=>{
    const [selector, attr] = sel.split('@');
    const el  = document.querySelector(selector);
    const val = dict[key];
    if (!el || val == null) return;
    attr ? el.setAttribute(attr, val) : el.textContent = val;
  });

  // 5) Label van de taal-knop updaten op basis van de hidden <select>
  const langBtnText = document.getElementById('lang-btn-text');
  const langSelect  = document.getElementById('lang-select');
  if (langBtnText && langSelect) {
    const opt = langSelect.querySelector(`option[value="${currentLocale}"]`);
    if (opt) langBtnText.textContent = opt.textContent;
  }
})();
</script>