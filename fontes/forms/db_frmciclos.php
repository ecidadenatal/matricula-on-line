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
$oDaoCiclos->rotulo->label();
$sOpcao = "incluir";

if( $db_opcao == 2 || $db_opcao == 22 ) {
  $sOpcao = "alterar";
} else if( $db_opcao == 3 || $db_opcao == 33 ) {
  $sOpcao = "excluir";
}
?>
<form name="form1" method="post" action="">
  <fieldset class="form-container">
    <legend>Cadastro de Ciclos</legend>
    <table>
      <tr style="display: none;">
        <td nowrap title="<?=$Tmo09_codigo?>">
          <label for="mo09_codigo"><?=$Lmo09_codigo?></label>
        </td>
        <td>
          <?php
          db_input( 'mo09_codigo', 8, $Imo09_codigo, true, 'text', 3 );
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo09_descricao?>">
          <label for="mo09_descricao"><?=$Lmo09_descricao?></label>
        </td>
        <td>
          <?php
          db_input( 'mo09_descricao', 70, $Imo09_descricao, true, 'text', $db_opcao );
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo09_sigla?>">
          <label for="mo09_sigla"><?=$Lmo09_sigla?></label>
        </td>
        <td>
          <?php
          db_input( 'mo09_sigla', 10, $Imo09_sigla, true, 'text', $db_opcao );
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo09_eja?>">
          <label for="mo09_eja"><?=$Lmo09_eja?></label>
        </td>
        <td>
          <?php
          $x = array( "f" => "NÃO", "t" => "SIM" );
          db_select( 'mo09_eja', $x, true, $db_opcao );
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo09_dtcad?>">
          <label for="mo09_dtcad"><?=$Lmo09_dtcad?></label>
        </td>
        <td>
          <?php
          db_inputdata( 'mo09_dtcad', @$mo09_dtcad_dia, @$mo09_dtcad_mes, @$mo09_dtcad_ano, true, 'text', $db_opcao );
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo09_status?>">
          <label for="mo09_status"><?=$Lmo09_status?></label>
        </td>
        <td>
          <?php
          $x = array( "f" => "NÃO", "t" => "SIM" );
          db_select( 'mo09_status', $x, true, $db_opcao );
          ?>
        </td>
      </tr>
      <tr>
        <td id="lancadorEnsinos" colspan="2"></td>
      </tr>
    </table>
  </fieldset>
  <input id="ensinos" name="ensinos" type="hidden" value="" />
  <input id="opcao"   name="<?php echo $sOpcao; ?>" type="hidden" value="<?php echo $sOpcao; ?>" />

  <input name="<?=( $db_opcao == 1 ? "incluir" : ( $db_opcao == 2 || $db_opcao == 22 ? "alterar" : "excluir" ) )?>"
         type="button"
         id="db_opcao"
         value="<?=( $db_opcao == 1 ? "Incluir" : ( $db_opcao == 2 || $db_opcao == 22 ? "Alterar" : "Excluir" ) )?>"
         <?=( $db_botao == false ? "disabled" : "" )?>
         onclick="validaCampos()"
  />
  <input name="pesquisar"
         type="button"
         id="pesquisar"
         value="Pesquisar"
         onclick="js_pesquisa();"
  />
</form>
<script>
const MENSAGENS_DB_FRMCICLOS = 'educacao.matriculaonline.db_frmciclos.';

var iOpcao                 = <?php echo $db_opcao; ?>;
var sRpc                   = 'mol4_ciclos.RPC.php';
var lPossuiFasesVinculadas = false;

function js_pesquisa() {

  js_OpenJanelaIframe(
                       'CurrentWindow.corpo',
                       'db_iframe_ciclos',
                       'func_ciclos.php?funcao_js=parent.js_preenchepesquisa|dl_sequencial',
                       'Pesquisa Ciclo',
                       true
                     );
}

