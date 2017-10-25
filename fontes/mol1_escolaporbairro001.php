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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oDaoEscola = new cl_escola();
$oDaoEscola->rotulo->label();
?>
<html>
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBLancador.widget.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
  <div class="container">
    <form>
      <fieldset>
        <legend>Escola por Bairros</legend>
        <table style="border-spacing: 0px 8px;">
          <tr>
            <td>
              <label for="ed18_i_codigo">
              <?php
              db_ancora( 'Escola:', 'pesquisaEscolas( true );', 1 );
              ?>
              </label>
            </td>
            <td>
              <?php
              db_input( 'ed18_i_codigo', 10, $Ied18_i_codigo, true, 'text', 1, "onchange='pesquisaEscolas( false );'" );
              db_input( 'ed18_c_nome',   10, $Ied18_c_nome,   true, 'text', 3 );
              ?>
            </td>
          </tr>
          <tr>
            <td id="lancadorBairros" colspan="2"></td>
          </tr>
        </table>
      </fieldset>
      <input id="btnSalvar" name="btnSalvar" type="button" value="Salvar" />
      <input id="btnLimpar" name="btnLimpar" type="button" value="Limpar" />
    </form>
  </div>
</body>
<?php
db_menu( db_getsession ( "DB_id_usuario" ), db_getsession ( "DB_modulo" ), db_getsession ( "DB_anousu" ), db_getsession ( "DB_instit" ) );
?>
<script>
const MENSAGENS_MOL1_ESCOLAPORBAIRRO = 'educacao.matriculaonline.mol1_escolaporbairro001.';

var sRpc = 'mol4_escolabairro.RPC.php';

$('btnLimpar').onclick = function() {
  limpaCampos();
};

$('btnSalvar').onclick = function() {
  salvarBairros();
};

/**
 * Cria a instancia de DBLancador para adicionar os bairros
 * @type {DBLancador}
 */
var oLancadorBairro = new DBLancador( 'oLancadorBairro' );
    oLancadorBairro.setTextoFieldset( 'Bairros' );
    oLancadorBairro.setLabelAncora( 'Bairro:' );
    oLancadorBairro.setNomeInstancia( 'oLancadorBairro' );
    oLancadorBairro.setGridHeight( 150 );
    oLancadorBairro.setParametrosPesquisa( 'func_bairro.php', [ 'j13_codi', 'j13_descr' ] );
    oLancadorBairro.setTituloJanela( 'Pesquisa Bairros' );
    oLancadorBairro.show( $('lancadorBairros') );

/**
 * Pesquisa os departamentos cadastrados como escola
 * @param lMostra
 */
function pesquisaEscolas( lMostra ) {

  var sUrl        = 'func_escola.php?funcao_js=parent.retornoEscola';
  var sParametros = "|ed18_i_codigo|ed18_c_nome";

  if( !lMostra ) {

    if( empty( $F('ed18_i_codigo') ) ) {

      limpaCampos();
      return;
    }

    sParametros = '&pesquisa_chave=' + $F('ed18_i_codigo');
  }

  sUrl += sParametros;

  js_OpenJanelaIframe( 'CurrentWindow.corpo', 'db_iframe_escola', sUrl, 'Pesquisa Escola', lMostra );
}

/**
 * Retorno da escola selecionada, verificando se existem bairros vinculados a mesma
 */
function retornoEscola() {

  db_iframe_escola.hide();
  oLancadorBairro.clearAll();


  if( arguments[1] !== true && arguments[1] !== false ) {

    $('ed18_i_codigo').value = arguments[0];
    $('ed18_c_nome').value   = arguments[1];

    buscaBairrosVinculados();
  } else if( arguments[1] === false ) {

    $('ed18_c_nome').value = arguments[0];
    buscaBairrosVinculados();
  } else if( arguments[1] === true ) {

    $('ed18_i_codigo').value = '';
    $('ed18_c_nome').value   = arguments[0];
  }
}

/**
 * Busca os bairros vinculados a escola selecionada
 */
function buscaBairrosVinculados() {

  var oParametros          = {};
      oParametros.sExecuta = 'bairrosVinculados';
      oParametros.iEscola  = $F('ed18_i_codigo');

  var oAjaxRequest = new AjaxRequest( sRpc, oParametros, retornoBuscaBairrosVinculados );
      oAjaxRequest.setMessage( _M( MENSAGENS_MOL1_ESCOLAPORBAIRRO + 'buscando_bairros' ) );
      oAjaxRequest.execute();
}

/**
 * Retorna os bairros vinculados a escola, preenchendo os mesmos na grid
 * @param oRetorno
 * @param lErro
 * @returns {boolean}
 */
function retornoBuscaBairrosVinculados( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  oRetorno.aBairros.each(function( oBairro ) {

    $('txtCodigooLancadorBairro').value    = oBairro.codigo_bairro;
    $('txtDescricaooLancadorBairro').value = oBairro.nome_bairro.urlDecode();
    oLancadorBairro.lancarRegistro();

    $('txtCodigooLancadorBairro').value    = '';
    $('txtDescricaooLancadorBairro').value = '';
  });
}

/**
 * Salva os vinculos( ou remoçao dos mesmos ) em relaçao a escola
 * @returns {boolean}
 */
function salvarBairros() {

  if( empty( $F('ed18_i_codigo') ) ) {

    alert( _M( MENSAGENS_MOL1_ESCOLAPORBAIRRO + 'escola_nao_informada' ) );
    return false;
  }

  var aBairros = [];
  oLancadorBairro.getRegistros().each(function( oBairro ) {
    aBairros.push( oBairro.sCodigo );
  });

  if (aBairros.length == 0) {

    alert( _M(MENSAGENS_MOL1_ESCOLAPORBAIRRO + 'nenhum_bairro_adicionado') );
    return;
  }

  var oParametros = {};
      oParametros.sExecuta = 'salvarBairros';
      oParametros.iEscola  = $F('ed18_i_codigo');
      oParametros.aBairros = aBairros;

  var oAjaxRequest = new AjaxRequest( sRpc, oParametros, retornoSalvarBairros );
      oAjaxRequest.setMessage( _M( MENSAGENS_MOL1_ESCOLAPORBAIRRO + 'salvando_bairros' ) );
      oAjaxRequest.execute();
}

/**
 * Retorno do salvar os vinculos
 * @param oRetorno
 * @param lErro
 */
function retornoSalvarBairros( oRetorno, lErro ) {

  alert( oRetorno.sMensagem.urlDecode() );

  if( !lErro ) {
    limpaCampos();
  }
}

function limpaCampos() {

  $('ed18_i_codigo').value = '';
  $('ed18_c_nome').value   = '';

  $('txtCodigooLancadorBairro').value    = '';
  $('txtDescricaooLancadorBairro').value = '';

  oLancadorBairro.clearAll();
}

$('ed18_i_codigo').className = 'field-size2';
$('ed18_c_nome').className   = 'field-size9';
</script>
