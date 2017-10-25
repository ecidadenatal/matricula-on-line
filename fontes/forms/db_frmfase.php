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
//MODULO: matriculaonline
$clfase->rotulo->label();
$oDaoCiclos = new cl_ciclos;
$oDaoCiclos->rotulo->label();
$sOpcao = "incluir";

if( $db_opcao == 2 || $db_opcao == 22 ) {
  $sOpcao = "alterar";
} else if( $db_opcao == 3 || $db_opcao == 33 ) {
  $sOpcao = "excluir";
}

?>
<div class="container">
  <form id="frmFase" class="form-container" name="form1" method="post" action="">
    <fieldset><legend>Cadastro de Fases</legend>
    <table>
      <?php
        db_input('mo04_codigo',8,$Imo04_codigo,true,'hidden',3,'')
      ?>
      <tr>
        <td nowrap title="<?=$Tmo04_desc?>">
          <label for="mo04_desc"><?=$Lmo04_desc?></label>
        </td>
        <td>
          <?php
          db_input('mo04_desc', 70, $Imo04_desc, true, 'text', $db_opcao,"")
          ?>
        </td>
      </tr>
      <tr style='display:none;'>
        <td nowrap title="<?=$Tmo04_anousu?>" >
          <label for="mo04_anousu"><?=$Lmo04_anousu?></label>
        </td>
        <td>
          <?php
            db_input('mo04_anousu', 4, $Imo04_anousu, true, 'text', $db_opcao, "")
          ?>
        </td>
      </tr>

      <tr>
        <td nowrap title="<?=$Tmo04_datacorte?>">
          <label for="mo04_datacorte"><?=$Lmo04_datacorte?></label>
        </td>
        <td>
          <?php
            db_inputdata('mo04_datacorte', $mo04_datacorte_dia, $mo04_datacorte_mes, $mo04_datacorte_ano, true, 'text', $db_opcao,"")
          ?>
        </td>
      </tr>

      <tr>
        <td nowrap title="<?=$Tmo04_dtini?>">
          <label for="mo04_dtini"><?=$Lmo04_dtini?></label>
        </td>
        <td>
          <?php
            db_inputdata('mo04_dtini', $mo04_dtini_dia, $mo04_dtini_mes, $mo04_dtini_ano, true, 'text', $db_opcao,"")
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo04_dtfim?>">
          <label for="mo04_dtfim"><?=$Lmo04_dtfim?></label>
        </td>
        <td>
          <?php
            db_inputdata('mo04_dtfim', $mo04_dtfim_dia, $mo04_dtfim_mes, $mo04_dtfim_ano, true, 'text', $db_opcao,"")
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=$Tmo04_ciclo?>">
          <label for="mo04_ciclo">
            <?php
              db_ancora($Lmo04_ciclo,"js_pesquisamo04_ciclo(true);",$db_opcao);
            ?>
          </label>
        </td>
        <td>
          <?php
            db_input('mo04_ciclo',10,$Imo04_ciclo,true,'text',$db_opcao,"onchange='js_pesquisamo04_ciclo(false);'");
            db_input('mo09_descricao',58,$Imo09_descricao,true,'text',3,'');
          ?>
        </td>
      </tr>
    </table>
  </fieldset>
  <input id="opcao" name="<?php echo $sOpcao; ?>" type="hidden" value="<?php echo $sOpcao; ?>" />
  <input name="<?=($db_opcao==1?"incluir":($db_opcao==2||$db_opcao==22?"alterar":"excluir"))?>"
         type="button" id="db_opcao" <?=($db_botao==false?"disabled":"")?>
         value="<?=($db_opcao==1?"Incluir":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>"
         onclick="possuiVagasNaFase()";>
  <input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa();" >
</form>

<script>

const MENSAGEM_FRMFASE = 'educacao.matriculaonline.db_frmbase.';
var sRpcCiclo          = 'mol4_ciclos.RPC.php';
var sRpcFase           = 'mol4_fase.RPC.php';
var iDbOpcao           = '<?=$db_opcao ?>';
var lPossuiVagasNaFase = false;

$('mo04_codigo').addClassName("field-size2");
$('mo04_desc').addClassName("field-size9");
$('mo04_anousu').addClassName("field-size2");
$('mo04_dtini').addClassName("field-size2");
$('mo04_dtfim').addClassName("field-size2");
$('mo04_ciclo').addClassName("field-size2");
$('mo04_datacorte').addClassName("field-size2");
$('mo09_descricao').addClassName("field-size7");

function js_pesquisamo04_ciclo( mostra ) {

  var oDados           = {};
      oDados.sExecucao = 'pesquisaVagasVinculadasCiclo';
      oDados.iFase     = $F('mo04_codigo');
      oDados.lMostra   = mostra;

  var oAjaxRequest = new AjaxRequest( 'mol4_ciclos.RPC.php', oDados, retornoVerificaVagaVinculadaCiclo );
      oAjaxRequest.execute();
}

function retornoVerificaVagaVinculadaCiclo( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMessage.urlDecode() );
    return false;
  }

  if ( iDbOpcao == 2 ) {

    if ( oRetorno.lVagasVinculadasCiclo ) {

      if ( !confirm( _M( MENSAGEM_FRMFASE + 'confirma_exclusao_ciclo'))) {

        $('mo04_ciclo').value = oRetorno.iCiclo;
        return false;
      }
    }
  }

  if ( oRetorno.lMostra == true ) {
     js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_ciclos','func_ciclos.php?funcao_js=parent.js_mostraciclo1|dl_sequencial|dl_descricao','Pesquisa Ciclo',true);
  } else {

    if ( document.form1.mo04_ciclo.value != '' ) {
       js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_ciclos','func_ciclos.php?pesquisa_chave='+document.form1.mo04_ciclo.value+'&funcao_js=parent.js_mostraciclo','Pesquisa Ciclo',false)
    } else{
       document.form1.mo09_descricao.value = '';
    }
  }
}