function js_preenchepesquisa(chave) {

  db_iframe_ciclos.hide();
  <?php
  if( $db_opcao != 1 ) {
    echo " location.href = '" . basename( $GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"] ) . "?chavepesquisa='+chave";
  }
  ?>
}

/**
 * Verifica se todos os campos foram preenchidos, antes de percorrer os ensinos e enviar via submit
 */
function validaCampos() {

  if( empty( $F('mo09_descricao') ) ) {

    alert( _M( MENSAGENS_DB_FRMCICLOS + 'descricao_nao_informada' ) );
    return false;
  }


  if( empty( $F('mo09_dtcad') ) ) {

    alert( _M( MENSAGENS_DB_FRMCICLOS + 'data_cadastro_nao_informado' ) );
    return false;
  }

  percorreEnsinos();
}

/**
 * Verifica se ao menos 1 ensino foi selecionado
 */
function percorreEnsinos() {

  if( oLancadorEnsinos.getRegistros().length == 0 ) {

    alert( _M( MENSAGENS_DB_FRMCICLOS + 'selecione_ensino' ) );
    return false;
  }

  var aEnsinos = [];
  oLancadorEnsinos.getRegistros().each(function( oEnsino ) {
    aEnsinos.push( oEnsino.sCodigo );
  });

  $('ensinos').value = aEnsinos.join(',');

  if ( iOpcao == 3 && lPossuiFasesVinculadas ) {

    if ( !confirm( _M( MENSAGENS_DB_FRMCICLOS + 'confirma_exclusao' ) ) ) {
      return false;
    }
  }

  document.form1.submit();
}

/**
 * Pesquisa os ensinos vinculados a um ciclo
 */
function pesquisaEnsinosVinculados() {

  var oDados           = {};
      oDados.sExecucao = 'pesquisaEnsinosVinculadosCiclo';
      oDados.iCiclo    = $F('mo09_codigo');

  var oAjaxRequest = new AjaxRequest( sRpc, oDados, retornoPesquisaEnsinosVinculados );
      oAjaxRequest.setMessage( _M( MENSAGENS_DB_FRMCICLOS + 'pesquisando_ensinos' ) );
      oAjaxRequest.execute();
}

/**
 * Retorna os ensinos vinculados, preenchendo a grid do DBLancador
 * @param oRetorno
 * @param lErro
 * @returns {boolean}
 */
function retornoPesquisaEnsinosVinculados( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  oRetorno.aEnsinos.each(function( oEnsino ) {

    $('txtCodigooLancadorEnsinos').value    = oEnsino.iCodigo;
    $('txtDescricaooLancadorEnsinos').value = oEnsino.sDescricao.urlDecode();
    oLancadorEnsinos.lancarRegistro();
    $('txtCodigooLancadorEnsinos').value    = '';
    $('txtDescricaooLancadorEnsinos').value = '';
  });
}

var oLancadorEnsinos                = new DBLancador( 'oLancadorEnsinos' );
    oLancadorEnsinos.sTextoFieldset = 'Adicionar Ensino';
    oLancadorEnsinos.setGridHeight( 150 );
    oLancadorEnsinos.setLabelAncora( 'Ensino:' );
    oLancadorEnsinos.setNomeInstancia( 'oLancadorEnsinos' );
    oLancadorEnsinos.setParametrosPesquisa( 'func_ensino.php', [ 'ed10_i_codigo', 'ed10_c_descr' ] );

    if( iOpcao == 3 || iOpcao == 33 ) {
      oLancadorEnsinos.setHabilitado( false );
    }

    oLancadorEnsinos.setTituloJanela( 'Pesquisa Ensino' );
    oLancadorEnsinos.show( $('lancadorEnsinos') );

if( iOpcao == 22 || iOpcao == 33 ) {
  js_pesquisa();
}

if( iOpcao == 2 || iOpcao == 3 ) {

  pesquisaEnsinosVinculados();
  possuiFasesVinculadas();
}

$('mo09_descricao').className = 'field-size7';
$('mo09_sigla').className     = 'field-size2';
$('mo09_eja').className       = 'field-size2';
$('mo09_dtcad').className     = 'field-size2';
$('mo09_status').className    = 'field-size2';

/**
 * Verifica os ensinos que possuem vagas vinculadas
 * Reescrito o metodo removerRegistro do DBLancador
 */
oLancadorEnsinos.removerRegistro = function ( iEnsino ) {

  if( empty( $F('mo09_codigo') ) ) {

    removeEnsino( iEnsino );
    return;
  }

  var oDados           = {};
      oDados.sExecucao = 'pesquisaVagasVinculadasEnsino';
      oDados.iCiclo    = $F('mo09_codigo');
      oDados.iEnsino   = iEnsino;

  var oAjaxRequest = new AjaxRequest( sRpc, oDados, retornoVerificaVagaVinculadaEnsino );
      oAjaxRequest.setMessage( _M( MENSAGENS_DB_FRMCICLOS + 'verificando_ensino' ) );
      oAjaxRequest.execute();
};

function retornoVerificaVagaVinculadaEnsino( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  if ( oRetorno.lVagasVinculadasEnsino ) {

    if ( !confirm( _M( MENSAGENS_DB_FRMCICLOS + 'confirma_exclusao_ensino'))) {
      return false;
    }
  }

  removeEnsino( oRetorno.iEnsino );
}

function removeEnsino( iEnsino ) {

  var sInstancia = oLancadorEnsinos.getNomeInstancia();

  if (oLancadorEnsinos.oRegistros[sInstancia + iEnsino]) { // se o registro clicado estar dentro do array em memoria retiramos ele
    delete(oLancadorEnsinos.oRegistros[sInstancia + iEnsino]);
  }

  oLancadorEnsinos.renderizarRegistros(); // e a grid será novamente renderizada
}

/**
 * Verifica se o ciclo possui fase utilizando o mesmo
 */
function possuiFasesVinculadas() {

  var oDados           = {};
      oDados.sExecucao = 'verificaFasesVinculadas';
      oDados.iCiclo    = $F('mo09_codigo');

  var oAjaxRequest = new AjaxRequest( sRpc, oDados, retornoPossuiFasesVinculadas );
      oAjaxRequest.setMessage( _M( MENSAGENS_DB_FRMCICLOS + 'verificando_fases_vinculadas' ) );
      oAjaxRequest.execute();
}

/**
 * Retorno da validaçao das fases que utilizam o ciclo
 * @param oRetorno
 * @param lErro
 * @returns {boolean}
 */
function retornoPossuiFasesVinculadas( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  if( oRetorno.aFases.length == 0 ) {

    lPossuiFasesVinculadas = false;
    return;
  }

  lPossuiFasesVinculadas = true;
}
</script>
