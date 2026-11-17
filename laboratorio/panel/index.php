<?php
// Panel de monitoreo: lee el log de auditoría (JSON, una línea por evento) del WAF.
$tipos = ['942' => 'SQL Injection', '941' => 'XSS', '930' => 'Path Traversal / LFI',
          '932' => 'Inyección de comandos', '913' => 'Escáner automatizado', '933' => 'Inyección PHP', '920' => 'Protocolo HTTP'];
$eventos = []; $conteo = [];
foreach (@file('/logs/audit.log') ?: [] as $linea) {
    $e = json_decode($linea, true); if (!$e) continue;
    $reglas = []; $peso = []; $score = '';
    foreach ($e['audit_data']['messages'] ?? [] as $m) {
        preg_match('/\[id "(\d+)"\].*?\[msg "([^"]*)"\]/', $m, $r); if (!$r) continue;
        if (preg_match('/Total Score: (\d+)/', $r[2], $s)) $score = $s[1];
        $fam = substr($r[1], 0, 3);
        if (!isset($tipos[$fam]) || $r[1] === '920350') continue;   // 920350 = Host con IP (ruido del laboratorio)
        $reglas[] = "$r[1] / $r[2]";
        if ($fam !== '920') $peso[$fam] = ($peso[$fam] ?? 0) + 1000 + $fam;  // familia con más reglas gana; empate -> id mayor
    }
    if (!$reglas) continue;
    $tipo = $tipos[$peso ? array_search(max($peso), $peso) : '920'];
    $bloq = !empty($e['audit_data']['action']['intercepted']);
    $conteo[$tipo] = ($conteo[$tipo] ?? 0) + 1;
    $eventos[] = ['hora' => substr($e['transaction']['time'], 12, 8), 'ip' => $e['transaction']['remote_address'], 'ua' => explode('/', $e['request']['headers']['User-Agent'] ?? '?')[0],
        'url' => urldecode(explode(' ', $e['request']['request_line'])[1] ?? ''), 'tipo' => $tipo,
        'reglas' => $reglas, 'score' => $score, 'bloq' => $bloq];
}
$eventos = array_slice(array_reverse($eventos), 0, 40);
$h = fn($s) => htmlspecialchars($s, ENT_QUOTES);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta http-equiv="refresh" content="3">
<title>Monitoreo WAF</title><style>
 body{font-family:system-ui,sans-serif;margin:0;background:#0f1420;color:#e6e9f0}
 header{padding:16px 24px;background:#161d2e;border-bottom:1px solid #263049} h1{margin:0;font-size:20px}
 .kpis{display:flex;gap:12px;flex-wrap:wrap;padding:16px 24px} .kpi{background:#161d2e;border:1px solid #263049;border-radius:8px;padding:10px 16px}
 .kpi b{display:block;font-size:26px} table{width:calc(100% - 48px);margin:0 24px 24px;border-collapse:collapse;font-size:13px}
 th,td{padding:8px;border-bottom:1px solid #263049;text-align:left;vertical-align:top} th{color:#8b95ad}
 .b{background:#5b1a22;color:#ffb3bb;padding:2px 8px;border-radius:4px;font-weight:600} .d{background:#4d4214;color:#ffe08a;padding:2px 8px;border-radius:4px;font-weight:600}
 code{color:#9ecbff;word-break:break-all} small{color:#8b95ad}
</style></head><body>
<header><h1>Monitoreo del WAF con ModSecurity + OWASP CRS</h1><small>Se actualiza cada 3 s. <?= count($eventos) ?> eventos recientes</small></header>
<div class="kpis"><div class="kpi"><small>Total</small><b><?= array_sum($conteo) ?></b></div>
<?php foreach ($conteo as $t => $n): ?><div class="kpi"><small><?= $h($t) ?></small><b><?= $n ?></b></div><?php endforeach ?></div>
<table><tr><th>Hora (UTC)</th><th>IP origen*</th><th>Herramienta</th><th>Ataque</th><th>Petición</th><th>Reglas OWASP activadas</th><th>Puntaje</th><th>Acción</th></tr>
<?php foreach ($eventos as $e): ?><tr><td><?= $e['hora'] ?></td><td><?= $h($e['ip']) ?></td><td><?= $h($e['ua']) ?></td><td><?= $h($e['tipo']) ?></td>
<td><code><?= $h($e['url']) ?></code></td><td><?= implode('<br>', array_map($h, $e['reglas'])) ?></td><td><?= $e['score'] ?></td>
<td><?= $e['bloq'] ? '<span class="b">BLOQUEADO 403</span>' : '<span class="d">SOLO DETECTADO</span>' ?></td></tr><?php endforeach ?>
</table><p style="margin:0 24px 24px"><small>* En Docker Desktop (Mac) la IP de origen aparece como la puerta de enlace de Docker; en un servidor Linux se vería la IP real del atacante.</small></p></body></html>
