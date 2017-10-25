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
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

$oVagas = new cl_vagas();
$oVagas->rotulo->label();

$oRotulo = new rotulocampo();
$oRotulo->label("fase.mo04_desc");
$oRotulo->label("ciclosensino.mo14_ensino");
$oRotulo->label("ed18_c_nome");
$oRotulo->label("ed15_i_codigo");
$oRotulo->label("ed15_c_nome");
$oRotulo->label("ed10_c_descr");
$oRotulo->label("ed85_i_turno");
?>
<html>
<head>
<title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/datagrid.widget.js"></script>
<script type="text/javascript" src="scripts/AjaxRequest.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default" >

  <div class="container">
    <form class="form-container" name="form1">
      <fieldset>
        <legend>Cadastro de Vagas</legend>
        <table>
          <tr>
            <td>
              <label for="mo10_fase">
                <?php
                  db_ancora($Lmo10_fase,"pesquisaFase(true);", 1);
                ?>
              </label>
            </td>
            <td>
              <?php
                db_input ( 'mo10_fase', 10, $Imo10_fase, true, 'text', "", "onchange='pesquisaFase(false);'" );
                db_input ( 'mo04_desc', 50, $Imo04_desc, true, 'text', 3 );
              ?>
            </td>
          </tr>
          <tr>
            <td>
              <label for="mo10_escola">
                <?php
                  db_ancora($Lmo10_escola,"pesquisaEscola(true);", 1);
                ?>
              </label>
            </td>
            <td>
              <?php
                db_input ( 'mo10_escola', 10, $Imo10_escola, true, 'text', "", "onchange='pesquisaEscola(false);'" );
                db_input ( 'ed18_c_nome', 50, $Ied18_c_nome, true, 'text', 3 );
              ?>
            </td>
          </tr>
          <tr>
            <td>
              <label for="comboEnsino"><?=$Lmo14_ensino?></label>
            </td>
            <td>
               <select id="comboEnsino" onchange="limparEnsino(true);">
                 <option>Selecione...</option>
               </select>
            </td>
          </tr>
          <tr>
            <td>
              <label for="ed85_i_turno">
                <?php
                  db_ancora($Lmo10_turno,"pesquisaTurno(true);", 1);
                ?>
              </label>
            </td>
            <td>
              <?php
                db_input ( 'ed85_i_turno', 10, $Ied85_i_turno, true, 'text', "", "onchange='pesquisaTurno(false);'" );
                db_input ( 'ed15_c_nome',  50, $Ied15_c_nome,  true, 'text', 3 );
              ?>
            </td>
          </tr>
        </table>
      </fieldset>
      <input type="button" id="btnPesquisar" value="Pesquisar" />
    </form>
  </div>
  <input id="iCiclo" type="hidden" value="" />

  <div id="containerVagas">
    <fieldset>
      <legend>Etapas / Vagas</legend>
      <div id="ctnGridVagas"></div>
    </fieldset>
    <input type="button" id="btnSalvar" value="Salvar" />
  </div>
  <?php
    db_menu();
  ?>
</body>
</html>
<script>

const MENSAGEM_VAGAS001 =  "educacao.matriculaonline.mol1_vagas001.";

$('containerVagas').addClassName("container");
$('containerVagas').style.width = "40%";

var oGridEtapasVagas              = new DBGrid('gridEtapasVagas');
    oGridEtapasVagas.nameInstance = 'oGridEtapasVagas';
    oGridEtapasVagas.setCellWidth(new Array("70%", "30%"));
    oGridEtapasVagas.setCellAlign(new Array("left", "rigth"));
    oGridEtapasVagas.setHeader(new Array("Etapa", "Vagas", "Código Etapa", "Código Vaga"));
    oGridEtapasVagas.setHeight(200);
    oGridEtapasVagas.aHeaders[2].lDisplayed = false;
    oGridEtapasVagas.aHeaders[3].lDisplayed = false;
    oGridEtapasVagas.show($('ctnGridVagas'));


