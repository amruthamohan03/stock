/**
 * Session Manager — Client Side (FINAL FIXED VERSION)
 *
 * Fixes applied on top of previous version:
 *  A. handleTimeout() now requires TWO consecutive failures before redirecting —
 *     eliminates spurious redirects from network blips or single failed fetches.
 *  B. checkSession does NOT reset last_activity on server. Remaining time
 *     returned is always accurate idle time (PHP fix B/C).
 *  C. fetch() wrapper always sends credentials + X-Requested-With (kept from v2).
 *  D. handleTimeout() is guarded by this.timedOut so it runs exactly once.
 *  E. Clear, consistent units: all PHP values are SECONDS, all timer IDs are MS.
 */

class SessionManager {
    constructor(config = {}) {
        this.baseUrl           = config.baseUrl           || '';
        this.sessionTimeout    = config.sessionTimeout    || 3600;   // seconds
        this.warningTime       = config.warningTime       || 300;    // seconds (5 min)
        this.checkInterval     = config.checkInterval     || 60000;  // ms     (1 min)
        this.keepAliveInterval = config.keepAliveInterval || 300000; // ms     (5 min)
        this.redirectUrl       = config.redirectUrl       || '/auth/login';

        // State
        this.lastActivity      = Date.now();
        this.checkTimer        = null;
        this.keepAliveTimer    = null;
        this.warningShown      = false;
        this.isActive          = false;
        this.countdownInterval = null;
        this.timedOut          = false;   // FIX D: fire timeout logic only once

        // FIX A: require two consecutive failures before redirecting
        this._failCount        = 0;
        this._maxFails         = 2;

        this.init();
    }

    // ── Init ──────────────────────────────────────────────────────────────────

    async init() {
        try {
            const config = await this.getSessionConfig();

            if (!config.isLoggedIn) {
                console.log('SessionManager: user not logged in — skipping.');
                return;
            }

            // Use authoritative server values
            this.sessionTimeout = config.timeout;
            this.warningTime    = config.warningTime || this.warningTime;
            this.isActive       = true;

            this.setupActivityTracking();
            this.startMonitoring();
            this.startKeepAlive();

            console.log(
                `SessionManager: ready | timeout=${this.sessionTimeout}s ` +
                `warning=${this.warningTime}s remaining=${config.remaining}s`
            );
        } catch (err) {
            console.error('SessionManager init error:', err);
        }
    }

    // ── Fetch wrapper ─────────────────────────────────────────────────────────

