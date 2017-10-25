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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oRotulo = new rotulocampo();
$oRotulo->label('fase.mo04_codigo');
$oRotulo->label('fase.mo04_desc');
?>
<html>
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
</head>
<body class='body-default'>
  <div class='container'>
    <form name="form1">
      <fieldset>
        <legend>Processar Designação</legend>
        <table class="form-container">
          <tr>
          <td nowrap title="<?=$Tmo04_codigo?>">
            <label for="mo04_codigo">
              <?php
                db_ancora("Fase: ", "pesquisaFase(true);", 1);
              ?>
            </label>
          </td>
          <td>
            <?php
              db_input('mo04_codigo',10,$Imo04_codigo,true,'text', 1,"onchange='pesquisaFase(false);'");
              db_input('mo04_desc',40,$Imo04_desc,true,'text',3,'');
            ?>
          </td>
        </tr>
        </table>
      </fieldset>
      <input type='button' name='btnProcessar' id='btnProcessar' value="Processar" disabled="disabled" />
    </form>
  </div>
<?php
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
<script type="text/javascript">

var sRPC                           = 'mol4_processardesignacao.RPC.php';
const MSG_MOL3_PROCESSARDESIGNACAO = "educacao.matriculaonline.mol3_processardesignacao001.";
function pesquisaFase(lMostra) {

  var sUrl = 'func_faseencerrada.php?funcao_js=parent.mostraFase';

  if (lMostra) {

    sUrl += '|mo04_codigo|mo04_desc';
    js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_fase',sUrl , 'Pesquisa Fase',true);
  } else if ( $F('mo04_codigo') != '' ) {

    sUrl += '&pesquisa_chave='+$F('mo04_codigo');
    js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_fase', sUrl,'Pesquisa Fase', false)
  } else {

    $('mo04_codigo').value = '';
    $('mo04_desc').value   = '';
    $('btnProcessar').setAttribute('disabled', 'disabled');
  }
}

function mostraFase() {

  if ( typeof arguments[1] == 'boolean' ) {

    $('mo04_desc').value = arguments[0];
    if ( arguments[1] ) {
      $('mo04_codigo').value = '';
    }

    liberaProcessar();
    return;
  }
  $('mo04_codigo').value = arguments[0];
  $('mo04_desc').value   = arguments[1];
  db_iframe_fase.hide();
  liberaProcessar();
}

function liberaProcessar() {

  if ($F('mo04_codigo') == '') {
    return;
  }

  var oAjax = new AjaxRequest(sRPC, {'exec':'validaEnsinosFase', iFase : $F('mo04_codigo')}, retornoLiberaProcessar);
  oAjax.setMessage( _M(MSG_MOL3_PROCESSARDESIGNACAO + "verificando_criterios_designacao" ) );
  oAjax.execute();
}

function retornoLiberaProcessar(oRetorno, lErro) {

  if (lErro) {

    alert(oRetorno.sMessage.urlDecode());
    return;
  }
  $('btnProcessar').removeAttribute('disabled');
}

$('btnProcessar').observe('click', function() {

  var oAjax = new AjaxRequest(sRPC, {exec:'processarFase', iFase : $F('mo04_codigo')}, retornoProcessar);
  oAjax.setMessage('Processando ...');
  oAjax.execute();
});

function retornoProcessar(oRetorno, lErro) {

  alert(oRetorno.sMessage.urlDecode());
  $('btnProcessar').setAttribute('disabled', 'disabled');
  if (lErro) {
    return;
  }
  $('mo04_codigo').value = '';
  $('mo04_desc').value   = '';
}
</script>