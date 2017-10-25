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
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBToggleList.widget.js"></script>
</head>
<body class='body-default'>
  <div class='container'>
    <form id="frm1" >
      <fieldset>
        <legend>Ordenar Critérios de Designação</legend>
        <table class="form-container">
          <tr>
            <td> <label for="cboEnsino">Ensino:</label></td>
            <td>
              <select id='cboEnsino' onchange="buscaCriteriosEnsino();">
                <option value="" >Selecione</option>
              </select>
            </td>
          </tr>
        </table>
        <fieldset class='separator'>
          <legend>Critérios de Designação</legend>
          <div id="ctnCriterios"></div>
        </fieldset>
      </fieldset>
      <input type="button" id='btnSalvar' name='btnSalvar' value="Salvar" />
    </form>
  </div>
<?php
  db_menu();
?>
</body>
</html>
<script type="text/javascript">

const MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 = "educacao.matriculaonline.mol3_ordenarcriteriosdesignacao001.";

var sRPCBase         = "edu_educacaobase.RPC.php";
var sRPCCriterio     = "mol4_ordenarcriteriosdesignacao.RPC.php";
var oToogleCriterios = new DBToggleList( [{ sId: 'sCriterio', sLabel: 'Critérios de Designação' }] );
    oToogleCriterios.show( $('ctnCriterios') );

function retornoEnsinos(oRetorno, lErro) {

  if (lErro) {

    alert( _M(MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 + "sem_ensinos") );
    return;
  }

  oRetorno.aEnsino.each( function (oEnsino) {
    $('cboEnsino').add( new Option(oEnsino.sDescricao.urlDecode(), oEnsino.iCodigo));
  });
}

(function(){

  var oBuscaEnsinos = new AjaxRequest(sRPCBase, {'exec':'pesquisaEnsino'}, retornoEnsinos);
  oBuscaEnsinos.setMessage( _M(MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 + "busca_ensino") );
  oBuscaEnsinos.execute();

})();


function retornoCriterios(oRetorno, lErro) {

  if (lErro) {

    alert(oRetorno.sMessage.urlDecode());
    return;
  }

  oToogleCriterios.clearAll();
  oRetorno.aCriteriosVinculados.each(function(oDados) {

    var oCriterio = {'iCriterio': oDados.mo16_sequencial, 'sCriterio': oDados.mo16_descricao.urlDecode()};
    oToogleCriterios.addSelected(oCriterio);
  });

  if( oRetorno.aCriterios != '' ) {

    for (var i in oRetorno.aCriterios) {

      var oDados = oRetorno.aCriterios[i];
      var oCriterio = {'iCriterio': oDados.mo16_sequencial, 'sCriterio': oDados.mo16_descricao.urlDecode()};
      oToogleCriterios.addSelect(oCriterio);
    }
  }

  oToogleCriterios.show( $('ctnCriterios') );
}

/**
 * busca os critérios de designação e os vínculados ao ensino
 */
function buscaCriteriosEnsino() {

  if ( $F('cboEnsino') == '') {

    oToogleCriterios.clearAll();
    return;
  }

  var oBuscaCriterios = new AjaxRequest(sRPCCriterio,
                                        {'exec' : 'buscaCriterios', 'iEnsino' : $F('cboEnsino')},
                                        retornoCriterios);
  oBuscaCriterios.setMessage( _M(MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 + "busca_criterios") );
  oBuscaCriterios.execute();
}


$('btnSalvar').observe('click', function() {

  var aCriteriosSelecionados = [];

  oToogleCriterios.getSelected().each(function( oCriterio ) {
    aCriteriosSelecionados.push( oCriterio.iCriterio );
  });

  if (aCriteriosSelecionados.length == 0) {

    alert(_M(MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 + "selecione_criterio"));
    return;
  }

  var oParametros = {exec: 'salvarCriteriosEnsino', 'iEnsino' : $F('cboEnsino') };
  oParametros.aCriterios = aCriteriosSelecionados;

  var oAjax = new AjaxRequest(sRPCCriterio, oParametros, retornoSalvar);
  oAjax.setMessage( _M(MSG_MOL3_ORDENARCRITERIOSDESIGNACAO001 + "salvando_criterios") );
  oAjax.execute();

});

function retornoSalvar(oRetorno, lErro) {

  alert(oRetorno.sMessage.urlDecode());
  if (lErro) {
    return;
  }

  $('cboEnsino').value = '';
  buscaCriteriosEnsino();

}

</script>