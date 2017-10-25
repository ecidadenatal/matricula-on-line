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
parse_str( $_SERVER["QUERY_STRING"] );

$clciclos = new cl_ciclos;
$clciclos->rotulo->label("mo09_codigo");
$clciclos->rotulo->label("mo09_descricao");
?>
<html>
<head>
  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
  <link href='estilos.css' rel='stylesheet' type='text/css'>
  <script language='JavaScript' type='text/javascript' src='scripts/scripts.js'></script>
</head>
<body>
  <form name="form2" method="post" action="" class="container">
    <fieldset>
      <legend>Dados para Pesquisa</legend>
      <table width="35%" border="0" align="center" cellspacing="3" class="form-container">
        <tr>
          <td><label><?=$Lmo09_codigo?></label></td>
          <td><? db_input("mo09_codigo",10,$Imo09_codigo,true,"text",4,"","chave_mo09_codigo"); ?></td>
        </tr>
        <tr>
          <td><label><?=$Lmo09_descricao?></label></td>
          <td><? db_input("mo09_descricao",10,$Imo09_descricao,true,"text",4,"","chave_mo09_descricao");?></td>
        </tr>
      </table>
    </fieldset>
    <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar">
    <input name="limpar" type="reset" id="limpar" value="Limpar" >
    <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_ciclos.hide();">
  </form>
      <?php
      if(!isset($pesquisa_chave)){

        $campos  = "mo09_codigo    as dl_sequencial,";
        $campos .= "mo09_descricao as dl_descricao,";
        $campos .= "mo09_sigla     as dl_sigla, ";
        $campos .= "mo09_status    as dl_status,";
        $campos .= "mo09_dtcad     as dl_cadastro,";
        $campos .= "mo09_eja       as dl_eja";

        if(isset($chave_mo09_codigo) && (trim($chave_mo09_codigo)!="") ){
	         $sql = $clciclos->sql_query($chave_mo09_codigo,$campos,"mo09_codigo");
        }else if(isset($chave_mo09_descricao) && (trim($chave_mo09_descricao)!="") ){
	         $sql = $clciclos->sql_query("",$campos,"mo09_descricao"," mo09_descricao like '$chave_mo09_descricao%' ");
        }else{
           $sql = $clciclos->sql_query("",$campos,"mo09_codigo","");
        }
        $repassa = array();
        if(isset($chave_mo09_descricao)){
          $repassa = array("chave_mo09_codigo"=>$chave_mo09_codigo,"chave_mo09_descricao"=>$chave_mo09_descricao);
        }
        echo '<div class="container">';
        echo '  <fieldset>';
        echo '    <legend>Resultado da Pesquisa</legend>';
          db_lovrot($sql,15,"()","",$funcao_js,"","NoMe",$repassa);
        echo '  </fieldset>';
        echo '</div>';
      }else{
        if($pesquisa_chave!=null && $pesquisa_chave!=""){
          $result = $clciclos->sql_record($clciclos->sql_query($pesquisa_chave));
          if($clciclos->numrows!=0){
            db_fieldsmemory($result,0);
            echo "<script>".$funcao_js."('$mo09_descricao',false);</script>";
          }else{
	         echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
          }
        }else{
	       echo "<script>".$funcao_js."('',false);</script>";
        }
      }
      ?>
</body>
</html>
<script>
js_tabulacaoforms("form2","chave_mo09_descricao",true,1,"chave_mo09_descricao",true);
</script>

<script type="text/javascript">
(function() {
  var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
  input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
})();
</script>