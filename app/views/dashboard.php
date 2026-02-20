<?php
/**
 * Dynamic Dashboard View
 * Cards are rendered based on role_dashboard_card_mapping_t
 * 
 * Required variables from controller:
 * - $cards: Array of card objects from Dashboard::getCardsByRole()
 * - $cardData: Array of card data from Dashboard::getCardData()
 */

// URL mapping: card_key to actual routes
$urlMap = [
    'total_users'       => 'user/index',
    'system_settings'   => 'settings/index',
];

// Color to gradient mapping
$colorGradients = [
    'primary' => 'linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%)',
    'success' => 'linear-gradient(135deg, #059669 0%, #10b981 100%)',
    'purple'  => 'linear-gradient(135deg, #7c3aed 0%, #a855f7 100%)',
    'teal'    => 'linear-gradient(135deg, #0891b2 0%, #06b6d4 100%)',
    'warning' => 'linear-gradient(135deg, #d97706 0%, #f59e0b 100%)',
    'danger'  => 'linear-gradient(135deg, #dc2626 0%, #ef4444 100%)',
    'pink'    => 'linear-gradient(135deg, #db2777 0%, #ec4899 100%)',
    'info'    => 'linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%)',
    'orange'  => 'linear-gradient(135deg, #ea580c 0%, #f97316 100%)',
    'indigo'  => 'linear-gradient(135deg, #4f46e5 0%, #6366f1 100%)',
];
?>

<!-- ✅ Font Awesome for Icons - MUST BE LOADED -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* CSS Variables for Light and Dark Mode */
    :root, [data-bs-theme="light"] {
        --bg-primary: #f8fafc;
        --bg-card: #ffffff;
        --text-title: #64748b;
        --text-number: #1e293b;
        --text-subtitle: #10b981;
        --border-color: rgba(0, 0, 0, 0.08);
        --shadow-color: rgba(0, 0, 0, 0.08);
        --card-hover-shadow: rgba(0, 0, 0, 0.12);
    }

    [data-bs-theme="dark"] {
        --bg-primary: #0f172a;
        --bg-card: #1e293b;
        --text-title: #94a3b8;
        --text-number: #f1f5f9;
        --text-subtitle: #34d399;
        --border-color: rgba(255, 255, 255, 0.1);
        --shadow-color: rgba(0, 0, 0, 0.3);
        --card-hover-shadow: rgba(0, 0, 0, 0.5);
    }

    /* Premium Card Styling */
    .page-content {
        background: var(--bg-primary);
        padding: 20px;
        min-height: 100vh;
    }

    .page-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: 0 4px 20px var(--shadow-color);
        overflow: hidden;
        height: 100%;
        position: relative;
        padding: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px var(--card-hover-shadow);
    }

    .card-header {
        padding: 0;
        background: transparent;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .header-title {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-title);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .icon-wrapper {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .card-body {
        padding: 0;
    }

    .card-number {
        font-size: 48px;
        font-weight: 700;
        color: var(--text-number);
        line-height: 1;
        margin-bottom: 8px;
        letter-spacing: -1px;
    }

    .card-subtitle {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-subtitle);
        margin: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .page-content {
            padding: 10px;
        }
        
        .dashboard-card {
            padding: 20px;
        }

        .card-number {
            font-size: 36px;
        }

        .icon-wrapper {
            width: 48px;
            height: 48px;
            font-size: 20px;
        }

        .header-title {
            font-size: 12px;
        }
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<div class="page-content">
    <div class="page-container">

        <?php if (empty($cards)): ?>
            <!-- No cards configured for this role -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No dashboard cards are configured for your role. Please contact the administrator.
            </div>
        <?php else: ?>

            <!-- Dynamic Cards Grid -->
            <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 g-4">
                
                <?php foreach ($cards as $card): 
                    // Get card data
                    $data = $cardData[$card->card_key] ?? ['total' => 0, 'active' => 0, 'label' => $card->card_subtitle];
                    // Get icon class (use Font Awesome from database or fallback)
                    $iconClass = $card->card_icon ?? 'fas fa-clipboard';
                    
                    // Get URL (use urlMap or card's own URL)
                    $cardUrl = $urlMap[$card->card_key] ?? ltrim($card->card_url ?? '#', '/');
                    
                    // Get gradient color
                    $gradient = $colorGradients[$card->card_color] ?? $colorGradients['primary'];
                ?>
                    
                    <div class="col">
                        <a href="<?= APP_URL . '/' . htmlspecialchars($cardUrl) ?>" class="text-decoration-none">
                            <div class="dashboard-card">
                                <div class="d-flex card-header justify-content-between align-items-center">
                                    <div>
                                        <h4 class="header-title"><?= htmlspecialchars($card->card_title) ?></h4>
                                    </div>
                                    <div class="icon-wrapper" style="background: <?= $gradient ?>;">
                                        <i class="<?= htmlspecialchars($iconClass) ?>"></i>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="card-number"><?= number_format($data['total']) ?></div>
                                    <p class="card-subtitle">
                                        <?= htmlspecialchars($data['label']) ?>: <?= number_format($data['active']) ?>
                                    </p>
                                </div>
                            </div>
                        </a>
                    </div>
                    
                <?php endforeach; ?>
                
            </div>

        <?php endif; ?>

    </div>
    
    <?php include(VIEW_PATH . 'layouts/partials/footer.php'); ?>

</div>