    /**
     * FIX C (retained): always send session cookie + AJAX header.
     * The AJAX header tells PHP not to call header() redirect on timeout.
     */
    async _fetch(path, options = {}) {
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers || {}),
        };
        return fetch(`${this.baseUrl}${path}`, {
            credentials: 'same-origin',
            ...options,
            headers,
        });
    }

    async getSessionConfig() {
        const res = await this._fetch('/auth/getConfig');
        if (!res.ok) throw new Error(`getConfig HTTP ${res.status}`);
        return res.json();
    }

    async checkSessionStatus() {
        const res = await this._fetch('/auth/checkSession');
        if (!res.ok) throw new Error(`checkSession HTTP ${res.status}`);
        return res.json();
    }

    async sendKeepAlive() {
        const res = await this._fetch('/auth/keepAlive', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
        });
        if (!res.ok) throw new Error(`keepAlive HTTP ${res.status}`);
        return res.json();
    }

    // ── Activity tracking ─────────────────────────────────────────────────────

    setupActivityTracking() {
        ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(evt =>
            document.addEventListener(evt, () => this.updateActivity(), { passive: true })
        );
    }

    updateActivity() {
        this.lastActivity = Date.now();
        if (this.warningShown) this.hideWarning();
    }

    // ── Timers ────────────────────────────────────────────────────────────────

    startMonitoring() {
        this.checkTimer = setInterval(() => this.checkSession(), this.checkInterval);
    }

    stopMonitoring() {
        clearInterval(this.checkTimer);
        this.checkTimer = null;
    }

    startKeepAlive() {
        this.keepAliveTimer = setInterval(async () => {
            try {
                const data = await this.sendKeepAlive();
                if (data.success) {
                    console.debug(`SessionManager: keep-alive OK — ${data.remaining}s left`);
                    this._failCount = 0; // reset on success
                } else {
                    console.warn('SessionManager: keep-alive rejected', data);
                    this._handleFailure();
                }
            } catch (err) {
                console.error('SessionManager: keep-alive error:', err);
                this._handleFailure();
            }
        }, this.keepAliveInterval);
    }

    stopKeepAlive() {
        clearInterval(this.keepAliveTimer);
        this.keepAliveTimer = null;
    }

    // ── Session check ─────────────────────────────────────────────────────────

    async checkSession() {
        if (this.timedOut) return;

        try {
            const data = await this.checkSessionStatus();

            // FIX A: require consecutive failures before acting
            if (!data.isLoggedIn) {
                this._handleFailure();
                return;
            }

            // Successful check — reset fail counter
            this._failCount = 0;

            const remaining = data.remaining; // seconds from server

            if (remaining <= 0) {
                this.handleTimeout();
                return;
            }

            if (remaining <= this.warningTime && !this.warningShown) {
                this.showWarning(remaining);
            }

        } catch (err) {
            // Network error — count as a failure but don't immediately redirect
            console.error('SessionManager: checkSession error:', err);
            this._handleFailure();
        }
    }

    /**
     * FIX A: Track consecutive failures.
     * Only trigger timeout after _maxFails in a row to survive network blips.
     */
    _handleFailure() {
        this._failCount++;
        console.warn(`SessionManager: failure ${this._failCount}/${this._maxFails}`);
        if (this._failCount >= this._maxFails) {
            this.handleTimeout();
        }
    }

    // ── Warning modal ─────────────────────────────────────────────────────────

    showWarning(remainingSeconds) {
        this.warningShown = true;
        if (!document.getElementById('sessionWarningModal')) {
            this._createWarningModal();
        }
        this._updateWarningMessage(remainingSeconds);
        bootstrap.Modal.getOrCreateInstance(
            document.getElementById('sessionWarningModal')
        ).show();
        this._startCountdown(remainingSeconds);
    }

    _updateWarningMessage(secs) {
        const m = Math.floor(secs / 60);
        const s = Math.floor(secs % 60);
        const el = document.getElementById('sessionWarningMessage');
        if (el) {
            el.textContent =
                `Your session will expire in ${m} min ${s} sec. ` +
                `Any unsaved changes will be lost.`;
        }
    }

    _startCountdown(initialSecs) {
        clearInterval(this.countdownInterval);
        let remaining = Math.floor(initialSecs);

        this.countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
                this.handleTimeout();
                return;
            }
            this._updateWarningMessage(remaining);
        }, 1000);
    }

    hideWarning() {
        this.warningShown = false;
        clearInterval(this.countdownInterval);
        this.countdownInterval = null;

        const el = document.getElementById('sessionWarningModal');
        if (el) {
            const m = bootstrap.Modal.getInstance(el);
            if (m) m.hide();
        }
    }

    _createWarningModal() {
        document.body.insertAdjacentHTML('beforeend', `
            <div class="modal fade" id="sessionWarningModal"
                 data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Session Timeout Warning
                            </h5>
                        </div>
                        <div class="modal-body">
                            <p id="sessionWarningMessage" class="mb-0"></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    onclick="window.sessionManager.logout()">
                                Logout Now
                            </button>
                            <button type="button" class="btn btn-primary"
                                    onclick="window.sessionManager.extendSession()">
                                Continue Working
                            </button>
                        </div>
                    </div>
                </div>
            </div>`);
    }

    // ── Extend / Logout / Timeout ─────────────────────────────────────────────

    async extendSession() {
        try {
            const data = await this.sendKeepAlive();
            if (data.success) {
                this._failCount = 0;
                this.updateActivity();
                this.hideWarning();
                this.showNotification('Session extended successfully.', 'success');
            } else {
                this.showNotification('Could not extend session. Please save your work.', 'danger');
            }
        } catch (err) {
            console.error('SessionManager: extendSession error:', err);
            this.showNotification('Failed to extend session.', 'danger');
        }
    }

    /**
     * FIX D: guarded by this.timedOut — runs exactly once.
     * Redirects after 3 seconds so user can read the notification.
     */
    handleTimeout() {
        if (this.timedOut) return;
        this.timedOut = true;

        this.stopMonitoring();
        this.stopKeepAlive();
        this.hideWarning();

        this.showNotification(
            'Your session has expired. Redirecting to login…', 'warning'
        );

        setTimeout(() => {
            window.location.href = this.redirectUrl;
        }, 3000);
    }

    logout() {
        this.stopMonitoring();
        this.stopKeepAlive();
        this.hideWarning();
        window.location.href = `${this.baseUrl}/auth/logout`;
    }

    // ── Toast ─────────────────────────────────────────────────────────────────

    showNotification(message, type = 'info') {
        if (!document.getElementById('sessionNotification')) {
            document.body.insertAdjacentHTML('beforeend', `
                <div id="sessionNotification"
                     class="position-fixed top-0 end-0 p-3" style="z-index:9999;">
                    <div class="toast align-items-center border-0" role="alert"
                         aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body"></div>
                            <button type="button" class="btn-close me-2 m-auto"
                                    data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                </div>`);
        }

        const toastEl = document.querySelector('#sessionNotification .toast');
        toastEl.querySelector('.toast-body').textContent = message;
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    }

    destroy() {
        this.stopMonitoring();
        this.stopKeepAlive();
        this.hideWarning();
        this.isActive = false;
    }
}

// ── Bootstrap ─────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    window.sessionManager = new SessionManager({
        baseUrl:           window.BASE_URL || '',
        sessionTimeout:    3600,    // seconds — overridden by /auth/getConfig
        warningTime:       300,     // seconds (5 min)
        checkInterval:     60000,   // ms      (1 min)
        keepAliveInterval: 300000,  // ms      (5 min)
        redirectUrl:       (window.BASE_URL || '') + '/auth/login',
    });
});