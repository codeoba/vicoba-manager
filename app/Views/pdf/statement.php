<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<title>Member Statement — <?= e($member->full_name) ?></title>
<style>
  body { font-family: 'Helvetica Neue', Arial, sans-serif; font-size: 13px; color: #1e293b; line-height: 1.5; margin: 0; padding: 20px; }
  .header { display: flex; justify-content: space-between; align-items: center; border-b: 2px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
  .logo { font-size: 22px; font-weight: 800; color: #1e3a5f; }
  .badge { background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: bold; border: 1px solid #bfdbfe; }
  .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
  .meta-item label { font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; display: block; }
  .meta-item span { font-weight: 700; font-size: 14px; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th { background: #1e3a5f; color: white; text-align: left; padding: 10px; font-size: 11px; text-transform: uppercase; }
  td { padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
  tr:nth-child(even) { background: #f8fafc; }
  .text-right { text-align: right; }
  .font-bold { font-weight: bold; }
  .text-emerald { color: #16a34a; }
  .text-red { color: #dc2626; }
  .footer { margin-top: 40px; padding-top: 15px; border-t: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #64748b; }
  .qr-box { border: 1px dashed #cbd5e1; padding: 10px; border-radius: 6px; text-align: center; font-family: monospace; font-size: 10px; background: #fff; }
  @media print { body { padding: 0; } .no-print { display: none; } }
</style>
</head>
<body>

<div class="header">
  <div>
    <div class="logo">🏛️ VICOBA Manager</div>
    <div style="font-size:12px; color:#64748b;">Taarifa Rasmi ya Mwanachama (Member Statement)</div>
  </div>
  <div style="text-align:right;">
    <span class="badge">OFISI NA HAKIKI</span>
    <div style="font-size:11px; color:#64748b; margin-top:5px;">Tarehe: <?= date('d/m/Y H:i') ?></div>
  </div>
</div>

<div class="meta-grid">
  <div class="meta-item"><label>Jina la Mwanachama</label><span><?= e($member->full_name) ?></span></div>
  <div class="meta-item"><label>Namba ya Uanachama</label><span><?= e($member->member_number) ?></span></div>
  <div class="meta-item"><label>Kikundi</label><span><?= e($group->name ?? 'VICOBA') ?></span></div>
  <div class="meta-item"><label>Hali ya Uanachama</label><span style="color:#16a34a;"><?= strtoupper($member->status) ?></span></div>
</div>

<h3>📊 Muhtasari wa Akiba na Mikopo</h3>
<table>
  <thead>
    <tr>
      <th>Tarehe</th>
      <th>Namba ya Muamala</th>
      <th>Aina ya Muamala</th>
      <th>Njia ya Malipo</th>
      <th class="text-right">Kiasi (<?= e($group->currency ?? 'TZS') ?>)</th>
    </tr>
  </thead>
  <tbody>
    <?php if (empty($transactions)): ?>
    <tr><td colspan="5" style="text-align:center; padding:20px; color:#94a3b8;">Hakuna miamala iliyorekodiwa.</td></tr>
    <?php else: foreach ($transactions as $t): ?>
    <tr>
      <td><?= format_date($t->created_at) ?></td>
      <td style="font-family:monospace;"><?= e($t->transaction_code) ?></td>
      <td class="font-bold"><?= e(str_replace('_', ' ', strtoupper($t->type))) ?></td>
      <td><?= e($t->payment_method) ?></td>
      <td class="text-right font-bold <?= in_array($t->type, ['loan_disbursement','expense']) ? 'text-red' : 'text-emerald' ?>">
        <?= in_array($t->type, ['loan_disbursement','expense']) ? '-' : '+' ?><?= money($t->amount) ?>
      </td>
    </tr>
    <?php endforeach; endif; ?>
  </tbody>
</table>

<div class="footer">
  <div>
    <strong>Thibitisho la Mfumo:</strong> Dokumenti hii imezalishwa kiotomatiki na VICOBA Manager.<br>
    Validation Code: <span style="font-family:monospace; font-weight:bold;"><?= strtoupper(md5($member->id . date('Ymd'))) ?></span>
  </div>
  <div class="qr-box">
    🔒 AUTHENTICATED<br>VICOBA-VERIFIED
  </div>
</div>

<div class="no-print" style="margin-top:20px; text-align:center;">
  <button onclick="window.print()" style="background:#2563eb; color:white; border:none; padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer;">🖨️ Chapisha / Hifadhi kama PDF</button>
</div>

</body>
</html>
