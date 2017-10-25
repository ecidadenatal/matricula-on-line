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
require_once(modification("libs/JSON.php"));

$oJson                  = new services_json();
$oParam                 = $oJson->decode(str_replace("\\","",$_POST["json"]));
$oRetorno               = new stdClass();
$oRetorno->iStatus      = 1;
$oRetorno->sMessage     = '';

define('MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC', 'educacao.matriculaonline.mol4_ordenarcriteriosdesignacaoRPC.');

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "buscaCriterios":

      $sCampos       = " mo16_sequencial, mo16_descricao ";
      $oDaoCriterios = new cl_criteriosdesignacao();
      $sSqlCriterios = $oDaoCriterios->sql_query_file(null, "*", "mo16_descricao");
      $rsCriterios   = db_query($sSqlCriterios);

      $oMsgErro = new stdClass();
      if (!$rsCriterios) {

        $oMsgErro->sErro = pg_last_error();
        throw new Exception( _M(MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC."erro_buscar_criterios", $oMsgErro) );
      }

      $iLinhas = pg_num_rows($rsCriterios);
      if ($iLinhas == 0) {
        throw new Exception( _M(MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC."sem_criterios_cadastrados") );
      }

      $aCriterios = array();
      for ($i=0; $i < $iLinhas; $i++) {

        $oDados = db_utils::fieldsMemory($rsCriterios, $i, false, false, true);
        $aCriterios[$oDados->mo16_sequencial] = $oDados;
      }

      $sCamposVinculados = "{$sCampos}, mo17_ordem ";
      $sWhereVinculados  = "mo17_ensino = {$oParam->iEnsino}";
      $sSqlVinculados    = $oDaoCriterios->sql_query_ensino(null, $sCamposVinculados, "mo17_ordem", $sWhereVinculados);
      $rsVinculados      = db_query($sSqlVinculados);
      $iLinhasVinculados = pg_num_rows($rsVinculados);

      $aCriteriosVinculados = array();
      for ($i=0; $i < $iLinhasVinculados; $i++) {

        $oDados = db_utils::fieldsMemory($rsVinculados, $i, false, false, true);
        $aCriteriosVinculados[] = $oDados;
        unset($aCriterios[$oDados->mo16_sequencial]);
      }

      $oRetorno->aCriteriosVinculados = $aCriteriosVinculados;
      $oRetorno->aCriterios           = $aCriterios;

      break;

    case 'salvarCriteriosEnsino':

      if ( empty($oParam->iEnsino) ) {
        throw new Exception(_M(MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC."informe_ensino"));
      }

      if ( empty($oParam->aCriterios) || count($oParam->aCriterios) == 0) {
        throw new Exception(_M(MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC."informe_ensino"));
      }

      $oEnsino         = EnsinoRepository::getEnsinoByCodigo($oParam->iEnsino);
      $oCriterioEnsino = new CriterioDesignacaoEnsino($oEnsino);

      $iOrdem = 1;
      foreach ($oParam->aCriterios as $iCodigoCriterio) {

        $oCriterioDesignacao = new TipoCriterioDesignacao();
        $oCriterioDesignacao->setCodigo($iCodigoCriterio);
        $oCriterioEnsino->setOrdenacaoCriterio($oCriterioDesignacao, $iOrdem);
        $iOrdem++;
      }

      $oCriterioEnsino->salvar();
      $oRetorno->sMessage = urlencode( _M(MSG_MOL4_ORDENARCRITERIOSDESIGNACAORPC."salvo_sucesso") );

      break;
  }

  db_fim_transacao(false);


} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = urlencode($eErro->getMessage());
}

$oRetorno->erro = $oRetorno->iStatus == 2;
echo $oJson->encode($oRetorno);