$('mo10_fase').addClassName('field-size2');
$('mo04_desc').addClassName('field-size7');
$('mo10_escola').addClassName('field-size2');
$('ed18_c_nome').addClassName('field-size7');
$('ed85_i_turno').addClassName('field-size2');
$('ed15_c_nome').addClassName('field-size7');
$('comboEnsino').setAttribute('rel', 'ignore-css');
$('comboEnsino').addClassName('field-size9');
$('btnSalvar').disabled = 'disabled';

var sCiclosRpc    = "mol4_ciclos.RPC.php";
var sVagasRpc     = "mol4_vagas.RPC.php";

function pesquisaFase( lMostra ) {

  oGridEtapasVagas.clearAll(true);
  $('btnSalvar').disabled = 'disabled';

  var sUrl = 'func_fase.php?lAtivos=true';
  if( lMostra) {

    sUrl += '&funcao_js=parent.retornoPesquisaFase|mo04_codigo|mo04_desc|mo04_ciclo';
    js_OpenJanelaIframe( '', 'db_iframe_fase', sUrl, 'Pesquisa Fase', true);
  } else if ( $F('mo10_fase') != '' ) {

    sUrl += '&funcao_js=parent.retornoPesquisaFase';
    sUrl += '&pesquisa_chave=' + $F('mo10_fase');
    js_OpenJanelaIframe( '', 'db_iframe_fase', sUrl, 'Pesquisa Fase', false);
  } else {

    $('mo04_desc').value = '';
    limpaCampos();
  }
}

function retornoPesquisaFase() {

  $('btnPesquisar').disabled = true;
  limpaCampos();

  if( typeof arguments[1] == "boolean" ) {

    $('mo04_desc').value = arguments[0];
    $('iCiclo').value    = arguments[2];

    if ( arguments[1] == true) {
      $('mo10_fase').value = '';
    }
  } else {

    $('mo10_fase').value = arguments[0];
    $('mo04_desc').value = arguments[1];
    $('iCiclo').value    = arguments[2];
  }

  $('btnPesquisar').disabled      = false;
  $('comboEnsino').options.length = 0;
  $('comboEnsino').add(new Option('Selecione...', ''));
  db_iframe_fase.hide();
}

function pesquisaEnsinosVinculados() {

  var oDados           = {};
      oDados.sExecucao = 'pesquisaEnsinosVinculadosFase';
      oDados.iFase     = $F('mo10_fase');
      oDados.iEscola   = $F('mo10_escola');

  var oAjaxRequest = new AjaxRequest( sCiclosRpc, oDados, retornoPesquisaEnsinosVinculados );
      oAjaxRequest.setMessage( _M( MENSAGEM_VAGAS001 + "buscando_ensinos") );
      oAjaxRequest.execute();
}

function retornoPesquisaEnsinosVinculados( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  var iQuantidadeEnsino = 0;

  oRetorno.aEnsinos.each( function (oEnsino) {

    $('comboEnsino').add(new Option(oEnsino.sDescricao.urlDecode(), oEnsino.iCodigo));
    iQuantidadeEnsino++;
  });

  if (iQuantidadeEnsino == 1) {
    $('comboEnsino').value = oRetorno.aEnsinos[0].iCodigo;
  }
}

function pesquisaEscola( lMostra ) {

  $('ed85_i_turno').value         = '';
  $('ed15_c_nome').value          = '';
  $('comboEnsino').options.length = 0;
  $('comboEnsino').add( new Option( 'Selecione...', '' ) );
  oGridEtapasVagas.clearAll(true);
  $('btnSalvar').disabled = 'disabled';

  $('ed85_i_turno').value = '';
  $('ed15_c_nome').value  = '';

  if ( $F('mo10_fase') == '' ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_fase") );
    return false;
  }

  var sUrl = 'func_escola.php?';
  if( lMostra) {

    sUrl += 'funcao_js=parent.retornoPesquisaEscola|ed18_i_codigo|ed18_c_nome';
    sUrl += '&iFase=' + $F('mo10_fase');
    js_OpenJanelaIframe( '', 'db_iframe_escola', sUrl, 'Pesquisa Escola', true);
  } else if ( $F('mo10_escola') != '' ) {

    sUrl += 'funcao_js=parent.retornoPesquisaEscola';
    sUrl += '&pesquisa_chave=' + $F('mo10_escola');
    sUrl += '&iFase=' + $F('mo10_fase');
    js_OpenJanelaIframe( '', 'db_iframe_escola', sUrl, 'Pesquisa Escola', false);
  } else {
    limpaCampos();
  }
}

