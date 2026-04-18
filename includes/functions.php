<?php
/**
 * Vitanova — Fonctions utilitaires
 * formatPrice, sanitize, showError, showSuccess, productSvg, etc.
 */

// ============================================================
// Formatage
// ============================================================

function formatPrice(float $price): string
{
    return number_format($price, 3, ',', ' ') . ' TND';
}

function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// ============================================================
// Affichage des alertes HTML
// ============================================================

function showError(string $message): string
{
    return '<div class="alert alert--error" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>' . sanitize($message) . '</span>
    </div>';
}

function showSuccess(string $message): string
{
    return '<div class="alert alert--success" role="alert">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        <span>' . sanitize($message) . '</span>
    </div>';
}

function showFieldError(string $message): string
{
    return '<span class="field-error">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        ' . sanitize($message) . '
    </span>';
}

// ============================================================
// SVG Placeholder produit
// ============================================================

function productSvgPlaceholder(string $name, string $category = ''): string
{
    $colors = [
        'stress'    => ['#0f6e56', '#e1f5ee', '#5dcaa5'],
        'sommeil'   => ['#1e3a5f', '#e8f0fe', '#4a80c4'],
        'energie'   => ['#7c4d1a', '#fff3e0', '#e8a24d'],
        'bien-etre' => ['#5a2d82', '#f3e8ff', '#9c6bc4'],
    ];

    $c = $colors[$category] ?? ['#0f6e56', '#e1f5ee', '#5dcaa5'];
    $initial = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');

    $catLabels = [
        'stress'    => 'STRESS',
        'sommeil'   => 'SOMMEIL',
        'energie'   => 'ÉNERGIE',
        'bien-etre' => 'BIEN-ÊTRE',
    ];
    $catLabel = $catLabels[$category] ?? 'BIO';

    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 300" width="300" height="300" role="img" aria-label="' . htmlspecialchars($name) . '">
        <defs>
            <linearGradient id="g_' . md5($name) . '" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:' . $c[1] . ';stop-opacity:1"/>
                <stop offset="100%" style="stop-color:' . $c[2] . ';stop-opacity:0.3"/>
            </linearGradient>
        </defs>
        <!-- Fond -->
        <rect width="300" height="300" fill="url(#g_' . md5($name) . ')" rx="12"/>
        <!-- Bouteille corps -->
        <rect x="110" y="80" width="80" height="140" rx="10" fill="' . $c[0] . '" opacity="0.15"/>
        <rect x="115" y="85" width="70" height="130" rx="8" fill="white" opacity="0.7"/>
        <!-- Bouteille bouchon -->
        <rect x="122" y="60" width="56" height="28" rx="6" fill="' . $c[0] . '"/>
        <!-- Etiquette -->
        <rect x="120" y="110" width="60" height="70" rx="4" fill="' . $c[0] . '" opacity="0.9"/>
        <!-- Initiale -->
        <text x="150" y="152" font-family="Inter,Arial,sans-serif" font-size="28" font-weight="700"
              text-anchor="middle" fill="white">' . $initial . '</text>
        <!-- Ligne décorative -->
        <line x1="128" y1="165" x2="172" y2="165" stroke="white" stroke-width="1" opacity="0.5"/>
        <!-- Label catégorie -->
        <text x="150" y="180" font-family="Inter,Arial,sans-serif" font-size="7" font-weight="600"
              text-anchor="middle" fill="white" letter-spacing="1" opacity="0.8">' . $catLabel . '</text>
        <!-- Nom produit en bas -->
        <text x="150" y="250" font-family="Inter,Arial,sans-serif" font-size="11" font-weight="600"
              text-anchor="middle" fill="' . $c[0] . '" opacity="0.8">' . htmlspecialchars(mb_strtoupper($name, 'UTF-8')) . '</text>
        <!-- Pastilles décoratives -->
        <circle cx="75" cy="75" r="30" fill="' . $c[2] . '" opacity="0.15"/>
        <circle cx="225" cy="225" r="40" fill="' . $c[0] . '" opacity="0.08"/>
        <circle cx="240" cy="60" r="20" fill="' . $c[2] . '" opacity="0.1"/>
    </svg>';
}

function productSvgDataUrl(string $name, string $category = ''): string
{
    $svg = productSvgPlaceholder($name, $category);
    return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($svg);
}

// ============================================================
// Étoiles de notation
// ============================================================

function renderStars(float $rating, bool $interactive = false, string $name = 'rating'): string
{
    $html = '<div class="stars" ' . ($interactive ? 'data-interactive="true"' : '') . '>';
    for ($i = 1; $i <= 5; $i++) {
        if ($interactive) {
            $html .= '<label class="star-label" title="' . $i . ' étoile' . ($i > 1 ? 's' : '') . '">
                <input type="radio" name="' . htmlspecialchars($name) . '" value="' . $i . '" class="star-input">
                <svg class="star-icon" viewBox="0 0 24 24" width="24" height="24">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                             fill="' . ($i <= $rating ? '#f59e0b' : '#e5e7eb') . '" stroke="#f59e0b" stroke-width="1"/>
                </svg>
            </label>';
        } else {
            $html .= '<svg class="star-icon" viewBox="0 0 24 24" width="16" height="16">
                <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                         fill="' . ($i <= $rating ? '#f59e0b' : '#e5e7eb') . '" stroke="#f59e0b" stroke-width="1"/>
            </svg>';
        }
    }
    $html .= '</div>';
    return $html;
}

// ============================================================
// Pagination
// ============================================================

function paginate(int $total, int $perPage, int $currentPage, string $urlPattern): string
{
    $totalPages = (int)ceil($total / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<nav class="pagination" aria-label="Pagination">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $url    = str_replace('{page}', $i, $urlPattern);
        $html  .= '<a href="' . htmlspecialchars($url) . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

// ============================================================
// Catégorie — libellé français
// ============================================================

function categoryLabel(string $cat): string
{
    $labels = [
        'stress'    => 'Stress & Anxiété',
        'sommeil'   => 'Sommeil',
        'energie'   => 'Énergie & Focus',
        'bien-etre' => 'Bien-être',
    ];
    return $labels[$cat] ?? ucfirst($cat);
}

function categoryIcon(string $cat): string
{
    $icons = [
        'stress'    => '🧘',
        'sommeil'   => '🌙',
        'energie'   => '⚡',
        'bien-etre' => '🌿',
    ];
    return $icons[$cat] ?? '💊';
}

// ============================================================
// Status commande — libellé et badge
// ============================================================

function orderStatusLabel(string $status): string
{
    $labels = [
        'en_attente' => 'En attente',
        'confirmee'  => 'Confirmée',
        'expediee'   => 'Expédiée',
        'livree'     => 'Livrée',
    ];
    return $labels[$status] ?? ucfirst($status);
}

function orderStatusBadge(string $status): string
{
    $classes = [
        'en_attente' => 'badge--amber',
        'confirmee'  => 'badge--blue',
        'expediee'   => 'badge--purple',
        'livree'     => 'badge--green',
    ];
    $class = $classes[$status] ?? 'badge--default';
    return '<span class="badge ' . $class . '">' . orderStatusLabel($status) . '</span>';
}

// ============================================================
// JSON helpers (pour les APIs)
// ============================================================

function jsonResponse(bool $success, string $message, array $data = [], int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}
