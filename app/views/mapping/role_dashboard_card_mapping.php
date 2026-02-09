<!-- jQuery (if not already loaded) -->
<script>
    if (typeof jQuery === 'undefined') {
        document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
    }
</script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    /* Card Selection Styles */
    .card-selector-container {
        max-height: 600px;
        overflow-y: auto;
        padding: 10px;
    }
    
    .page-section {
        margin-bottom: 30px;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .page-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .page-header .badge {
        font-size: 0.8rem;
    }
    
    .page-body {
        padding: 15px;
        background: #fafafa;
    }
    
    .category-section {
        margin-bottom: 20px;
    }
    
    .category-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 15px;
        border-radius: 8px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    
    .category-header.general { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .category-header.import { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .category-header.export { background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%); }
    .category-header.finance { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
    .category-header.admin { background: linear-gradient(135deg, #4776E6 0%, #8E54E9 100%); }
    
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }
    
    .selectable-card {
        position: relative;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    
    .selectable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        border-color: #667eea;
    }
    
    .selectable-card.selected {
        border-color: #28a745;
        background: linear-gradient(145deg, #f0fff4 0%, #dcffe4 100%);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
    }
    
    .selectable-card.selected .card-check {
        opacity: 1;
        transform: scale(1);
    }
    
    .card-check {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 24px;
        height: 24px;
        background: #28a745;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        opacity: 0;
        transform: scale(0);
        transition: all 0.3s ease;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.4);
    }
    
    .card-icon-wrapper {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 1.2rem;
        color: white;
    }
    
    .card-icon-wrapper.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .card-icon-wrapper.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .card-icon-wrapper.warning { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
    .card-icon-wrapper.danger { background: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%); }
    .card-icon-wrapper.info { background: linear-gradient(135deg, #396afc 0%, #2948ff 100%); }
    .card-icon-wrapper.purple { background: linear-gradient(135deg, #7F00FF 0%, #E100FF 100%); }
    .card-icon-wrapper.teal { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .card-icon-wrapper.pink { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); }
    
    .card-title-text {
        font-weight: 600;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 2px;
    }
    
    .card-subtitle-text {
        font-size: 0.75rem;
        color: #888;
    }
    
    /* Select All Controls */
    .select-controls {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .select-controls .btn {
        font-size: 0.8rem;
        padding: 5px 12px;
    }
    
    /* Role Selector Styling */
    .role-select-wrapper select {
        padding: 10px 15px;
        font-size: 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    
    .role-select-wrapper select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }
    
    /* Stats Badge */
    .stats-badge {
        background: #667eea;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Loading Overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 12px;
        min-height: 200px;
    }
    
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Toast Notification */
    .toast-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }
    
    .toast-notification.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .toast-notification.error { background: linear-gradient(135deg, #cb2d3e 0%, #ef473a 100%); }
    
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    /* Filter Buttons */
    .filter-btn {
        border: 2px solid #e0e0e0;
        background: white;
        color: #666;
        border-radius: 20px;
        padding: 5px 14px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .filter-btn:hover, .filter-btn.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
    }

    /* Group By Toggle */
    .group-toggle {
        display: flex;
        gap: 5px;
        background: #f0f0f0;
        padding: 3px;
        border-radius: 8px;
    }
    
    .group-toggle .btn {
        padding: 4px 12px;
        font-size: 0.8rem;
        border: none;
        background: transparent;
        color: #666;
        border-radius: 6px;
    }
    
    .group-toggle .btn.active {
        background: white;
        color: #333;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Quick Stats */
    .quick-stats {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        padding: 10px 0;
    }
    
    .quick-stat {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .quick-stat .stat-icon {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
    }
    
    .quick-stat .stat-info {
        line-height: 1.2;
    }
    
    .quick-stat .stat-value {
        font-weight: 700;
        font-size: 1.1rem;
        color: #333;
    }
    
    .quick-stat .stat-label {
        font-size: 0.75rem;
        color: #888;
    }
</style>

<div class="page-content">
    <div class="page-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h4 class="mb-1"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Dashboard Card Mapping</h4>
                                <small class="text-muted">Assign dashboard cards to user roles by page</small>
                            </div>
                            <div class="stats-badge" id="selectionStats">
                                <i class="bi bi-check2-square"></i>
                                <span id="selectedCount">0</span> / <span id="totalCount">0</span> cards selected
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Role and Filter Selection -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-person-badge me-2"></i>Select Role</label>
                                <div class="role-select-wrapper">
                                    <select id="role_id" class="form-select">
                                        <option value="">-- Choose a Role --</option>
                                        <?php foreach ($roles as $r): ?>
                                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Filter by Page</label>
                                <select id="menu_filter" class="form-select">
                                    <option value="">-- All Pages --</option>
                                    <?php foreach ($menus as $menu): ?>
                                        <option value="<?= $menu['id'] ?>"><?= htmlspecialchars($menu['menu_name']) ?> (<?= $menu['card_count'] ?> cards)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><i class="bi bi-funnel me-2"></i>Filter by Category</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" class="filter-btn active" data-category="all">All</button>
                                    <?php foreach ($categories as $cat): ?>
                                        <button type="button" class="filter-btn" data-category="<?= $cat['card_category'] ?>">
                                            <?= ucfirst($cat['card_category']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats (shown after role selected) -->
                        <div class="quick-stats mb-3" id="quickStats" style="display: none;">
                            <div class="quick-stat">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                    <i class="bi bi-grid-3x3"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value" id="totalCardsCount">0</div>
                                    <div class="stat-label">Total Cards</div>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value" id="mappedCardsCount">0</div>
                                    <div class="stat-label">Mapped Cards</div>
                                </div>
                            </div>
                            <div class="quick-stat">
                                <div class="stat-icon" style="background: linear-gradient(135deg, #fc4a1a 0%, #f7b733 100%);">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                                <div class="stat-info">
                                    <div class="stat-value" id="unmappedCardsCount">0</div>
                                    <div class="stat-label">Unmapped</div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="select-controls">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="selectAll">
                                        <i class="bi bi-check-all me-1"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAll">
                                        <i class="bi bi-x-lg me-1"></i> Deselect All
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" id="invertSelection">
                                        <i class="bi bi-arrow-repeat me-1"></i> Invert
                                    </button>
                                </div>
                                <div class="group-toggle">
                                    <button type="button" class="btn active" data-group="page"><i class="bi bi-file-earmark me-1"></i>By Page</button>
                                    <button type="button" class="btn" data-group="category"><i class="bi bi-tag me-1"></i>By Category</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#copyModal">
                                <i class="bi bi-clipboard me-1"></i> Copy from Role
                            </button>
                        </div>

                        <!-- Card Selection Area -->
                        <form id="cardMappingForm">
                            <div class="card-selector-container position-relative" id="cardContainer">
                                <div class="text-center text-muted py-5" id="placeholder">
                                    <i class="bi bi-hand-index-thumb" style="font-size: 3rem; opacity: 0.5;"></i>
                                    <p class="mt-3">Select a role to load dashboard cards</p>
                                </div>
                            </div>

                            <div class="text-end mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-light me-2" id="resetBtn">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-save me-2"></i> Save Mapping
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>
</div>

<!-- Copy Mapping Modal -->
<div class="modal fade" id="copyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clipboard me-2"></i>Copy Mapping from Another Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Copy card mappings from one role to another.</p>
                <div class="mb-3">
                    <label class="form-label">Source Role (copy from)</label>
                    <select id="sourceRole" class="form-select">
                        <option value="">-- Select Source Role --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Target Role (copy to)</label>
                    <select id="targetRoleCopy" class="form-select">
                        <option value="">-- Select Target Role --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['role_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Filter by Page (optional)</label>
                    <select id="copyMenuFilter" class="form-select">
                        <option value="">-- All Pages --</option>
                        <?php foreach ($menus as $menu): ?>
                            <option value="<?= $menu['id'] ?>"><?= htmlspecialchars($menu['menu_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This will <strong>replace</strong> existing mappings for the target role.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmCopy">
                    <i class="bi bi-clipboard-check me-1"></i> Copy Mapping
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for jQuery to be available
(function checkJQuery() {
    if (typeof jQuery === 'undefined') {
        setTimeout(checkJQuery, 50);
        return;
    }
    
    // jQuery is available, initialize the app
    initDashboardCardMapping();
})();

function initDashboardCardMapping() {
    const $ = jQuery;
    
    $(document).ready(function() {
        let allCards = [];
        let selectedCards = new Set();
        let groupBy = 'page'; // 'page' or 'category'
        let categoryFilter = 'all';
        const APP_URL = '<?= APP_URL ?>';

        // Show toast notification
        function showToast(message, type) {
            type = type || 'success';
            const toast = $('<div class="toast-notification ' + type + '">' + message + '</div>');
            $('body').append(toast);
            setTimeout(function() { 
                toast.fadeOut(300, function() { 
                    $(this).remove(); 
                }); 
            }, 3000);
        }

        // Update selection stats
        function updateStats() {
            $('#selectedCount').text(selectedCards.size);
            $('#totalCount').text(allCards.length);
            $('#totalCardsCount').text(allCards.length);
            $('#mappedCardsCount').text(selectedCards.size);
            $('#unmappedCardsCount').text(allCards.length - selectedCards.size);
        }

        // Render cards grouped by page/menu
        function renderCardsByPage(cards) {
            const container = $('#cardContainer');
            container.empty();

            if (!cards || cards.length === 0) {
                container.html('<div class="text-center text-muted py-5"><i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i><p class="mt-3">No dashboard cards found</p></div>');
                return;
            }

            // Group by menu
            const grouped = {};
            cards.forEach(function(card) {
                const menuName = card.menu_name || 'Unassigned';
                if (!grouped[menuName]) {
                    grouped[menuName] = {
                        menu_id: card.menu_id,
                        cards: []
                    };
                }
                // Apply category filter
                if (categoryFilter === 'all' || card.card_category === categoryFilter) {
                    grouped[menuName].cards.push(card);
                }
            });

            // Render each page section
            Object.keys(grouped).sort().forEach(function(menuName) {
                if (grouped[menuName].cards.length === 0) return;

                const selectedInPage = grouped[menuName].cards.filter(function(c) { 
                    return selectedCards.has(c.card_id.toString()); 
                }).length;

                const section = $('<div class="page-section" data-menu-id="' + grouped[menuName].menu_id + '">' +
                    '<div class="page-header">' +
                        '<span><i class="bi bi-file-earmark-text me-2"></i>' + menuName + '</span>' +
                        '<span class="badge bg-light text-dark">' + selectedInPage + ' / ' + grouped[menuName].cards.length + ' selected</span>' +
                    '</div>' +
                    '<div class="page-body"><div class="card-grid"></div></div>' +
                '</div>');

                const grid = section.find('.card-grid');
                
                grouped[menuName].cards.forEach(function(card) {
                    const isSelected = selectedCards.has(card.card_id.toString());
                    const cardEl = $('<div class="selectable-card ' + (isSelected ? 'selected' : '') + '" ' +
                        'data-card-id="' + card.card_id + '" ' +
                        'data-category="' + card.card_category + '" ' +
                        'data-menu-id="' + card.menu_id + '">' +
                        '<div class="card-check"><i class="bi bi-check"></i></div>' +
                        '<div class="card-icon-wrapper ' + (card.card_color || 'primary') + '">' +
                            '<i class="bi ' + (card.card_icon || 'bi-card-text') + '"></i>' +
                        '</div>' +
                        '<div class="card-title-text">' + card.card_title + '</div>' +
                        '<div class="card-subtitle-text">' + (card.card_subtitle || '') + '</div>' +
                        '<input type="hidden" name="card_ids[]" value="' + card.card_id + '" ' + (isSelected ? '' : 'disabled') + '>' +
                    '</div>');
                    grid.append(cardEl);
                });

                container.append(section);
            });

            updateStats();
        }

        // Render cards grouped by category
        function renderCardsByCategory(cards) {
            const container = $('#cardContainer');
            container.empty();

            if (!cards || cards.length === 0) {
                container.html('<div class="text-center text-muted py-5"><i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i><p class="mt-3">No dashboard cards found</p></div>');
                return;
            }

            // Group by category
            const grouped = {};
            cards.forEach(function(card) {
                const cat = card.card_category || 'general';
                if (categoryFilter !== 'all' && cat !== categoryFilter) return;
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(card);
            });

            // Render each category
            Object.keys(grouped).sort().forEach(function(category) {
                const section = $('<div class="category-section" data-category="' + category + '">' +
                    '<div class="category-header ' + category + '">' +
                        '<span><i class="bi bi-folder me-2"></i>' + category.toUpperCase() + '</span>' +
                        '<span class="badge bg-white text-dark">' + grouped[category].length + ' cards</span>' +
                    '</div>' +
                    '<div class="card-grid"></div>' +
                '</div>');

                const grid = section.find('.card-grid');
                
                grouped[category].forEach(function(card) {
                    const isSelected = selectedCards.has(card.card_id.toString());
                    const cardEl = $('<div class="selectable-card ' + (isSelected ? 'selected' : '') + '" ' +
                        'data-card-id="' + card.card_id + '" ' +
                        'data-category="' + category + '" ' +
                        'data-menu-id="' + card.menu_id + '">' +
                        '<div class="card-check"><i class="bi bi-check"></i></div>' +
                        '<div class="card-icon-wrapper ' + (card.card_color || 'primary') + '">' +
                            '<i class="bi ' + (card.card_icon || 'bi-card-text') + '"></i>' +
                        '</div>' +
                        '<div class="card-title-text">' + card.card_title + '</div>' +
                        '<div class="card-subtitle-text">' + (card.card_subtitle || '') + '</div>' +
                        '<small class="badge bg-info mt-1">' + card.menu_name + '</small>' +
                        '<input type="hidden" name="card_ids[]" value="' + card.card_id + '" ' + (isSelected ? '' : 'disabled') + '>' +
                    '</div>');
                    grid.append(cardEl);
                });

                container.append(section);
            });

            updateStats();
        }

        // Render cards based on current grouping
        function renderCards(cards) {
            if (groupBy === 'page') {
                renderCardsByPage(cards);
            } else {
                renderCardsByCategory(cards);
            }
        }

        // Load cards for selected role
        function loadCards() {
            const roleId = $('#role_id').val();
            const menuId = $('#menu_filter').val();

            if (!roleId) {
                $('#cardContainer').html('<div class="text-center text-muted py-5" id="placeholder"><i class="bi bi-hand-index-thumb" style="font-size: 3rem; opacity: 0.5;"></i><p class="mt-3">Select a role to load dashboard cards</p></div>');
                $('#quickStats').hide();
                allCards = [];
                selectedCards.clear();
                updateStats();
                return;
            }

            // Show loading
            $('#cardContainer').html('<div class="loading-overlay"><div class="loading-spinner"></div></div>');

            const params = { role_id: roleId };
            if (menuId) params.menu_id = menuId;

            $.getJSON(APP_URL + 'roleDashboardCard/getMapping', params)
                .done(function(res) {
                    if (res.success) {
                        allCards = res.data;
                        selectedCards.clear();
                        
                        // Mark already mapped cards as selected
                        res.data.forEach(function(card) {
                            if (card.is_mapped == 1) {
                                selectedCards.add(card.card_id.toString());
                            }
                        });
                        
                        $('#quickStats').show();
                        renderCards(allCards);
                    } else {
                        showToast(res.message || 'Failed to load cards', 'error');
                    }
                })
                .fail(function() {
                    showToast('Error loading cards', 'error');
                });
        }

        // Role selection change
        $('#role_id').change(function() {
            const roleId = $(this).val();
            $('#targetRoleCopy').val(roleId);
            loadCards();
        });

        // Menu filter change
        $('#menu_filter').change(function() {
            loadCards();
        });

        // Group by toggle
        $(document).on('click', '.group-toggle .btn', function() {
            $('.group-toggle .btn').removeClass('active');
            $(this).addClass('active');
            groupBy = $(this).data('group');
            renderCards(allCards);
        });

        // Category filter
        $(document).on('click', '.filter-btn', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            categoryFilter = $(this).data('category');
            renderCards(allCards);
        });

        // Card click handler
        $(document).on('click', '.selectable-card', function() {
            const cardId = $(this).data('card-id').toString();
            const input = $(this).find('input');
            
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                selectedCards.delete(cardId);
                input.prop('disabled', true);
            } else {
                $(this).addClass('selected');
                selectedCards.add(cardId);
                input.prop('disabled', false);
            }
            
            updateStats();
            
            // Update page header count if grouped by page
            if (groupBy === 'page') {
                renderCards(allCards);
            }
        });

        // Select All
        $('#selectAll').click(function() {
            $('.selectable-card:visible').each(function() {
                const cardId = $(this).data('card-id').toString();
                $(this).addClass('selected');
                $(this).find('input').prop('disabled', false);
                selectedCards.add(cardId);
            });
            updateStats();
            if (groupBy === 'page') renderCards(allCards);
        });

        // Deselect All
        $('#deselectAll').click(function() {
            $('.selectable-card:visible').each(function() {
                const cardId = $(this).data('card-id').toString();
                $(this).removeClass('selected');
                $(this).find('input').prop('disabled', true);
                selectedCards.delete(cardId);
            });
            updateStats();
            if (groupBy === 'page') renderCards(allCards);
        });

        // Invert Selection
        $('#invertSelection').click(function() {
            $('.selectable-card:visible').each(function() {
                const cardId = $(this).data('card-id').toString();
                const input = $(this).find('input');
                
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    selectedCards.delete(cardId);
                    input.prop('disabled', true);
                } else {
                    $(this).addClass('selected');
                    selectedCards.add(cardId);
                    input.prop('disabled', false);
                }
            });
            updateStats();
            if (groupBy === 'page') renderCards(allCards);
        });

        // Reset button
        $('#resetBtn').click(function() {
            loadCards();
        });

        // Save form
        $('#cardMappingForm').submit(function(e) {
            e.preventDefault();
            
            const roleId = $('#role_id').val();
            const menuId = $('#menu_filter').val();
            
            if (!roleId) {
                showToast('Please select a role first!', 'error');
                return;
            }

            const cardIds = Array.from(selectedCards);
            
            const data = { 
                role_id: roleId, 
                card_ids: cardIds 
            };
            
            if (menuId) {
                data.menu_id = menuId;
            }
            
            $.ajax({
                url: APP_URL + 'roleDashboardCard/saveMapping',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, 'success');
                    } else {
                        showToast(res.message || 'Error saving', 'error');
                    }
                },
                error: function() {
                    showToast('Error saving mapping', 'error');
                }
            });
        });

        // Copy mapping
        $('#confirmCopy').click(function() {
            const sourceRole = $('#sourceRole').val();
            const targetRole = $('#targetRoleCopy').val() || $('#role_id').val();
            const menuId = $('#copyMenuFilter').val();
            
            if (!sourceRole) {
                showToast('Please select source role', 'error');
                return;
            }
            
            if (!targetRole) {
                showToast('Please select target role', 'error');
                return;
            }

            const data = { 
                source_role_id: sourceRole, 
                target_role_id: targetRole 
            };
            
            if (menuId) {
                data.menu_id = menuId;
            }

            $.ajax({
                url: APP_URL + 'roleDashboardCard/copyMapping',
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        showToast(res.message, 'success');
                        $('#copyModal').modal('hide');
                        if (targetRole == $('#role_id').val()) {
                            loadCards();
                        }
                    } else {
                        showToast(res.message || 'Error copying', 'error');
                    }
                },
                error: function() {
                    showToast('Error copying mapping', 'error');
                }
            });
        });
    });
}
</script>