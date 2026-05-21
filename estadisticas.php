<?php require_once 'config/conexion.php'; include 'includes/header.php'; include 'includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <h1 class="display-6 fw-bold mb-4"><i class="fas fa-chart-bar text-primary"></i> Estadísticas</h1>
        
        <div class="row g-4 mb-4">
            <div class="col-md-4"><div class="stat-card"><canvas id="categoriasChart" height="300"></canvas></div></div>
            <div class="col-md-4"><div class="stat-card"><canvas id="prioridadChart" height="300"></canvas></div></div>
            <div class="col-md-4"><div class="stat-card"><canvas id="estadoChart" height="300"></canvas></div></div>
        </div>
        
        <div class="row">
            <div class="col-md-6"><div class="stat-card"><canvas id="tendenciaChart" height="250"></canvas></div></div>
            <div class="col-md-6"><div class="stat-card"><h5 class="mb-3">Resumen General</h5><?php $total=$pdo->query("SELECT COUNT(*) FROM reports")->fetch()['COUNT(*)']; $resueltos=$pdo->query("SELECT COUNT(*) FROM reports WHERE estado='resuelto'")->fetch()['COUNT(*)']; $tiempo=$pdo->query("SELECT AVG(DATEDIFF(fecha_resuelto,fecha_reporte)) as avg FROM reports WHERE fecha_resuelto IS NOT NULL")->fetch()['avg'];?><div class="text-center"><h2 class="text-primary"><?php echo $total; ?></h2><p>Total Reportes</p><hr><h2 class="text-success"><?php echo $resueltos; ?></h2><p>Resueltos (<?php echo $total>0?round(($resueltos/$total)*100):0;?>%)</p><hr><h2 class="text-info"><?php echo round($tiempo?:0,1); ?></h2><p>Días promedio de resolución</p></div></div></div>
        </div>
    </div>
</div>

<script>
<?php
$cats=$pdo->query("SELECT categoria,COUNT(*) as t FROM reports GROUP BY categoria")->fetchAll();$catData=[];
$prios=$pdo->query("SELECT prioridad,COUNT(*) as t FROM reports GROUP BY prioridad")->fetchAll();
$estados=$pdo->query("SELECT estado,COUNT(*) as t FROM reports GROUP BY estado")->fetchAll();
$fechas=$pdo->query("SELECT DATE(fecha_reporte) as fecha,COUNT(*) as total FROM reports GROUP BY DATE(fecha_reporte) ORDER BY fecha DESC LIMIT 7")->fetchAll();
?>
new Chart(document.getElementById('categoriasChart'),{type:'pie',data:{labels:[<?php foreach($cats as $c) echo "'".$c['categoria']."',"; ?>], datasets:[{data:[<?php foreach($cats as $c) echo $c['t'].","; ?>], backgroundColor:['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6']}]}});
new Chart(document.getElementById('prioridadChart'),{type:'doughnut',data:{labels:[<?php foreach($prios as $p) echo "'".$p['prioridad']."',"; ?>], datasets:[{data:[<?php foreach($prios as $p) echo $p['t'].","; ?>], backgroundColor:['#ef4444','#f59e0b','#10b981']}]}});
new Chart(document.getElementById('estadoChart'),{type:'bar',data:{labels:[<?php foreach($estados as $e) echo "'".$e['estado']."',"; ?>], datasets:[{label:'Reportes',data:[<?php foreach($estados as $e) echo $e['t'].","; ?>], backgroundColor:['#f59e0b','#3b82f6','#10b981']}]}});
new Chart(document.getElementById('tendenciaChart'),{type:'line',data:{labels:[<?php foreach(array_reverse($fechas) as $f) echo "'".$f['fecha']."',"; ?>], datasets:[{label:'Reportes diarios',data:[<?php foreach(array_reverse($fechas) as $f) echo $f['total'].","; ?>], borderColor:'#6366f1',tension:0.4}]}});
</script>

<?php include 'includes/footer.php'; ?>