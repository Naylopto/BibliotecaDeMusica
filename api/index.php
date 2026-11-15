<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function out($data, int $status=200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); exit; }
function body(): array { return json_decode(file_get_contents('php://input'), true) ?: []; }
function userId(): int { if (empty($_SESSION['uid'])) out(['error'=>'Inicia sesión para continuar.'],401); return (int)$_SESSION['uid']; }
function cartId(PDO $db, int $uid): int {
    $q=$db->prepare("SELECT idcarrito FROM carrito WHERE idusuario=? AND estado='activo' ORDER BY idcarrito DESC LIMIT 1"); $q->execute([$uid]); $id=$q->fetchColumn();
    if (!$id) { $db->prepare("INSERT INTO carrito(idusuario) VALUES(?)")->execute([$uid]); $id=(int)$db->lastInsertId(); }
    return (int)$id;
}
try {
    $db=db(); $method=$_SERVER['REQUEST_METHOD']; $action=$_GET['action'] ?? '';
    if ($method==='GET' && $action==='products') {
        $sql="SELECT p.idproducto,p.sku,p.nombre,p.descripcion,p.precio,p.stock,c.nombre categoria,
          COALESCE(i.url_imagen,'') imagen,u.nombre vendedor
          FROM productos p JOIN categorias c ON c.idcategoria=p.idcategoria JOIN usuarios u ON u.idusuario=p.id_vendedor
          LEFT JOIN imagenes_prod i ON i.idproducto=p.idproducto
          WHERE p.estado='activo' AND p.stock>0"; $args=[];
        if (!empty($_GET['categoria'])) { $sql.=' AND p.idcategoria=?'; $args[]=(int)$_GET['categoria']; }
        if (!empty($_GET['q'])) { $sql.=' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)'; $term='%'.trim($_GET['q']).'%'; $args[]=$term; $args[]=$term; }
        $sql.=' ORDER BY p.fecha_reg DESC'; $q=$db->prepare($sql); $q->execute($args); out(['products'=>$q->fetchAll()]);
    }
    if ($method==='GET' && $action==='categories') out(['categories'=>$db->query('SELECT idcategoria,nombre FROM categorias ORDER BY nombre')->fetchAll()]);
    if ($method==='GET' && $action==='me') out(['user'=>isset($_SESSION['uid'])?['id'=>$_SESSION['uid'],'nombre'=>$_SESSION['name'],'rol'=>$_SESSION['role']]:null]);
    if ($method==='POST' && $action==='register') {
        $b=body(); $email=strtolower(trim($b['correo']??'')); $name=trim($b['nombre']??''); $password=(string)($b['password']??'');
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)||$name===''||strlen($password)<8) out(['error'=>'Indica nombre, correo válido y contraseña de al menos 8 caracteres.'],422);
        $role=($b['rol']??'')==='vendedor'?'vendedor':'comprador';
        $q=$db->prepare('INSERT INTO usuarios(nombre,correo,passw,rol) VALUES(?,?,?,?)'); $q->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role]);
        session_regenerate_id(true); $_SESSION['uid']=(int)$db->lastInsertId(); $_SESSION['name']=$name; $_SESSION['role']=$role; out(['user'=>['nombre'=>$name,'rol'=>$role]],201);
    }
    if ($method==='POST' && $action==='login') {
        $b=body(); $q=$db->prepare('SELECT idusuario,nombre,passw,rol FROM usuarios WHERE correo=?'); $q->execute([strtolower(trim($b['correo']??''))]); $u=$q->fetch();
        if (!$u||!password_verify((string)($b['password']??''),$u['passw'])) out(['error'=>'Correo o contraseña incorrectos.'],401);
        session_regenerate_id(true); $_SESSION['uid']=(int)$u['idusuario']; $_SESSION['name']=$u['nombre']; $_SESSION['role']=$u['rol']; out(['user'=>['nombre'=>$u['nombre'],'rol'=>$u['rol']]]);
    }
    if ($method==='POST' && $action==='logout') { $_SESSION=[]; session_destroy(); out(['ok'=>true]); }
    if ($method==='POST' && $action==='products') {
        $uid=userId(); if (($_SESSION['role']??'')!=='vendedor'&&($_SESSION['role']??'')!=='admin') out(['error'=>'La cuenta debe tener rol vendedor.'],403);
        $b=body(); $name=trim($b['nombre']??''); $price=(float)($b['precio']??0); $stock=(int)($b['stock']??0); $cat=(int)($b['idcategoria']??0);
        if ($name===''||$price<=0||$stock<0||$cat<1) out(['error'=>'Completa nombre, categoría, precio y existencia válidos.'],422);
        $sku='INS-'.strtoupper(bin2hex(random_bytes(5))); $db->beginTransaction();
        $db->prepare("INSERT INTO productos(idcategoria,id_vendedor,sku,nombre,descripcion,precio,stock,estado) VALUES(?,?,?,?,?,?,?,'activo')")->execute([$cat,$uid,$sku,$name,trim($b['descripcion']??''),$price,$stock]); $pid=(int)$db->lastInsertId();
        if (!empty($b['imagen'])&&filter_var($b['imagen'],FILTER_VALIDATE_URL)) $db->prepare('INSERT INTO imagenes_prod(idproducto,url_imagen,texto_alt) VALUES(?,?,?)')->execute([$pid,$b['imagen'],$name]);
        $db->commit(); out(['ok'=>true,'idproducto'=>$pid],201);
    }
    if ($method==='GET' && $action==='cart') {
        $uid=userId(); $cid=cartId($db,$uid); $q=$db->prepare("SELECT d.idproducto,p.nombre,d.precio_ven precio,d.cantidad,p.stock,c.nombre categoria,COALESCE((SELECT i.url_imagen FROM imagenes_prod i WHERE i.idproducto=p.idproducto ORDER BY i.idimagen LIMIT 1),'') imagen FROM carrito_detalle d JOIN productos p ON p.idproducto=d.idproducto JOIN categorias c ON c.idcategoria=p.idcategoria WHERE d.idcarrito=? ORDER BY d.idcarritodet DESC"); $q->execute([$cid]); out(['items'=>$q->fetchAll()]);
    }
    if ($method==='POST' && $action==='cart') {
        $uid=userId(); $b=body(); $pid=(int)($b['idproducto']??0); $qty=max(1,min(99,(int)($b['cantidad']??1))); $cid=cartId($db,$uid);
        $q=$db->prepare("SELECT precio,stock FROM productos WHERE idproducto=? AND estado='activo'");$q->execute([$pid]);$p=$q->fetch(); if(!$p)out(['error'=>'Producto no disponible.'],404); if($qty>(int)$p['stock'])out(['error'=>'No hay suficientes unidades.'],409);
        $db->prepare('INSERT INTO carrito_detalle(idcarrito,idproducto,precio_ven,cantidad) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE cantidad=cantidad+VALUES(cantidad), precio_ven=VALUES(precio_ven)')->execute([$cid,$pid,$p['precio'],$qty]); out(['ok'=>true],201);
    }
    if ($method==='PUT' && $action==='cart') {
        $uid=userId(); $b=body(); $pid=(int)($b['idproducto']??0); $qty=(int)($b['cantidad']??0); $cid=cartId($db,$uid);
        if($qty<1||$qty>99)out(['error'=>'La cantidad debe ser entre 1 y 99.'],422);
        $q=$db->prepare('SELECT p.stock FROM carrito_detalle d JOIN carrito c ON c.idcarrito=d.idcarrito JOIN productos p ON p.idproducto=d.idproducto WHERE c.idcarrito=? AND c.idusuario=? AND d.idproducto=?');$q->execute([$cid,$uid,$pid]);$stock=$q->fetchColumn();
        if($stock===false)out(['error'=>'El producto ya no está en tu carrito.'],404);if($qty>(int)$stock)out(['error'=>'No hay suficientes unidades disponibles.'],409);
        $db->prepare('UPDATE carrito_detalle SET cantidad=? WHERE idcarrito=? AND idproducto=?')->execute([$qty,$cid,$pid]);out(['ok'=>true]);
    }
    if ($method==='DELETE' && $action==='cart') { $uid=userId();$b=body();$db->prepare('DELETE d FROM carrito_detalle d JOIN carrito c ON c.idcarrito=d.idcarrito WHERE c.idusuario=? AND c.estado="activo" AND d.idproducto=?')->execute([$uid,(int)($b['idproducto']??0)]);out(['ok'=>true]); }
    if ($method==='POST' && $action==='buy') {
        $uid=userId(); $b=body(); $pid=(int)($b['idproducto']??0); $qty=max(1,min(99,(int)($b['cantidad']??1)));
        $db->beginTransaction();
        $q=$db->prepare("SELECT idproducto,sku,nombre,precio,stock FROM productos WHERE idproducto=? AND estado='activo' FOR UPDATE"); $q->execute([$pid]); $p=$q->fetch();
        if(!$p){$db->rollBack();out(['error'=>'Producto no disponible.'],404);}
        if((int)$p['stock']<$qty){$db->rollBack();out(['error'=>'No hay suficientes unidades.'],409);}
        $total=(float)$p['precio']*$qty;
        $db->prepare("INSERT INTO pedidos(idusuario,total,estado) VALUES(?,?,'pendiente')")->execute([$uid,$total]); $order=(int)$db->lastInsertId();
        $db->prepare('INSERT INTO pedido_detalle(idpedido,idproducto,sku,nombre_producto,precio_unitario,cantidad) VALUES(?,?,?,?,?,?)')->execute([$order,$p['idproducto'],$p['sku'],$p['nombre'],$p['precio'],$qty]);
        $db->prepare('UPDATE productos SET stock=stock-? WHERE idproducto=?')->execute([$qty,$pid]);
        $db->prepare("UPDATE productos SET estado=IF(stock=0,'agotado','activo') WHERE idproducto=?")->execute([$pid]);
        $db->prepare("INSERT INTO pagos(idpedido,metodo_pago,monto,estado) VALUES(?,'contra_entrega',?,'pendiente')")->execute([$order,$total]);
        $db->prepare("INSERT INTO entregas(idpedido,estado) VALUES(?,'pendiente')")->execute([$order]);
        $db->commit(); out(['ok'=>true,'pedido'=>$order,'total'=>number_format($total,2,'.','')],201);
    }
    if ($method==='POST' && $action==='checkout') {
        $uid=userId(); $b=body(); $cid=cartId($db,$uid); $db->beginTransaction();
        $q=$db->prepare('SELECT d.idproducto,d.precio_ven,d.cantidad,p.sku,p.nombre,p.stock FROM carrito_detalle d JOIN productos p ON p.idproducto=d.idproducto WHERE d.idcarrito=? FOR UPDATE');$q->execute([$cid]);$allItems=$q->fetchAll();
        $selected=isset($b['productos'])&&is_array($b['productos'])?array_values(array_unique(array_map('intval',$b['productos']))):null;
        $items=$selected===null?$allItems:array_values(array_filter($allItems,fn($it)=>in_array((int)$it['idproducto'],$selected,true)));
        if(!$items){$db->rollBack();out(['error'=>'Selecciona al menos un producto para continuar.'],422);}
        $total=0;foreach($items as $it){if((int)$it['stock']<(int)$it['cantidad']){$db->rollBack();out(['error'=>'Un producto ya no tiene existencias suficientes.'],409);} $total+=(float)$it['precio_ven']*(int)$it['cantidad'];}
        $addr=null;if(!empty($b['direccion'])){ $a=$b['direccion'];$db->prepare('INSERT INTO direcciones(idusuario,alias,calle,numero_exterior,municipio,colonia,estado,codigo_postal,referencias) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$uid,'Entrega',trim($a['calle']??''),trim($a['numero']??''),trim($a['municipio']??''),trim($a['colonia']??''),trim($a['estado']??''),trim($a['cp']??''),trim($a['referencias']??'')]);$addr=(int)$db->lastInsertId(); }
        $db->prepare("INSERT INTO pedidos(idusuario,iddireccion,total,estado) VALUES(?,?,?,'pendiente')")->execute([$uid,$addr,$total]);$order=(int)$db->lastInsertId();
        foreach($items as $it){$db->prepare('INSERT INTO pedido_detalle(idpedido,idproducto,sku,nombre_producto,precio_unitario,cantidad) VALUES(?,?,?,?,?,?)')->execute([$order,$it['idproducto'],$it['sku'],$it['nombre'],$it['precio_ven'],$it['cantidad']]);$db->prepare('UPDATE productos SET stock=stock-? WHERE idproducto=?')->execute([$it['cantidad'],$it['idproducto']]);$db->prepare("UPDATE productos SET estado=IF(stock=0,'agotado','activo') WHERE idproducto=?")->execute([$it['idproducto']]);}
        $methodPay=in_array($b['metodo_pago']??'', ['contra_entrega','transferencia'],true)?$b['metodo_pago']:'contra_entrega';$db->prepare("INSERT INTO pagos(idpedido,metodo_pago,monto,estado) VALUES(?,?,?,'pendiente')")->execute([$order,$methodPay,$total]);$db->prepare("INSERT INTO entregas(idpedido,estado) VALUES(?,'pendiente')")->execute([$order]);
        $ids=array_map(fn($it)=>(int)$it['idproducto'],$items);$marks=implode(',',array_fill(0,count($ids),'?'));$db->prepare("DELETE FROM carrito_detalle WHERE idcarrito=? AND idproducto IN ($marks)")->execute(array_merge([$cid],$ids));
        $remaining=$db->prepare('SELECT COUNT(*) FROM carrito_detalle WHERE idcarrito=?');$remaining->execute([$cid]);if((int)$remaining->fetchColumn()===0)$db->prepare("UPDATE carrito SET estado='convertido' WHERE idcarrito=?")->execute([$cid]);
        $db->commit();out(['ok'=>true,'pedido'=>$order,'total'=>number_format($total,2,'.','')],201);
    }
    out(['error'=>'Acción no encontrada.'],404);
} catch (PDOException $e) { if(isset($db)&&$db->inTransaction())$db->rollBack(); if($e->getCode()==='23000')out(['error'=>'El correo ya está registrado o los datos relacionados no son válidos.'],409); error_log($e->getMessage());out(['error'=>'Error al acceder a la base de datos. Revisa la configuración del servidor.'],500); }
catch (Throwable $e) { if(isset($db)&&$db->inTransaction())$db->rollBack();error_log($e->getMessage());out(['error'=>'Error interno del servidor.'],500); }
