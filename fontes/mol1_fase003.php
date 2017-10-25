<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("classes/db_fase_classe.php"));
require_once(modification("dbforms/db_funcoes.php"));

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($_POST);

$mo04_dtini_dia     = '';
$mo04_dtini_mes     = '';
$mo04_dtini_ano     = '';
$mo04_dtfim_dia     = '';
$mo04_dtfim_mes     = '';
$mo04_dtfim_ano     = '';
$mo04_datacorte_dia = '';
$mo04_datacorte_mes = '';
$mo04_datacorte_ano = '';

$clfase     = new cl_fase;

$db_botao = false;
$db_opcao = 33;

if ( isset($excluir) ) {

  $oDaoVagas  = new cl_vagas;

  db_inicio_transacao();
  $db_opcao = 3;
  $oDaoVagas->excluir(null, "mo10_fase = {$mo04_codigo}");
  $clfase->excluir($mo04_codigo);
  db_fim_transacao();
} else if ( isset($chavepesquisa) ) {

  $db_opcao = 3;
  $result   = $clfase->sql_record($clfase->sql_query($chavepesquisa));
  db_fieldsmemory($result,0);
  $db_botao = true;
}
?>
<html>
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
  <?php
    require_once(modification("forms/db_frmfase.php"));
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>
</body>
</html>
<?php
if (isset($excluir)) {

  if ($clfase->erro_status == "0") {
    $clfase->erro(true,false);
  }else{
    $clfase->erro(true,true);
  }
}
if($db_opcao==33){
  echo "<script>document.form1.pesquisar.click();</script>";
}
?>
<script>
js_tabulacaoforms("form1","db_opcao",true,1,"db_opcao",true);
</script>