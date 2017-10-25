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
$oRotulo->label('ed11_i_codigo');
$oRotulo->label('ed11_c_descr');
$oRotulo->label('ed18_i_codigo');
$oRotulo->label('ed18_c_nome');

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
  <script language="JavaScript" type="text/javascript" src="scripts/classes/educacao/matriculaonline/DBFuncoesRelatoriosEtapaEscola.js"></script>
</head>
<body class='body-default'>
  <div class='container'>

  <form>

    <fieldset>
      <legend>Relatório Designados</legend>
      <table class="form-container">

        <tr>
          <td>
            <label for="mo04_codigo">
              <?php
                db_ancora('Fase:',"pesquisaFase(true);", 1);
              ?>
            </label>
          </td>
          <td>
            <?php
              db_input ( 'mo04_codigo', 10, $Imo04_codigo, true, 'text', "", "onchange='pesquisaFase(false);'" );
              db_input ( 'mo04_desc', 50, $Imo04_desc, true, 'text', 3 );
            ?>
          </td>
        </tr>

        <tr>
          <td>
            <label for="ed11_i_codigo">
              <?php
                db_ancora('Etapa: ' , 'pesquisaEtapaFase(true, 2)', 1);
              ?>
            </label>
          </td>
          <td>
            <?php
              db_input ( 'ed11_i_codigo', 10, $Ied11_i_codigo, true, 'text', "", "onchange='pesquisaEtapaFase(false, 2);'" );
              db_input ( 'ed11_c_descr', 50, $Ied11_c_descr, true, 'text', 3 );
            ?>
          </td>
        </tr>

        <tr>
          <td>
            <label for="ed18_i_codigo">
              <?php
                db_ancora('Escola: ' , 'pesquisaEscolaFase(true, 2)', 1);
              ?>
            </label>
          </td>
          <td>
            <?php
              db_input ( 'ed18_i_codigo', 10, $Ied18_i_codigo, true, 'text', "", "onchange='pesquisaEscolaFase(false, 2);'" );
              db_input ( 'ed18_c_nome', 50, $Ied18_c_nome, true, 'text', 3 );
            ?>
          </td>
        </tr>

        <tr>
          <td><label for='ordem'>Ordem:</label></td>
          <td >
            <select id='ordem'>
              <option value='A' >Alfabética</option>
              <option value='D' >Designação</option>
            </select>
          </td>
        </tr>
        <tr>
          <td><label for="modelo">Modelo: </label></td>
          <td>
            <select id='modelo'>
              <option value="A">Analítico</option>
              <option value="S">Sintético</option>
            </select>
          </td>
        </tr>


      </table>
    </fieldset>
    <input type="button" value="Imprimir" id="btnImprimir" name="btnImprimir" />

  </form>

  </div>
  <?php
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>


<script type="text/javascript">

  var MSG_MOL2_DESIGNADOS001 = "educacao.matriculaonline.mol2_designados001.";

  function pesquisaFase ( lMostra ) {

    limpaCamposEtapa();
    limpaCamposEscola();

    var sUrl = 'func_fase.php?lAtivos=false&lPossuiVagas=true';

    if( lMostra ) {

      sUrl += '&funcao_js=parent.retornoPesquisaFase|mo04_codigo|mo04_desc|mo04_ciclo';
      js_OpenJanelaIframe( '', 'db_iframe_fase', sUrl, 'Pesquisa Fase', true);

    } else if ( $F('mo04_codigo') != '' ) {

      sUrl += '&funcao_js=parent.retornoPesquisaFase';
      sUrl += '&pesquisa_chave=' + $F('mo04_codigo');
      js_OpenJanelaIframe( '', 'db_iframe_fase', sUrl, 'Pesquisa Fase', false);
    } else {

      limpaCampos();
    }

  }

  function retornoPesquisaFase () {

    if( typeof arguments[1] == "boolean" ) {

      $('mo04_desc').value = arguments[0];

      if ( arguments[1] ) {
        $('mo04_codigo').value = '';
      }

      return;
    }


    $('mo04_desc').value   = arguments[1];
    $('mo04_codigo').value = arguments[0];

    db_iframe_fase.hide();
  }

  function limpaCampos () {

    $('mo04_codigo').value   = '';
    $('mo04_desc').value     = '';
    limpaCamposEtapa();
    limpaCamposEscola();
  }

  $('btnImprimir').observe('click', function () {

    if ( $F('mo04_codigo') == '' ) {

      alert( _M( MSG_MOL2_DESIGNADOS001 + "fase_nao_selecionada" ) );
      return false;
    }

    var sUrl  = "mol2_designados002.php";
        sUrl += "?iFase=" + $F('mo04_codigo');
        sUrl += "&iEtapa=" + $F('ed11_i_codigo');
        sUrl += "&iEscola=" + $F('ed18_i_codigo');
        sUrl += "&sOrdem=" + $F('ordem');
        sUrl += "&sModelo=" + $F('modelo');

    window.open(sUrl, '', 'width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
  });

</script>
</body>
</html>