function limparEnsino( lDeletaLinhasGrid ) {

  oGridEtapasVagas.clearAll( lDeletaLinhasGrid );
  $('btnSalvar').disabled = 'disabled';
}

function retornoPesquisaEscola() {

  $('btnPesquisar').disabled = true;

  if( typeof arguments[1] == "boolean" ) {

    $('ed18_c_nome').value = arguments[0];

    if ( arguments[1] == true) {
      $('mo10_escola').value = '';
    }

  } else {

    $('mo10_escola').value = arguments[0];
    $('ed18_c_nome').value = arguments[1];
  }

  $('btnPesquisar').disabled = false;

  db_iframe_escola.hide();
  pesquisaEnsinosVinculados();
}

function pesquisaTurno( lMostra ) {

  if( empty( $F('comboEnsino') ) ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_ensino" ) );

    $('ed85_i_turno').value = '';
    $('ed15_c_nome').value  = '';

    return false;
  }

  oGridEtapasVagas.clearAll(true);
  $('btnSalvar').disabled = 'disabled';

  if ( $F('mo10_fase') == '' ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_fase") );
    return false;
  }

  if ( $F('mo10_escola') == '' ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_escola")  );
    return false;
  }

  var sUrl = 'func_turnoescola.php?';

  if( lMostra ) {

    sUrl += 'funcao_js=parent.retornoPesquisaTurno|ed15_i_codigo|ed15_c_nome';
    sUrl += '&sEnsino=' + $F('comboEnsino');
    sUrl += '&iEscola=' + $F('mo10_escola');
    sUrl += '&lQueryTurno=true';
    js_OpenJanelaIframe( '', 'db_iframe_turno', sUrl, 'Pesquisa Turno', true );
  } else if ( $F('ed85_i_turno') != '' ) {

    sUrl += 'funcao_js=parent.retornoPesquisaTurno';
    sUrl += '&pesquisa_chave=' + $F('ed85_i_turno');
    sUrl += '&sEnsino=' + $F('comboEnsino');
    sUrl += '&iEscola=' + $F('mo10_escola');
    sUrl += '&lQueryTurno=true';
    js_OpenJanelaIframe( '', 'db_iframe_turno', sUrl, 'Pesquisa Turno', false );
  } else {

    $('ed85_i_turno').value = '';
    $('ed15_c_nome').value  = '';
  }
}

function retornoPesquisaTurno() {

  $('btnPesquisar').disabled = true;

  if( typeof arguments[1] == "boolean" ) {

    $('ed15_c_nome').value = arguments[0];

    if ( arguments[1] == true) {
      $('ed85_i_turno').value = '';
    }
  } else {

    $('ed85_i_turno').value = arguments[0];
    $('ed15_c_nome').value  = arguments[1];
  }

  $('btnPesquisar').disabled = false;

  db_iframe_turno.hide();
}

$('btnPesquisar').onclick = function() {

  if ( !validaCamposPreenchidos() ) {
    return;
  }

  var oDados           = {};
      oDados.exec      = 'buscar';
      oDados.iEnsino   = $F('comboEnsino');
      oDados.iEscola   = $F('mo10_escola');
      oDados.iTurno    = $F('ed85_i_turno');
      oDados.iFase     = $F('mo10_fase');

  var oAjaxRequest = new AjaxRequest( sVagasRpc, oDados, retornoPesquisaEtapasVagas );
      oAjaxRequest.setMessage( _M( MENSAGEM_VAGAS001 + "buscando_etapas") );
      oAjaxRequest.execute();
};

