(() => {
    'use strict';
    let toastTimer;
    const csrf = () => document.querySelector('meta[name=csrf-token]').content;
    function notify(message) {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.hidden = true; }, 3500);
    }
    window.prepareTakedown = form => {
        const reason = window.prompt('Alasan takedown postingan:');
        if (!reason || reason.trim().length < 3) return false;
        form.elements.moderation_reason.value = reason.trim();
        const button = form.querySelector('button[type=submit]');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        return true;
    };
    // Account controls stay in the header, including on small screens.
    const toggle = document.getElementById('account-toggle');
    const menu = document.getElementById('account-dropdown');
    function closeAccount(focus = false) {
        if (!menu) return;
        menu.hidden = true;
        toggle.setAttribute('aria-expanded', 'false');
        if (focus) toggle.focus();
    }
    toggle?.addEventListener('click', () => {
        const open = menu.hidden;
        menu.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', e => {
        if (menu && !e.target.closest('.account-menu')) closeAccount();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && menu && !menu.hidden) closeAccount(true);
    });
    document.addEventListener('focusin', e => {
        if (menu && !e.target.closest('.account-menu')) closeAccount();
    });
    document.querySelectorAll('[data-preview]').forEach(input => input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        const target = document.getElementById(input.dataset.preview);
        if (target.dataset.objectUrl) URL.revokeObjectURL(target.dataset.objectUrl);
        target.dataset.objectUrl = URL.createObjectURL(file);
        target.src = target.dataset.objectUrl;
        target.hidden = false;
    }));
    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-bookmark],[data-share]');
        if (!button) return;
        if (button.dataset.share) {
            try { await navigator.clipboard.writeText(button.dataset.share); notify('Link postingan disalin.'); }
            catch { window.prompt('Salin link postingan:', button.dataset.share); }
            return;
        }
        button.disabled = true;
        try {
            const res = await fetch(button.dataset.bookmark, {method: button.getAttribute('aria-pressed') === 'true' ? 'DELETE' : 'POST', headers: {'X-CSRF-TOKEN': csrf(), Accept: 'application/json'}});
            if (!res.ok) throw new Error();
            const data = await res.json();
            document.querySelectorAll('[data-bookmark]').forEach(b => {
                if (b.dataset.bookmark === button.dataset.bookmark) b.setAttribute('aria-pressed', String(data.saved));
            });
            notify(data.saved ? 'Postingan disimpan.' : 'Dihapus dari koleksi.');
        } catch { notify('Belum berhasil. Coba lagi atau masuk ulang.'); }
        finally { button.disabled = false; }
    });

    const chat = document.getElementById('chat-messages');
    if (!chat || chat.dataset.initialized) return;
    chat.dataset.initialized = 'true';
    const status = document.getElementById('chat-status');
    const form = document.getElementById('chat-compose');
    const input = form.elements.body;
    const sendButton = form.querySelector('button[type=submit]');
    const seen = new Set([...chat.querySelectorAll('[data-message-id]')].map(el => el.dataset.messageId));
    let cursor = Number(chat.dataset.after) || 0;
    let polling = false;
    let sending = false;
    let timer;
    let stopped = false;
    const format = new Intl.DateTimeFormat('id-ID', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'});
    function formatTime(el) { el.textContent = format.format(new Date(el.dateTime)); }
    chat.querySelectorAll('time[datetime]').forEach(formatTime);
    chat.scrollTop = chat.scrollHeight;
    function report(message) { status.textContent = message; status.hidden = !message; }
    function insert(message) {
        const id = String(message.id);
        if (seen.has(id)) return;
        seen.add(id);
        document.getElementById('chat-empty')?.remove();
        const bubble = document.createElement('div');
        bubble.dataset.messageId = id;
        bubble.className = 'bubble' + (String(message.sender_id) === chat.dataset.me ? ' mine' : '');
        const body = document.createElement('span');
        body.textContent = message.body;
        const time = document.createElement('time');
        time.dateTime = message.created_at;
        formatTime(time);
        bubble.append(body, time);
        // A send response may arrive before an older incoming message's poll.
        const next = [...chat.querySelectorAll('[data-message-id]')].find(el => Number(el.dataset.messageId) > Number(message.id));
        chat.insertBefore(bubble, next || null);
    }
    function schedule() {
        clearTimeout(timer);
        if (!stopped && chat.dataset.poll) timer = setTimeout(poll, 5000);
    }
    async function poll() {
        if (stopped || polling || !chat.dataset.poll) return;
        if (document.hidden) { schedule(); return; }
        polling = true;
        try {
            const res = await fetch(chat.dataset.poll + '?after=' + cursor, {headers: {Accept: 'application/json'}, cache: 'no-store', signal: AbortSignal.timeout(15000)});
            if (!res.ok) throw new Error();
            const messages = await res.json();
            if (!Array.isArray(messages)) throw new Error();
            const nearBottom = chat.scrollHeight - chat.scrollTop - chat.clientHeight < 75;
            for (const message of messages) {
                insert(message);
                cursor = Math.max(cursor, Number(message.id));
            }
            chat.dataset.after = String(cursor);
            if (nearBottom) chat.scrollTop = chat.scrollHeight;
            if (!sending && !form.dataset.failed) report('');
        } catch { if (!form.dataset.failed) report('Koneksi terputus. Menghubungkan kembali…'); }
        finally { polling = false; schedule(); }
    }
    form.addEventListener('submit', async event => {
        // Historical pages use the normal POST redirect to the newest page.
        if (!chat.dataset.poll) return;
        event.preventDefault();
        if (sending || !input.value.trim()) return;
        sending = true;
        sendButton.disabled = true;
        const body = input.value;
        report('');
        delete form.dataset.failed;
        try {
            const res = await fetch(form.action, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf(), Accept: 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify({body})});
            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                throw new Error(data.errors?.body?.[0] || 'Pesan belum terkirim. Coba kirim lagi.');
            }
            insert(await res.json());
            // Only polls advance the cursor; concurrent incoming messages cannot be skipped.
            if (input.value === body) input.value = '';
            chat.scrollTop = chat.scrollHeight;
            input.focus();
        } catch (error) {
            form.dataset.failed = 'true';
            report(error.message || 'Pesan belum terkirim. Coba kirim lagi.');
        } finally { sending = false; sendButton.disabled = false; }
    });
    document.querySelectorAll('[data-bot-option]').forEach(button => button.addEventListener('click', () => {
        if (sending) return;
        input.value = button.dataset.botOption;
        form.requestSubmit();
    }));
    document.addEventListener('visibilitychange', () => {
        clearTimeout(timer);
        if (!document.hidden) poll();
    });
    window.addEventListener('pagehide', () => { stopped = true; clearTimeout(timer); });
    window.addEventListener('pageshow', () => { stopped = false; schedule(); });
    schedule();
})();
