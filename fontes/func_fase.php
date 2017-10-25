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

db_postmemory($_POST);
parse_str( $_SERVER["QUERY_STRING"] );

$clfase = new cl_fase;
$clfase->rotulo->label("mo04_codigo");
$clfase->rotulo->label("mo04_desc");

$aWhere = array();
if ( !empty($lAtivos) && $lAtivos == 'true') {
  $aWhere[] = " mo04_processada is false ";
}

if ( !empty($lAtivos) && $lAtivos == 'false') {
  $aWhere[] = " mo04_processada is true ";
}

if ( !empty($lPossuiVagas) && $lPossuiVagas == 'true' ) {
  $aWhere[] = " exists( select 1 from plugins.vagas where mo10_fase = mo04_codigo and mo10_numvagas <> 0 )";
}


?>
<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
  <link href='estilos.css' rel='stylesheet' type='text/css'>
  <script language='JavaScript' type='text/javascript' src='scripts/scripts.js'></script>
  <script language='JavaScript' type='text/javascript' src='scripts/prototype.js'></script>
</head>
<body>
  <form name="form2" method="post" action="" class="container">
    <fieldset>
      <legend>Dados para Pesquisa</legend>
      <table width="35%" border="0" align="center" cellspacing="3" class="form-container">
        <tr>
          <td>
            <label for="chave_mo04_codigo"><?=$Lmo04_codigo?></label>
          </td>
          <td>
            <?php
            db_input( "mo04_codigo", 8, $Imo04_codigo, true, "text", 4, "", "chave_mo04_codigo" );
            ?>
          </td>
        </tr>
        <tr>
          <td>
            <label for="chave_mo04_desc"><?=$Lmo04_desc?></label>
          </td>
          <td>
            <?php
            db_input( "mo04_desc", 8, $Imo04_desc, true, "text", 4, "", "chave_mo04_desc" );
            ?>
          </td>
        </tr>
      </table>
    </fieldset>
    <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
    <input name="limpar" type="reset" id="limpar" value="Limpar" >
    <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_fase.hide();">
  </form>
  <?php

  if( !isset( $pesquisa_chave ) ) {

    if( isset( $campos ) == false ) {

      if( file_exists( "funcoes/db_func_fase.php" ) == true ) {
        include(modification("funcoes/db_func_fase.php"));
      } else {
        $campos = "plugins.fase.*";
      }
    }

    if( isset( $chave_mo04_codigo ) && ( trim( $chave_mo04_codigo ) != "" ) ) {
      $aWhere[] = "mo04_codigo = {$chave_mo04_codigo}";
    }

    if( isset( $chave_mo04_desc ) && ( trim( $chave_mo04_desc ) != "" ) ) {
      $aWhere[] = "mo04_desc like '{$chave_mo04_desc}%'";
    }

    $sWhere = implode(" and ", $aWhere);
    $sql    = $clfase->sql_query("", $campos, "mo04_desc", $sWhere);

    $repassa = array();
    if( isset( $chave_mo04_desc ) ) {
      $repassa = array( "chave_mo04_codigo" => $chave_mo04_codigo, "chave_mo04_desc" => $chave_mo04_desc );
    }

    echo '<div class="container">';
    echo '  <fieldset>';
    echo '    <legend>Resultado da Pesquisa</legend>';
      db_lovrot($sql,15,"()","",$funcao_js,"","NoMe",$repassa);
    echo '  </fieldset>';
    echo '</div>';
  } else {

    if( $pesquisa_chave != null && $pesquisa_chave != "" ) {

      $aWhere[] = " mo04_codigo = {$pesquisa_chave} ";
      $sWhere   = implode(" and ", $aWhere);
      $result   = $clfase->sql_record($clfase->sql_query(null, "*", null, $sWhere));

      if( $clfase->numrows != 0 ) {

        db_fieldsmemory( $result, 0 );
        echo "<script>".$funcao_js."('$mo04_desc',false, '$mo04_ciclo');</script>";
      } else {
        echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
      }
    } else {
      echo "<script>".$funcao_js."('',false);</script>";
    }
  }
  ?>
</body>
</html>
<script>

$('limpar').onclick = function() {

  $('chave_mo04_codigo').value = '';
  $('chave_mo04_desc').value   = '';
  $('pesquisar2').click();
}

</script>
<script type="text/javascript">
(function() {
  var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
  input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
})();
</script>