function retornoPesquisaEtapasVagas(oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  oGridEtapasVagas.clearAll(true);

  oRetorno.aEtapas.each( function ( oEtapaVaga ) {

    var aLinha = [];
        aLinha.push(oEtapaVaga.sEtapa.urlDecode());

    var oInputVagas           = document.createElement( "input" );
        oInputVagas.id        = "iVagas" + oEtapaVaga.iEtapa;
        oInputVagas.type      = "text";
        oInputVagas.maxLength = 4;
        oInputVagas.setAttribute("value", oEtapaVaga.iVagas);
        oInputVagas.setAttribute("oninput","js_ValidaCampos(this,1,'Número de vagas','t','f',event);");

    aLinha.push(oInputVagas.outerHTML);
    aLinha.push(oEtapaVaga.iEtapa);
    aLinha.push( oEtapaVaga.iCodigoVaga == '' ? null : oEtapaVaga.iCodigoVaga );
    oGridEtapasVagas.addRow(aLinha, null);
  });
  oGridEtapasVagas.renderRows();

  $('btnSalvar').disabled = '';
}

function validaCamposPreenchidos() {

  if ( empty( $F('mo10_fase') ) ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_fase") );
    return false;
  }

  if ( empty( $F('mo10_escola') ) ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_escola") );
    return false;
  }

  if ( empty( $F('ed85_i_turno') ) ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_turno") );
    return false;
  }

  if ( empty($F('comboEnsino')) ) {

    alert( _M( MENSAGEM_VAGAS001 + "selecione_ensino") );
    return false;
  }

  return true;
}

$('btnSalvar').onclick = function() {

  var oDados              = {};
      oDados.exec         = 'salvar';
      oDados.iEnsino      = $F('comboEnsino');
      oDados.iEscola      = $F('mo10_escola');
      oDados.iTurno       = $F('ed85_i_turno');
      oDados.iFase        = $F('mo10_fase');
      oDados.aEtapasVagas = [];

  var aLinhasEtapasVagas = oGridEtapasVagas.getRows();

  for ( var iContador = 0; iContador < aLinhasEtapasVagas.length; iContador++ ) {

    var oEtapaVagas             = {};
        oEtapaVagas.iVagas      = aLinhasEtapasVagas[iContador].aCells[1].getValue().trim() == '' ? '0' : aLinhasEtapasVagas[iContador].aCells[1].getValue();
        oEtapaVagas.iEtapa      = aLinhasEtapasVagas[iContador].aCells[2].getValue();
        oEtapaVagas.iCodigoVaga = aLinhasEtapasVagas[iContador].aCells[3].getValue();
    oDados.aEtapasVagas.push( oEtapaVagas );
  }

  var oAjaxRequest = new AjaxRequest( sVagasRpc, oDados, retornoSalvar );
      oAjaxRequest.setMessage( _M( MENSAGEM_VAGAS001 + "salvando_vagas")  );
      oAjaxRequest.execute();
};

function retornoSalvar(oRetorno, lErro) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  alert( _M( MENSAGEM_VAGAS001 + "vagas_salvas")  );
  window.location.href = "mol1_vagas001.php";

}

function limpaCampos() {

  $('mo10_escola').value          = '';
  $('ed18_c_nome').value          = '';
  $('ed85_i_turno').value         = '';
  $('ed15_c_nome').value          = '';
  $('comboEnsino').options.length = 0;
  $('comboEnsino').add( new Option( 'Selecione...', '' ) );
  oGridEtapasVagas.clearAll( true );
}

$('comboEnsino').onchange = function() {

  $('ed85_i_turno').value = '';
  $('ed15_c_nome').value  = '';
  oGridEtapasVagas.clearAll(true);
}
</script>