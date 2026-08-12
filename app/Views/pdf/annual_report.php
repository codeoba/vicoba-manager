<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<title>Ripoti ya Mwaka — <?= e($group->name) ?></title>
<style>
  body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px; color: #1e293b; line-height: 1.5; margin: 0; padding: 25px; }
  .header { border-b: 3px double #1e3a5f; padding-bottom: 15px; margin-bottom: 25px; text-align: center; }
  .title { font-size: 24px; font-weight: 800; color: #1e3a5f; text-transform: uppercase; margin-bottom: 5px; }
  .subtitle { font-size: 14px; color: #475569; font-weight: bold; }
  .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
  .stat-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; }
  .stat-card .val { font-size: 18px; font-weight: 800; color: #2563eb; margin-top: 5px; }
  .stat-card .lbl { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th { background: #1e3a5f; color: white; text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; }
  td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
  .text-right { text-align: right; }
  .font-bold { font-weight: bold; }
  .signature-section { margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; text-align: center; }
  .sig-line { border-top: 1px solid #94a3b8; margin-top: 40px; padding-top: 5px; font-size: 11px; font-weight: bold; color: #475569; }
  @media print { body { padding: 0; } .no-print { display: none; } }
</style>
</head>
<body>

<div class="header">
  <div class="title">🏛️ <?= e($group->name) ?></div>
  <div class="subtitle">RIPOTI RASMI YA KIFEDHA YA MWAKA (ANNUAL REGULATORY REPORT)</div>
  <div style="font-size:11px; color:#64748b; margin-top:5px;">Mkoa: <?= e($group->region ?? '—') ?> | Wilaya: <?= e($group->district ?? '—') ?> | Usajili: <?= e($group->registration_number ?? 'Hauna') ?></div>
</div>

<div class="stats-grid">
  <div class="stat-card"><div class="lbl">Wanachama Active</div><div class="val"><?= number_format($stats['member_count']) ?></div></div>
  <div class="stat-card"><div class="lbl">Jumla ya Hisa (Pool)</div><div class="val"><?= money($stats['total_shares']) ?></div></div>
  <div class="stat-card"><div class="lbl">Mikopo Inayodaiwa</div><div class="val"><?= money($stats['active_loans']) ?></div></div>
  <div class="stat-card"><div class="lbl">Faini Zilizolipwa</div><div class="val"><?= money($stats['fines_paid']) ?></div></div>
</div>

<h3>📊 Muhtasari wa Mizani (Financial Balance Sheet)</h3>
<table>
  <thead>
    <tr>
      <th>Aina ya Akaunti</th>
      <th>Maelezo</th>
      <th class="text-right">Kiasi (<?= e($group->currency ?? 'TZS') ?>)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="font-bold">Hisa za Wanachama</td>
      <td>Jumla ya mtaji wa hisa zilizochangwa</td>
      <td class="text-right font-bold"><?= money($stats['total_shares']) ?></td>
    </tr>
    <tr>
      <td class="font-bold">Mikopo Inayoendelea</td>
      <td>Salio la mikopo iliyotolewa inayorudishwa</td>
      <td class="text-right font-bold"><?= money($stats['active_loans']) ?></td>
    </tr>
    <tr>
      <td class="font-bold">Faini Hazijalipwa</td>
      <td>Faini zilizotolewa zinazosubiri malipo</td>
      <td class="text-right font-bold" style="color:#dc2626;"><?= money($stats['fines_pending']) ?></td>
    </tr>
    <tr>
      <td class="font-bold">Mikopo Iliyochelewa (NPL)</td>
      <td>Idadi ya mikopo iliyovuka tarehe ya mwisho</td>
      <td class="text-right font-bold" style="color:#dc2626;"><?= number_format($stats['overdue_loans']) ?> Loans</td>
    </tr>
  </tbody>
</table>

<div class="signature-section">
  <div>
    <div class="sig-line">Mwenyekiti (Chairman)</div>
  </div>
  <div>
    <div class="sig-line">Katibu (Secretary)</div>
  </div>
  <div>
    <div class="sig-line">Mweka Hazina (Treasurer)</div>
  </div>
</div>

<div class="no-print" style="margin-top:30px; text-align:center;">
  <button onclick="window.print()" style="background:#2563eb; color:white; border:none; padding:12px 24px; border-radius:8px; font-weight:bold; cursor:pointer;">🖨️ Chapisha / Hifadhi kama PDF</button>
</div>

</body>
</html>
