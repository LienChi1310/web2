<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('guha');
    session_start();
}

function cart_session_owner_key(?int $accountId = null): string
{
    $resolvedAccountId = (int)($accountId ?? ($_SESSION['account_id'] ?? 0));
    return $resolvedAccountId > 0 ? 'account:' . $resolvedAccountId : 'guest';
}

function cart_session_init(): void
{
    if (!isset($_SESSION['cart_by_owner']) || !is_array($_SESSION['cart_by_owner'])) {
        $_SESSION['cart_by_owner'] = [];
    }

    if (!isset($_SESSION['guest_cart']) || !is_array($_SESSION['guest_cart'])) {
        $_SESSION['guest_cart'] = [];
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (!isset($_SESSION['active_cart_owner']) || $_SESSION['active_cart_owner'] === '') {
        $_SESSION['active_cart_owner'] = cart_session_owner_key();
        if ($_SESSION['active_cart_owner'] === 'guest') {
            $_SESSION['cart'] = $_SESSION['guest_cart'];
        } else {
            $_SESSION['cart'] = $_SESSION['cart_by_owner'][$_SESSION['active_cart_owner']] ?? [];
        }
    }
}

function cart_session_save_active_cart(): void
{
    cart_session_init();

    $activeOwner = (string)($_SESSION['active_cart_owner'] ?? 'guest');
    $cart = (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) ? $_SESSION['cart'] : [];

    if ($activeOwner === 'guest') {
        $_SESSION['guest_cart'] = $cart;
        return;
    }

    $_SESSION['cart_by_owner'][$activeOwner] = $cart;
}

function cart_session_activate_owner(?int $accountId = null): void
{
    cart_session_init();

    $nextOwner = cart_session_owner_key($accountId);
    $currentOwner = (string)($_SESSION['active_cart_owner'] ?? 'guest');

    if ($currentOwner !== $nextOwner) {
        cart_session_save_active_cart();
        $_SESSION['active_cart_owner'] = $nextOwner;

        if ($nextOwner === 'guest') {
            $_SESSION['cart'] = $_SESSION['guest_cart'];
        } else {
            $_SESSION['cart'] = $_SESSION['cart_by_owner'][$nextOwner] ?? [];
        }

        return;
    }

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        if ($nextOwner === 'guest') {
            $_SESSION['cart'] = $_SESSION['guest_cart'];
        } else {
            $_SESSION['cart'] = $_SESSION['cart_by_owner'][$nextOwner] ?? [];
        }
    }
}