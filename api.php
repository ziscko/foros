<?php
   //api foros
   session_start();
   if(!isset($_SESSION['redir'])) $_SESSION['redir'] = "/foros/";
   include_once(__DIR__."/config.php");
   $msg = new stdclass; $msg->error = "error :(";
   //
   if(isset($_SERVER['HTTP_ORIGIN'])) {
      if(in_array($_SERVER['HTTP_ORIGIN'], $sitios)) { header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']); } 
      else { header("HTTP/1.0 403 Origin Denied"); return; }
   }
   if(strcmp($_POST['meme'],$meme)) {
   } elseif(isset($_POST['login'])) {
      $user = (object)$_POST['login'];
      $ID=false;
      //existe?
      $q = sprintf("SELECT * FROM users WHERE socialid = '%d' AND tipo='%s' AND correo='%s'",__($user->id), __($user->social), __($user->correo));
      $e = $sql->Query($q);
      if($e->num_rows>0) {
         $existe = $e->fetch_object();
         $msg->id = $ID = $existe->id;
         $user->fecha = $existe->alta;
         $user->alias = $existe->alias;
         $q = sprintf("UPDATE users SET genero='%s', nombre='%s', apellido='%s' WHERE id = %d", __($user->genero),  __($user->nombre),  __($user->apellido),  $ID);
      } else {
         $user->fecha = date('Y-m-d');
         $q = sprintf("INSERT INTO users (id,socialid,alta, correo, genero, nombre,apellido,alias,tipo) values(null,'%s', now(), '%s', '%s', '%s', '%s','%s','%s')",__($user->id), __($user->correo), __($user->genero),  __($user->nombre),  __($user->apellido), __($user->nombre), __($user->social));
         if($sql->Query($q)) {
            $msg->id = $ID = $sql->inser_id;
            $user->alias = $user->nombre;
         } else {
            $msg->error = "No se pudo almacenar el dato.";
         }
      }
      //
      if($ID) {
         $_SESSION['foro'] = $ID;
         $_SESSION['data'] = $user;
         unset($msg->error);
      }
   } elseif(isset($_POST['post'])) {
      //post leído
      unset($msg->error);
      $e=$sql->Query("SELECT * FROM postslog WHERE postid='".__($_POST['post'])."' AND me='".$_SESSION['me']."'");
      $msg->new=0;
      if($e->num_rows<1) {
         $msg->new=1;
         $q=sprintf("INSERT INTO postslog (postid,me,fecha) VALUES('%d','%d',now())",__($_POST['post']),$_SESSION['me']);
      } else {
         $q=sprintf("UPDATE postslog SET fecha = now() WHERE postid='%d' and me='%d'",__($_POST['post']),$_SESSION['me']);
      }
      unset($msg->error);
      $sql->Query($q);
   }
   echo json_encode($msg);