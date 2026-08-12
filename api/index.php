<?php
require __DIR__.'/../config.php';
$route = trim($_GET['route'] ?? '', '/');
$method = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?: [];
if ($route === 'health') json_out(['ok'=>true,'service'=>'rentflow']);
try {
  $pdo = db();
  if ($route === 'vehicles/available' && $method === 'GET') {
    $start=$_GET['start']??''; $end=$_GET['end']??''; $category=$_GET['category']??'';
    $sql="SELECT v.* FROM vehicles v WHERE v.active=1 AND (?='' OR v.category=?) AND NOT EXISTS (SELECT 1 FROM bookings b WHERE b.vehicle_id=v.id AND b.status IN ('pending_owner','confirmed') AND b.pickup_date < ? AND b.return_date > ?)";
    $s=$pdo->prepare($sql); $s->execute([$category,$category,$end,$start]); json_out($s->fetchAll(PDO::FETCH_ASSOC));
  }
  if ($route === 'bookings' && $method === 'POST') {
    foreach(['customer_name','phone','vehicle_id','pickup_date','return_date'] as $k) if(empty($body[$k])) json_out(['error'=>"missing_$k"],422);
    $s=$pdo->prepare('SELECT daily_rate FROM vehicles WHERE id=? AND active=1'); $s->execute([$body['vehicle_id']]); $v=$s->fetch(); if(!$v) json_out(['error'=>'vehicle_not_found'],404);
    $days=max(1,(new DateTime($body['pickup_date']))->diff(new DateTime($body['return_date']))->days); $total=$days*$v['daily_rate'];
    $s=$pdo->prepare("INSERT INTO bookings(customer_name,phone,vehicle_id,pickup_date,return_date,pickup_location,total,status) VALUES(?,?,?,?,?,?,?,'pending_owner')");
    $s->execute([$body['customer_name'],$body['phone'],$body['vehicle_id'],$body['pickup_date'],$body['return_date'],$body['pickup_location']??'Marrakech',$total]); json_out(['id'=>$pdo->lastInsertId(),'total_mad'=>$total,'status'=>'pending_owner'],201);
  }
  if (preg_match('#^bookings/(\\d+)/status$#',$route,$m) && $method === 'PATCH') {
    if(!in_array($body['status']??'', ['confirmed','rejected','cancelled'], true)) json_out(['error'=>'invalid_status'],422);
    $s=$pdo->prepare('UPDATE bookings SET status=? WHERE id=?'); $s->execute([$body['status'],$m[1]]); json_out(['ok'=>true]);
  }
  if ($route === 'dashboard/stats') {
    $stats=$pdo->query("SELECT COUNT(*) bookings, COALESCE(SUM(total),0) potential_revenue, COALESCE(SUM(CASE WHEN status='confirmed' THEN total ELSE 0 END),0) confirmed_revenue FROM bookings")->fetch(PDO::FETCH_ASSOC);
    $latest=$pdo->query('SELECT b.*,v.make,v.model FROM bookings b JOIN vehicles v ON v.id=b.vehicle_id ORDER BY b.id DESC LIMIT 20')->fetchAll(PDO::FETCH_ASSOC); json_out(['stats'=>$stats,'latest'=>$latest]);
  }
  json_out(['error'=>'not_found'],404);
} catch(Throwable $e) { json_out(['error'=>'service_unavailable'],503); }