function js_mostraciclo( chave, erro ) {

  document.form1.mo09_descricao.value = chave;

  if ( erro == true ) {

    document.form1.mo04_ciclo.focus();
    document.form1.mo04_ciclo.value = '';
  }
}

function js_mostraciclo1( chave1, chave2 ) {

  document.form1.mo04_ciclo.value     = chave1;
  document.form1.mo09_descricao.value = chave2;
  db_iframe_ciclos.hide();
}

function js_pesquisa() {

  var sUrl = 'func_fase.php?funcao_js=parent.js_preenchepesquisa|mo04_codigo&lAtivos=true';
  js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_fase', sUrl, 'Pesquisa Fases', true);
}

function js_preenchepesquisa( chave ) {

  db_iframe_fase.hide();
  <?php
    if ( $db_opcao != 1 ) {
      echo " location.href = '".basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"])."?chavepesquisa='+chave";
    }
  ?>
}

/**
 * Verifica se o ciclo selecionado possui vínculo com ensino.
 * Caso haja, a inclusão é realizada.
 */
function validarEnsinoVinculadoCiclo() {

  if ( empty($F('mo04_desc'))) {

    alert( _M( MENSAGEM_FRMFASE + "informe_descricao") );
    return;
  }

  if ( empty($F('mo04_datacorte'))) {

    alert( _M( MENSAGEM_FRMFASE + "informe_data_corte") );
    return;
  }

  if ( empty($F('mo04_dtini'))) {

    alert( _M( MENSAGEM_FRMFASE + "informe_data_inicio") );
    return;
  }

  if ( empty($F('mo04_dtfim'))) {

    alert( _M( MENSAGEM_FRMFASE + "informe_data_fim") );
    return;
  }

  if ( empty($F('mo04_ciclo'))) {

    alert( _M( MENSAGEM_FRMFASE + "selecione_ciclo") );
    return;
  }

  var oParametros             = {};
      oParametros.sExecucao   = "validarEnsinoVinculadoCiclo";
      oParametros.iCiclo      = $F('mo04_ciclo');
      oParametros.asynchronous = false;

  var oAjaxRequest = new AjaxRequest( sRpcCiclo, oParametros, retornoEnsinoVinculadoCiclo );
      oAjaxRequest.setMessage( "Aguarde, verificando ensino vinculado ao ciclo..." );
      oAjaxRequest.execute();
}

function retornoEnsinoVinculadoCiclo( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMessage.urlDecode() );
    return;
  }

  if ( oRetorno.lPossuiVinculo == false ) {

    alert( _M( MENSAGEM_FRMFASE + "ciclo_sem_ensino") );
    return;
  }

  if ( $F('mo04_dtini_ano') > $F('mo04_datacorte_ano') || $F('mo04_dtfim_ano') > $F('mo04_datacorte_ano') ) {

    alert( _M( MENSAGEM_FRMFASE + "ano_data_inicio_fim_maior_corte") );
    return ;
  }


  if ( !validaDatas() ) {

    alert( _M( MENSAGEM_FRMFASE + "data_fim_maior_data_inicio") );
    return;
  }

  if ( iDbOpcao == 3 && lPossuiVagasNaFase ) {

    if ( !confirm( _M( MENSAGEM_FRMFASE + 'confirma_exclusao'))) {
      return false;
    }
  }

  $('mo04_anousu').value = $F('mo04_datacorte_ano');

  $('frmFase').submit();
}

/**
 * Verifica se existem vagas cadastradas para a fase selecionada
 */
function possuiVagasNaFase() {

  if( iDbOpcao == 1 ) {
    validarEnsinoVinculadoCiclo();
  } else {

    var oDados          = {};
        oDados.sExecuta = 'verificaVagasNaFase';
        oDados.iFase    = $F('mo04_codigo');

    var oAjaxRequest = new AjaxRequest( sRpcFase, oDados, retornoPossuiVagasNaFase );
        oAjaxRequest.setMessage( _M( MENSAGEM_FRMFASE + 'verificando_vagas' ) );
        oAjaxRequest.execute();
  }
}

/**
 * Apos retornar se possui vagas na fase, realizada a chamada para validar os ensinos vinculados
 * @param oRetorno
 * @param lErro
 * @returns {boolean}
 */
function retornoPossuiVagasNaFase( oRetorno, lErro ) {

  if( lErro ) {

    alert( oRetorno.sMensagem.urlDecode() );
    return false;
  }

  lPossuiVagasNaFase = oRetorno.lPossuiVagasNaFase;

  validarEnsinoVinculadoCiclo();
}

function validaDatas() {

  if ($('mo04_dtini_ano').value != "" || $('mo04_dtini_mes').value != "" || $('mo04_dtini_dia').value != ""
    ||$('mo04_dtfim_ano').value != "" || $('mo04_dtfim_mes').value != "" || $('mo04_dtfim_dia').value != "") {

    var oDtInicio = new Date($('mo04_dtini_ano').value, $('mo04_dtini_mes').value, $('mo04_dtini_dia').value);
    var oDtFinal  = new Date($('mo04_dtfim_ano').value, $('mo04_dtfim_mes').value, $('mo04_dtfim_dia').value);

    if ( oDtInicio.getTime() > oDtFinal.getTime() ) {
      return false;
    }
  }

  return true;
}

</script>