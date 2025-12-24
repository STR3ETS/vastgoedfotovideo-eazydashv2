window.notifBell = function ({ csrf, indexUrl, readUrlBase, readAllUrl }) {
  return {
    csrf,
    indexUrl,
    readUrlBase,
    readAllUrl,

    open: false,
    items: [],
    unreadCount: 0,

    _timer: null,

    init() {
      this.refresh();

      // lichte polling (zodat badge live blijft)
      this._timer = setInterval(() => this.refresh(), 15000);
      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') this.refresh();
      });
    },

    async refresh() {
      try {
        const res = await fetch(this.indexUrl, {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });
        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) return;

        this.items = data.items || [];
        this.unreadCount = Number(data.unread_count || 0);
      } catch (e) {
        console.error(e);
      }
    },

    toggle() {
      this.open = !this.open;
      if (this.open) this.refresh();
    },

    async readAll() {
      try {
        const res = await fetch(this.readAllUrl, {
          method: 'PATCH',
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
          },
          credentials: 'same-origin',
        });
        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) return;

        this.unreadCount = 0;
        // local state ook updaten
        this.items = this.items.map(n => ({ ...n, read_at: n.read_at || new Date().toISOString() }));
      } catch (e) {
        console.error(e);
      }
    },

    async openNotification(n) {
      try {
        if (!n.read_at) {
          await fetch(`${this.readUrlBase}/${n.id}/read`, {
            method: 'PATCH',
            headers: {
              'X-CSRF-TOKEN': this.csrf,
              'X-Requested-With': 'XMLHttpRequest',
              Accept: 'application/json',
            },
            credentials: 'same-origin',
          });

          n.read_at = new Date().toISOString();
          this.unreadCount = Math.max(0, this.unreadCount - 1);
        }
      } catch (e) {
        console.error(e);
      } finally {
        // navigeer naar de melding
        window.location.href = n.data.url;
      }
    },
  };
};
