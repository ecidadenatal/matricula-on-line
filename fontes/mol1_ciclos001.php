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
require_once(modification("dbforms/db_funcoes.php"));

db_postmemory( $_POST );

$oDaoCiclos       = new cl_ciclos();
$oDaoCiclosEnsino = new cl_ciclosensino();

$db_opcao = 1;
$db_botao = true;
$lErro    = false;

if( isset( $incluir ) ) {

  try {

    db_inicio_transacao();

    $oDaoCiclos->incluir( null );

    if( $oDaoCiclos->erro_status == 0 ) {
      throw new DBException( $oDaoCiclos->erro_msg );
    }

    $iCiclo   = $oDaoCiclos->mo09_codigo;
    $aEnsinos = explode( ",", $ensinos );

    foreach( $aEnsinos as $iEnsino ) {

      $oDaoCiclosEnsino->mo14_sequencial = null;
      $oDaoCiclosEnsino->mo14_ciclo      = $iCiclo;
      $oDaoCiclosEnsino->mo14_ensino     = $iEnsino;
      $oDaoCiclosEnsino->incluir( null );

      if( $oDaoCiclosEnsino->erro_status == 0 ) {
        throw new DBException( $oDaoCiclosEnsino->erro_msg );
      }
    }

    db_fim_transacao();
    db_msgbox( 'Inclusão realizada com sucesso.' );
    db_redireciona( 'mol1_ciclos001.php' );
  } catch( Exception $oErro ) {

    db_msgbox( $oErro->getMessage() );
    db_fim_transacao( true );
  }
}
?>
<html>
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBLancador.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
  <div class="container">
    <?php
    include(modification("forms/db_frmciclos.php"));
    ?>
  </div>
<?php
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
<script>
js_tabulacaoforms( "form1", "mo09_status", true, 1, "mo09_status", true );
</script>