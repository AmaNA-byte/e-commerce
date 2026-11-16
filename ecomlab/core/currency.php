<?php
// Prices are stored in US dollars. Shoppers can view them in USD or Ghana cedis (GHS).
// Paystack charges in GHS, so checkout always converts the total to cedis.

const CURRENCIES = ['USD' => '$', 'GHS' => 'GHS '];
const FALLBACK_USD_TO_GHS = 11.72; // used only if the live rate cannot be fetched and nothing is cached
const RATE_CACHE_SECONDS = 6 * 3600; // fetch a fresh rate at most every 6 hours
const RATE_API = 'https://open.er-api.com/v6/latest/USD'; // free, no API key needed

// Remember the shopper's choice when they click USD / GHS (…?currency=GHS on any page).
if (isset($_GET['currency']) && isset(CURRENCIES[$_GET['currency']])) {
  $_SESSION['currency'] = $_GET['currency'];
}

function current_currency() {
  return $_SESSION['currency'] ?? 'USD';
}

// How many cedis one US dollar buys. Live rate, cached in a small file; falls back to the last
// cached rate (even if old) or FALLBACK_USD_TO_GHS if the rate service cannot be reached.
function usd_to_ghs_rate() {
  static $rate = null;
  if ($rate !== null) {
    return $rate;
  }
  $cache = sys_get_temp_dir() . '/shoppn_usd_ghs_rate.json';
  $cached = is_readable($cache) ? json_decode((string) file_get_contents($cache), true) : null;
  if ($cached && $cached['time'] > time() - RATE_CACHE_SECONDS) {
    return $rate = (float) $cached['rate'];
  }

  $ch = curl_init(RATE_API);
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_CONNECTTIMEOUT => 3]);
  $reply = json_decode((string) curl_exec($ch), true);
  curl_close($ch);
  $live = (float) ($reply['rates']['GHS'] ?? 0);

  if (($reply['result'] ?? '') === 'success' && $live > 1 && $live < 1000) {
    @file_put_contents($cache, json_encode(['rate' => $live, 'time' => time()]));
    return $rate = $live;
  }
  error_log('Could not fetch the USD to GHS rate; using ' . ($cached ? 'the last saved rate' : 'the fallback rate'));
  return $rate = $cached ? (float) $cached['rate'] : FALLBACK_USD_TO_GHS;
}

// A dollar amount in cedis, rounded to pesewas. Pass $rate to use a locked/saved rate.
function usd_to_ghs($usd, $rate = null) {
  return round($usd * ($rate ?? usd_to_ghs_rate()), 2);
}

// Formats a price stored in USD in the shopper's currency (or $currency), e.g. "$180.00" or "GHS 2,109.44".
function money($usd, $currency = null, $rate = null) {
  $currency = $currency ?? current_currency();
  $amount = $currency === 'GHS' ? usd_to_ghs($usd, $rate) : $usd;
  return CURRENCIES[$currency] . number_format($amount, 2);
}

// Formats an amount that is already in cedis.
function cedis($ghs) {
  return 'GHS ' . number_format($ghs, 2);
}

// Link to the current page with a different currency (keeps the other ?parameters).
function currency_link($currency) {
  return '?' . http_build_query(array_merge($_GET, ['currency' => $currency]));
}
