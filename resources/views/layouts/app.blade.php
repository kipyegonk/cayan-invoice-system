<!DOCTYPE html>
<html>
<head>
<title>@yield('title', 'Invoices')</title>
<style>
body{font-family:sans-serif;background:#f6f7f9;margin:0}
header{background:#1a1a1a;color:#fff;padding:16px 24px}
header a{color:#fff;text-decoration:none;font-weight:600}
main{max-width:900px;margin:24px auto;padding:0 16px}
.card{background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);padding:24px;margin-bottom:20px}
table{width:100%;border-collapse:collapse}
th{text-align:left;font-size:11px;text-transform:uppercase;color:#666;padding:8px;border-bottom:2px solid #eee}
td{padding:10px 8px;border-bottom:1px solid #f0f0f0}
a.row-link{color:#1a1a1a;text-decoration:none;font-weight:500}
.status{display:inline-block;padding:3px 10px;border-radius:3px;font-size:11px;font-weight:600}
.status.unpaid{background:#fde2e2;color:#b42318}
.status.paid{background:#d1fae5;color:#027a48}
.btn{display:inline-block;background:#1a1a1a;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-size:14px}
.totals{width:260px;margin-left:auto;margin-top:16px}
.totals td{border:none;padding:4px 8px}
.totals .grand{font-weight:700;border-top:2px solid #333}
</style>
</head>
<body>
<header><a href="/invoices">Invoices</a></header>
<main>@yield('content')</main>
</body>
</html>