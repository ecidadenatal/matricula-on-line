<?php
/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBseller Servicos de Informatica
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
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("dbforms/db_funcoes.php"));
?>
<html>
<head>
  <title>DBSeller Inform&aacute;tica Ltda - P&aacute;gina Inicial</title>
  <?
  db_app::load("scripts.js, strings.js, prototype.js, datagrid.widget.js, grid.style.css, AjaxRequest.js");
  ?>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <style>
    input[type="text"] { text-align: right; }
  </style>
</head>
<body>
  <div class="container">

     <fieldset>
       <legend>Idade por Etapa</legend>

       <table>
         <tr>
           <td style="width: 50px" nowrap>
             <label for="cboTipoEnsino">
               <b>Ensino:</b>
             </label>
           </td>
           <td>
             <select name="cboTipoEnsino"  id="cboTipoEnsino" style="width: 100%" onchange="pesquisaEtapasEnsino();">
                <option value="">Selecione</option>
             </select>
           </td>
         </tr>
         <tr>
           <td colspan="2" style="width: 700px">
             <fieldset>
               <legend>Etapa</legend>
               <div id="ctnGridEtapas">

               </div>
             </fieldset>
           </td>

         </tr>

       </table>

     </fieldset>
    <input type="button" value="Salvar" id="btnSalvar" onclick="salvarEtapas();">
  </div>
</body>
</html>
<?php
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>

<script type="text/javascript">

  const MENSAGEM_IDADE_ETAPA = 'educacao.matriculaonline.mol1_idadeetapa001.';
  (function(window) {

    var dataGrid              = new DBGrid('etapasEnsinoGrid');
    dataGrid.nameInstance = "dataGrid";
    dataGrid.setHeader(["Etapa", "Idade Inicial", "Idade Final", "Código Etapa"]);
    dataGrid.setCellWidth(["34%", "33%","33%"]);
    dataGrid.setCellAlign(['left', 'center', 'center']);
    dataGrid.aHeaders[3].lDisplayed = false;
    dataGrid.setHeight("200");
    dataGrid.show($('ctnGridEtapas'));



    /**
     * Pesquisa os ensinos disponiveis para a escola
     */
    var oParametros = {
      exec : 'pesquisaEnsino'
    }

    var oRequisicaoEnsino = new AjaxRequest('edu_educacaobase.RPC.php', oParametros,
      function (oRetorno, lErro) {

        var oCboEnsino = $('cboTipoEnsino');
        oCboEnsino.options.length = 1;
        oRetorno.aEnsino.each(function (oEnsino) {
          oCboEnsino.add(new Option(oEnsino.sDescricao.urlDecode(), oEnsino.iCodigo));
        });

    }).setMessage("Aguarde, carregando dados").execute();

    window.dataGrid = dataGrid;


  })(window);

  function pesquisaEtapasEnsino () {

    /**
     * Pesquisa os ensinos disponiveis para a escola
     */
    var oParametros = {
      exec         : 'getEtapas',
      iCodigoEnsino: $F('cboTipoEnsino')
    }

    var oRequisicaoEnsino = new AjaxRequest('mol1_idadeetapa.RPC.php', oParametros,

      function (oRetorno, lErro) {

        dataGrid.clearAll(true);
        oRetorno.aEtapas.each(function (oEtapa) {

          var sValorColunaMesInicial = criarInput('anoInicialEtapa_' + oEtapa.codigo,  oEtapa.idade_inicial.ano)+ " anos e ";
          sValorColunaMesInicial    += criarInput('mesInicialEtapa_' + oEtapa.codigo,  oEtapa.idade_inicial.mes)+ " meses";

          var sColunaIdadeFinal  = criarInput('anoFinalEtapa_' + oEtapa.codigo,  oEtapa.idade_final.ano)+ " anos e ";
          sColunaIdadeFinal     += criarInput('mesFinalEtapa_' + oEtapa.codigo,  oEtapa.idade_final.mes)+ " meses";
          window.dataGrid.addRow([
            oEtapa.nome.urlDecode(),
            sValorColunaMesInicial,
            sColunaIdadeFinal,
            oEtapa.codigo
          ]);

        });
        dataGrid.renderRows();

      }).setMessage("Aguarde, carregando dados").execute();
  }

  function salvarEtapas () {

    var dataEtapas = [];
    var lErro      = false;
    dataGrid.getRows().each(function (oRow) {

      var iCodigoEtapa = oRow.aCells[3].getValue();

      /**
       * Validamos se o periodo inicial é maior que o periodo final
       */
      var sPeriodoInicial = ( parseInt($F('anoInicialEtapa_' + iCodigoEtapa)) * 12 ) + parseInt($F('mesInicialEtapa_' + iCodigoEtapa));
      var sPeriodoFinal   = ( parseInt($F('anoFinalEtapa_' + iCodigoEtapa)) * 12 )   + parseInt($F('mesFinalEtapa_' + iCodigoEtapa));

      if ( sPeriodoInicial > sPeriodoFinal ) {

        var sEtapa =  oRow.aCells[0].getValue();
        alert(_M(MENSAGEM_IDADE_ETAPA+'periodo_inicial_maior_que_final', {sEtapa: sEtapa}));
        lErro = true;
        throw $break;
        return false;

      }
      dataEtapas.push({
        'codigo'        : iCodigoEtapa,
        'idade_inicial' : {ano: $F('anoInicialEtapa_' + iCodigoEtapa),
                           mes: $F('mesInicialEtapa_' + iCodigoEtapa)
                          },
        'idade_final'   : {ano: $F('anoFinalEtapa_' + iCodigoEtapa),
                           mes: $F('mesFinalEtapa_' + iCodigoEtapa)
                          }
      });

    });

    if (lErro) {
      return;
    }
    if (dataEtapas.length == 0) {

      alert(_M(MENSAGEM_IDADE_ETAPA+"nenhum_periodo_selecionado"));
      return;
    }

    var oParametros = {
      exec         : 'salvar',
      dataEtapas: dataEtapas
    }

    var oRequisicaoEnsino = new AjaxRequest('mol1_idadeetapa.RPC.php', oParametros,

      function (oRetorno, lErro) {

        alert(oRetorno.sMensagem.urlDecode());
        pesquisaEtapasEnsino();

      }).setMessage("Aguarde, salvando dados...").execute();

  }

  function criarInput(sNome, sValor) {

    var sInput = '<input type="text" value="' +sValor+ '" onkeypress="return js_mask(event, \'0-9\')"';
    sInput    += 'maxlength="2" id="'+sNome+'" style="width:50px"/>';
    return sInput;
  }

</script>
